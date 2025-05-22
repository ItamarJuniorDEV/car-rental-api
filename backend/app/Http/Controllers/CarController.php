<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCarRequest;
use App\Http\Requests\UpdateCarRequest;
use App\Models\Car;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Car::query();

        if ($request->has('plate')) {
            $query->where('plate', 'like', '%' . $request->input('plate') . '%');
        }

        if ($request->boolean('available')) {
            $query->where('available', true);
        }

        $cars = $query->paginate(min($request->integer('per_page', 15), 500));

        return response()->json([
            'message' => 'Veículos listados com sucesso!',
            'data' => $cars->items(),
            'pagination' => [
                'total' => $cars->total(),
                'per_page' => $cars->perPage(),
                'current_page' => $cars->currentPage(),
                'last_page' => $cars->lastPage(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json(['message' => 'Veículo não encontrado.'], 404);
        }

        return response()->json([
            'message' => 'Veículo encontrado com sucesso!',
            'data' => $car,
        ]);
    }

    public function store(StoreCarRequest $request): JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        try {
            $car = Car::create($request->validated());

            return response()->json([
                'message' => 'Veículo criado com sucesso!',
                'data' => $car,
            ], 201);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }

    public function update(UpdateCarRequest $request, int $id): JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $car = Car::find($id);

        if (!$car) {
            return response()->json(['message' => 'Veículo não encontrado.'], 404);
        }

        try {
            $car->fill($request->validated());
            $car->save();

            return response()->json([
                'message' => 'Veículo atualizado com sucesso!',
                'data' => $car,
            ]);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $car = Car::find($id);

        if (!$car) {
            return response()->json(['message' => 'Veículo não encontrado.'], 404);
        }

        if ($car->rentals()->whereNull('period_actual_end_date')->exists()) {
            return response()->json(['message' => 'Não é possível remover um veículo com locação ativa.'], 422);
        }

        try {
            $car->delete();

            return response()->json(['message' => 'Veículo removido com sucesso!']);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }
}

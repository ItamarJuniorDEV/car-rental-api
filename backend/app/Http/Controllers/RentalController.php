<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRentalRequest;
use App\Http\Requests\UpdateRentalRequest;
use App\Models\Rental;
use App\Services\RentalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class RentalController extends Controller
{
    public function __construct(private RentalService $service) {}

    public function index(Request $request): JsonResponse
    {
        $rentals = Rental::with(['client', 'car'])->paginate(min($request->integer('per_page', 15), 500));

        $data = array_map(fn ($r) => $this->service->format($r), $rentals->items());

        return response()->json([
            'message' => 'Locações listadas com sucesso!',
            'data' => $data,
            'pagination' => [
                'total' => $rentals->total(),
                'per_page' => $rentals->perPage(),
                'current_page' => $rentals->currentPage(),
                'last_page' => $rentals->lastPage(),
            ],
        ]);
    }

    public function show(Rental $rental): JsonResponse
    {
        $rental->load(['client', 'car']);

        return response()->json([
            'message' => 'Locação encontrada com sucesso!',
            'data' => $this->service->format($rental),
        ]);
    }

    public function store(StoreRentalRequest $request): JsonResponse
    {
        try {
            $rental = $this->service->create($request->validated());

            return response()->json([
                'message' => 'Locação criada com sucesso!',
                'data' => $this->service->format($rental),
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }

    public function update(UpdateRentalRequest $request, Rental $rental): JsonResponse
    {
        try {
            $updated = $this->service->update($rental, $request->validated());

            return response()->json([
                'message' => 'Locação atualizada com sucesso!',
                'data' => $this->service->format($updated),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }

    public function destroy(Rental $rental): JsonResponse
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        try {
            $this->service->delete($rental);

            return response()->json(['message' => 'Locação removida com sucesso!']);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }
}

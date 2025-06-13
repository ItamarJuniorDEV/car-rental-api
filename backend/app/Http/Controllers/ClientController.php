<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Client::query();

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        $clients = $query->paginate(min($request->integer('per_page', 15), 500));

        return response()->json([
            'message' => 'Clientes listados com sucesso!',
            'data' => $clients->items(),
            'pagination' => [
                'total' => $clients->total(),
                'per_page' => $clients->perPage(),
                'current_page' => $clients->currentPage(),
                'last_page' => $clients->lastPage(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Cliente não encontrado.'], 404);
        }

        return response()->json([
            'message' => 'Cliente encontrado com sucesso!',
            'data' => $client,
        ]);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        try {
            $client = Client::create($request->validated());

            return response()->json([
                'message' => 'Cliente criado com sucesso!',
                'data' => $client,
            ], 201);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }

    public function update(UpdateClientRequest $request, int $id): JsonResponse
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Cliente não encontrado.'], 404);
        }

        try {
            $client->fill($request->validated());
            $client->save();

            return response()->json([
                'message' => 'Cliente atualizado com sucesso!',
                'data' => $client,
            ]);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Cliente não encontrado.'], 404);
        }

        if ($client->rentals()->whereNull('period_actual_end_date')->exists()) {
            return response()->json(['message' => 'Não é possível remover um cliente com locação ativa.'], 422);
        }

        try {
            $client->delete();

            return response()->json(['message' => 'Cliente removido com sucesso!']);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }
}

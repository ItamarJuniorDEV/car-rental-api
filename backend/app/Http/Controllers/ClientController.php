<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Client::query();

        if ($request->has('name')) {
            $query->where('name', 'like', '%'.$request->input('name').'%');
        }

        $clients = $query->paginate(min($request->integer('per_page', 15), 500));

        return response()->json([
            'message' => 'Clientes listados com sucesso!',
            'data' => ClientResource::collection($clients->getCollection())->resolve(),
            'pagination' => [
                'total' => $clients->total(),
                'per_page' => $clients->perPage(),
                'current_page' => $clients->currentPage(),
                'last_page' => $clients->lastPage(),
            ],
        ]);
    }

    public function show(Client $client): JsonResponse
    {
        return response()->json([
            'message' => 'Cliente encontrado com sucesso!',
            'data' => (new ClientResource($client))->resolve(),
        ]);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = Client::create($request->validated());

        return response()->json([
            'message' => 'Cliente criado com sucesso!',
            'data' => (new ClientResource($client))->resolve(),
        ], 201);
    }

    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $client->fill($request->validated());
        $client->save();

        return response()->json([
            'message' => 'Cliente atualizado com sucesso!',
            'data' => (new ClientResource($client))->resolve(),
        ]);
    }

    public function destroy(Client $client): JsonResponse
    {
        if ($client->rentals()->whereNull('period_actual_end_date')->exists()) {
            return response()->json(['message' => 'Não é possível remover um cliente com locação ativa.'], 422);
        }

        $client->delete();

        return response()->json(['message' => 'Cliente removido com sucesso!']);
    }
}

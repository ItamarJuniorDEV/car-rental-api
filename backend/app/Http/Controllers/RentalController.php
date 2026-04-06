<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRentalRequest;
use App\Http\Requests\UpdateRentalRequest;
use App\Http\Resources\RentalResource;
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

        return response()->json([
            'message' => 'Locações listadas com sucesso!',
            'data' => RentalResource::collection($rentals->getCollection())->resolve(),
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
            'data' => (new RentalResource($rental))->resolve(),
        ]);
    }

    public function store(StoreRentalRequest $request): JsonResponse
    {
        try {
            $rental = $this->service->create($request->validated());

            return response()->json([
                'message' => 'Locação criada com sucesso!',
                'data' => (new RentalResource($rental))->resolve(),
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
                'data' => (new RentalResource($updated))->resolve(),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }

    public function destroy(Rental $rental): JsonResponse
    {
        $this->authorize('delete', $rental);

        try {
            $this->service->delete($rental);

            return response()->json(['message' => 'Locação removida com sucesso!']);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }
}

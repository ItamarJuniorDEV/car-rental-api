<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class BrandController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Brand::query();

        if ($request->has('name')) {
            $query->where('name', 'like', '%'.$request->input('name').'%');
        }

        $brands = $query->paginate(min($request->integer('per_page', 15), 500));

        return response()->json([
            'message' => 'Marcas listadas com sucesso!',
            'data' => $brands->items(),
            'pagination' => [
                'total' => $brands->total(),
                'per_page' => $brands->perPage(),
                'current_page' => $brands->currentPage(),
                'last_page' => $brands->lastPage(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $brand = Brand::find($id);

        if (! $brand) {
            return response()->json(['message' => 'Marca não encontrada.'], 404);
        }

        return response()->json([
            'message' => 'Marca encontrada com sucesso!',
            'data' => $brand,
        ]);
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        $this->authorize('create', Brand::class);

        try {
            $brand = Brand::create($request->validated());

            return response()->json([
                'message' => 'Marca criada com sucesso!',
                'data' => $brand,
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }

    public function update(UpdateBrandRequest $request, int $id): JsonResponse
    {
        $brand = Brand::find($id);

        if (! $brand) {
            return response()->json(['message' => 'Marca não encontrada.'], 404);
        }

        $this->authorize('update', $brand);

        try {
            $brand->fill($request->validated());
            $brand->save();

            return response()->json([
                'message' => 'Marca atualizada com sucesso!',
                'data' => $brand,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $brand = Brand::find($id);

        if (! $brand) {
            return response()->json(['message' => 'Marca não encontrada.'], 404);
        }

        $this->authorize('delete', $brand);

        try {
            $brand->delete();

            return response()->json(['message' => 'Marca removida com sucesso!']);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }
}

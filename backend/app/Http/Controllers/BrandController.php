<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            'data' => BrandResource::collection($brands->getCollection())->resolve(),
            'pagination' => [
                'total' => $brands->total(),
                'per_page' => $brands->perPage(),
                'current_page' => $brands->currentPage(),
                'last_page' => $brands->lastPage(),
            ],
        ]);
    }

    public function show(Brand $brand): JsonResponse
    {
        return response()->json([
            'message' => 'Marca encontrada com sucesso!',
            'data' => (new BrandResource($brand))->resolve(),
        ]);
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        $this->authorize('create', Brand::class);

        $brand = Brand::create($request->validated());

        return response()->json([
            'message' => 'Marca criada com sucesso!',
            'data' => (new BrandResource($brand))->resolve(),
        ], 201);
    }

    public function update(UpdateBrandRequest $request, Brand $brand): JsonResponse
    {
        $this->authorize('update', $brand);

        $brand->fill($request->validated());
        $brand->save();

        return response()->json([
            'message' => 'Marca atualizada com sucesso!',
            'data' => (new BrandResource($brand))->resolve(),
        ]);
    }

    public function destroy(Brand $brand): JsonResponse
    {
        $this->authorize('delete', $brand);

        $brand->delete();

        return response()->json(['message' => 'Marca removida com sucesso!']);
    }
}

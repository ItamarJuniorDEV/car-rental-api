<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLineRequest;
use App\Http\Requests\UpdateLineRequest;
use App\Models\Line;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class LineController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Line::query();

        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->integer('brand_id'));
        }

        $lines = $query->paginate(min($request->integer('per_page', 15), 500));

        return response()->json([
            'message' => 'Linhas listadas com sucesso!',
            'data' => $lines->items(),
            'pagination' => [
                'total' => $lines->total(),
                'per_page' => $lines->perPage(),
                'current_page' => $lines->currentPage(),
                'last_page' => $lines->lastPage(),
            ],
        ]);
    }

    public function show(Line $line): JsonResponse
    {
        return response()->json([
            'message' => 'Linha encontrada com sucesso!',
            'data' => $line,
        ]);
    }

    public function store(StoreLineRequest $request): JsonResponse
    {
        $this->authorize('create', Line::class);

        try {
            $line = Line::create($request->validated());

            return response()->json([
                'message' => 'Linha criada com sucesso!',
                'data' => $line,
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }

    public function update(UpdateLineRequest $request, Line $line): JsonResponse
    {
        $this->authorize('update', $line);

        try {
            $line->fill($request->validated());
            $line->save();

            return response()->json([
                'message' => 'Linha atualizada com sucesso!',
                'data' => $line,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }

    public function destroy(Line $line): JsonResponse
    {
        $this->authorize('delete', $line);

        try {
            $line->delete();

            return response()->json(['message' => 'Linha removida com sucesso!']);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }
}

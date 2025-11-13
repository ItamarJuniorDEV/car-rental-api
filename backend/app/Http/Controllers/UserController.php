<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $users = User::select('id', 'name', 'email', 'role', 'created_at')->get();

        return response()->json([
            'message' => 'Usuários listados com sucesso!',
            'data' => $users,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,user'],
        ]);

        try {
            $user = new User;
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->password = Hash::make($validated['password']);
            $user->role = $validated['role'];
            $user->save();

            return response()->json([
                'message' => 'Usuário criado com sucesso!',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                ],
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }

    public function updateRole(Request $request, int $id): JsonResponse
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validated = $request->validate([
            'role' => ['required', 'in:admin,user'],
        ]);

        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        if ($user->id === auth()->id()) {
            return response()->json(['erro' => 'Não é possível alterar o próprio perfil.'], 422);
        }

        try {
            $user->role = $validated['role'];
            $user->save();

            return response()->json([
                'message' => 'Perfil atualizado com sucesso!',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                ],
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }
}

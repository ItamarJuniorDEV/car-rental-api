<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = User::select('id', 'name', 'email', 'role', 'created_at')->get();

        return response()->json([
            'message' => 'Usuários listados com sucesso!',
            'data' => UserResource::collection($users)->resolve(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,user'],
        ]);

        $user = new User;
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->role = $validated['role'];
        $user->save();

        return response()->json([
            'message' => 'Usuário criado com sucesso!',
            'data' => (new UserResource($user))->resolve(),
        ], 201);
    }

    public function updateRole(UpdateUserRoleRequest $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        if ($user->id === auth()->id()) {
            return response()->json(['erro' => 'Não é possível alterar o próprio perfil.'], 422);
        }

        $user->role = $request->validated('role');
        $user->save();

        return response()->json([
            'message' => 'Perfil atualizado com sucesso!',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ],
        ]);
    }
}

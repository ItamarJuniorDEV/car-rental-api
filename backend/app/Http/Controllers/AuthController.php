<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Timebox;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = new User;
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->save();

        return response()->json(['token' => $user->createToken('auth')->plainTextToken], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $token = (new Timebox)->call(function () use ($request) {
            $user = User::where('email', $request->input('email'))->first();

            $hash = $user?->password ?? '$2y$12$placeholderplaceholderplaceholderplaceholderplaceholde';
            $passwordOk = Hash::check($request->input('password'), $hash);

            if (! $user || ! $passwordOk) {
                throw ValidationException::withMessages([
                    'email' => ['Credenciais inválidas.'],
                ]);
            }

            $user->tokens()->delete();

            return $user->createToken('auth')->plainTextToken;
        }, 500_000);

        return response()->json(['token' => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }
}

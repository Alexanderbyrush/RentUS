<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $users = User::included()->filter()->sort()->getOrPaginate();
        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Método no permitido'], 405);
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Validación
            $request->validate([
                'bio' => 'nullable|string|max:500',
                'department' => 'nullable|string|max:100',
                'city' => 'nullable|string|max:100',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255|unique:users,email,' . $id,
            ]);

            // IMPORTANTE: Manejo de foto en base64
            if ($request->hasFile('photo')) {
                $image = $request->file('photo');
                
                // Validar que el archivo sea válido
                if ($image->isValid()) {
                    $imageData = file_get_contents($image->getRealPath());
                    $base64 = base64_encode($imageData);
                    $mimeType = $image->getMimeType();
                    
                    // Formato: data:image/jpeg;base64,/9j/4AAQ...
                    $user->photo = 'data:' . $mimeType . ';base64,' . $base64;
                    
                    Log::info('Foto actualizada para usuario: ' . $id);
                }
            }

            // Actualizar otros campos solo si vienen en la request
            if ($request->has('bio')) {
                $user->bio = $request->input('bio');
            }
            
            if ($request->has('department')) {
                $user->department = $request->input('department');
            }
            
            if ($request->has('city')) {
                $user->city = $request->input('city');
            }
            
            if ($request->has('name')) {
                $user->name = $request->input('name');
            }
            
            if ($request->has('email')) {
                $user->email = $request->input('email');
            }

            $user->save();

            return response()->json([
                'message' => 'Perfil actualizado correctamente',
                'user' => $user
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error actualizando usuario: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar el perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }
}
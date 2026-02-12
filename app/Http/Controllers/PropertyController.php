<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    /**
     * Listado con filtros, paginación y ordenamiento
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        // Construir query con filtros
        $query = Property::query();

        // 🔍 FILTRO DE BÚSQUEDA (search)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // 🏷️ FILTRO POR ESTADO DE DISPONIBILIDAD (status)
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // ✅ FILTRO POR ESTADO DE APROBACIÓN (approval_status)
        if ($request->has('approval_status') && !empty($request->approval_status)) {
            $query->where('approval_status', $request->approval_status);
        }

        // 👁️ FILTRO POR VISIBILIDAD (visibility)
        if ($request->has('visibility') && !empty($request->visibility)) {
            $query->where('visibility', $request->visibility);
        }

        // 🌍 FILTRO POR CIUDAD (city)
        if ($request->has('city') && !empty($request->city)) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        // 💰 FILTRO POR PRECIO MÍNIMO (min_price)
        if ($request->has('min_price') && !empty($request->min_price)) {
            $query->where('monthly_price', '>=', $request->min_price);
        }

        // 💰 FILTRO POR PRECIO MÁXIMO (max_price)
        if ($request->has('max_price') && !empty($request->max_price)) {
            $query->where('monthly_price', '<=', $request->max_price);
        }

        // 📊 ORDENAMIENTO (sort_by y sort_order)
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        // Validar campos permitidos para ordenar
        $allowedSortFields = [
            'id',
            'title',
            'city',
            'monthly_price',
            'area_m2',
            'views',
            'created_at',
            'updated_at',
            'status',
            'approval_status',
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // 🔗 INCLUIR RELACIONES (eager loading)
        $query->with('user:id,name,email,phone,photo');

        // 📄 PAGINACIÓN
        $properties = $query->paginate($perPage);

        // 📋 LOG PARA DEBUG
        Log::info('🔍 Filtros aplicados en propiedades:', [
            'search' => $request->search,
            'status' => $request->status,
            'approval_status' => $request->approval_status,
            'visibility' => $request->visibility,
            'city' => $request->city,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
            'resultados' => $properties->total(),
        ]);

        return response()->json([
            'data' => $properties->items(),
            'meta' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ]
        ]);
    }

    /**
     * Mostrar propiedad por ID
     */
    public function show(Property $property)
    {
        $property->load('user:id,name,email,phone,photo');

        return response()->json([
            'success' => true,
            'data' => $property
        ]);
    }

    /**
     * Crear nueva propiedad
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'description'       => 'required|string',
            'address'           => 'required|string',
            'city'              => 'nullable|string|max:120',
            'status'            => 'nullable|string|in:available,rented,maintenance',
            'monthly_price'     => 'required|numeric|min:0',
            'area_m2'           => 'nullable|numeric|min:0',
            'num_bedrooms'      => 'nullable|integer|min:0',
            'num_bathrooms'     => 'nullable|integer|min:0',
            'included_services' => 'nullable|string', // Recibir como string JSON
            'image'             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'image_url'         => 'nullable|string',
            'lat'               => 'nullable|numeric', // ⭐ Ahora nullable
            'lng'               => 'nullable|numeric', // ⭐ Ahora nullable
            'accuracy'          => 'nullable|numeric',
            'user_id'           => 'nullable|integer|exists:users,id', // Campo para asignar usuario
            // ⭐ NO validamos publication_date porque lo asignamos automáticamente
        ]);

        // Parsear included_services si viene como string JSON
        if (isset($validated['included_services']) && is_string($validated['included_services'])) {
            $validated['included_services'] = json_decode($validated['included_services'], true) ?? [];
        }

        // Manejar subida de imagen si existe
        if ($request->hasFile('image')) {
            try {
                $image = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('properties', $filename, 'public');
                $validated['image_url'] = asset('storage/' . $path);
            } catch (\Exception $e) {
                Log::error('Error uploading image: ' . $e->getMessage());
            }
        }

        // ⭐ Asignar user_id: primero el proporcionado, luego el autenticado
        if (!isset($validated['user_id'])) {
            $validated['user_id'] = auth()->id();
        }

        // ⭐ SIEMPRE establecer publication_date con la fecha actual
        $validated['publication_date'] = now()->format('Y-m-d');

        // Si es admin/support creando, aprobar automáticamente
        $user = auth()->user();
        if (in_array($user->role, ['admin', 'support'])) {
            $validated['approval_status'] = 'approved';
            $validated['visibility'] = 'published';
        }

        $property = Property::create($validated);
        $property->load('user:id,name,email,phone,photo');

        return response()->json([
            'success'  => true,
            'message'  => 'Propiedad creada exitosamente',
            'property' => $property
        ], 201);
    }

    /**
     * Actualizar propiedad
     */
    public function update(Request $request, Property $property)
    {
        // 🔒 VALIDACIÓN DE PERMISO → Solo el dueño o admin/support pueden editar
        $user = auth()->user();
        $isOwner = $property->user_id === $user->id;
        $isAdmin = in_array($user->role, ['admin', 'support']);

        if (!$isOwner && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para editar esta propiedad'
            ], 403);
        }

        $validated = $request->validate([
            'title'             => 'sometimes|string|max:255',
            'description'       => 'sometimes|string',
            'address'           => 'sometimes|string',
            'city'              => 'sometimes|string|max:120',
            'status'            => 'sometimes|string|in:available,rented,maintenance',
            'monthly_price'     => 'sometimes|numeric|min:0',
            'area_m2'           => 'sometimes|numeric|min:0',
            'num_bedrooms'      => 'sometimes|integer|min:0',
            'num_bathrooms'     => 'sometimes|integer|min:0',
            'included_services' => 'sometimes|string', // Recibir como string JSON
            'image_url'         => 'sometimes|string|nullable',
            'image'             => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:5120',
            'lat'               => 'sometimes|numeric',
            'lng'               => 'sometimes|numeric',
        ]);

        // Parsear included_services si viene como string JSON
        if (isset($validated['included_services']) && is_string($validated['included_services'])) {
            $validated['included_services'] = json_decode($validated['included_services'], true) ?? [];
        }

        // Manejar subida de imagen si existe
        if ($request->hasFile('image')) {
            try {
                // Eliminar imagen anterior si existe
                if ($property->image_url) {
                    $oldPath = str_replace(asset('storage/'), '', $property->image_url);
                    Storage::disk('public')->delete($oldPath);
                }

                $image = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('properties', $filename, 'public');
                $validated['image_url'] = asset('storage/' . $path);
            } catch (\Exception $e) {
                Log::error('Error uploading image: ' . $e->getMessage());
            }
        }

        $property->update($validated);
        $property->load('user:id,name,email,phone,photo');

        return response()->json([
            'success'  => true,
            'message'  => 'Propiedad actualizada correctamente',
            'property' => $property
        ]);
    }

    /**
     * Eliminar propiedad
     */
    public function destroy(Property $property)
    {
        // 🔒 VALIDACIÓN DE PERMISO → Solo el dueño o admin/support pueden eliminar
        $user = auth()->user();
        $isOwner = $property->user_id === $user->id;
        $isAdmin = in_array($user->role, ['admin', 'support']);

        if (!$isOwner && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para eliminar esta propiedad'
            ], 403);
        }

        // Eliminar imagen si existe
        if ($property->image_url) {
            try {
                $path = str_replace(asset('storage/'), '', $property->image_url);
                Storage::disk('public')->delete($path);
            } catch (\Exception $e) {
                Log::error('Error deleting image: ' . $e->getMessage());
            }
        }

        $property->delete();

        return response()->json([
            'success' => true,
            'message' => 'Propiedad eliminada correctamente'
        ]);
    }

    /**
     * Guardar punto geográfico de la propiedad
     */
    public function savePoint(Request $request, $id)
    {
        $validated = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $property = Property::findOrFail($id);

        // 🔒 VALIDACIÓN DE PERMISO
        $user = auth()->user();
        $isOwner = $property->user_id === $user->id;
        $isAdmin = in_array($user->role, ['admin', 'support']);

        if (!$isOwner && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para actualizar la ubicación'
            ], 403);
        }

        $property->update([
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Ubicación guardada correctamente',
            'property' => $property
        ]);
    }

    /**
     * Contar total de propiedades
     */
    public function count()
    {
        return response()->json([
            'count' => Property::count()
        ]);
    }

    /**
     * Incrementar vistas de una propiedad
     */
    public function incrementViews($id)
    {
        $property = Property::findOrFail($id);
        $property->incrementViews();

        return response()->json([
            'success' => true,
            'message' => 'Visita registrada',
            'views' => $property->views
        ]);
    }
}

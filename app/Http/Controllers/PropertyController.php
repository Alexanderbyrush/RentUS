<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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

        // 🔗 INCLUIR RELACIONES (eager loading) - AGREGADAS LAS IMÁGENES
        $query->with([
            'user:id,name,email,phone,photo',
            'images' => function ($q) {
                $q->orderBy('order');
            }
        ]);

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
        $property->load([
            'user:id,name,email,phone,photo',
            'images' => function ($q) {
                $q->orderBy('order');
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $property
        ]);
    }

    /**
     * Crear nueva propiedad con múltiples imágenes
     */
    /**
     * Crear nueva propiedad con múltiples imágenes
     */
    public function store(Request $request)
    {
        try {
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
                'included_services' => 'nullable|string',
                'lat'               => 'nullable|numeric',
                'lng'               => 'nullable|numeric',
                'accuracy'          => 'nullable|numeric',
                'user_id'           => 'nullable|integer|exists:users,id',
                'images'            => 'nullable|string',
                'publication_date'  => 'nullable|date',
            ]);

            DB::beginTransaction();

            // 🔥 PROCESAR included_services - DEBE SER STRING JSON
            if (isset($validated['included_services'])) {
                // Si ya es un string JSON, dejarlo como está
                if (is_string($validated['included_services'])) {
                    // Validar que sea JSON válido
                    $decoded = json_decode($validated['included_services'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $validated['included_services'] = '[]';
                    }
                }
            } else {
                $validated['included_services'] = '[]';
            }

            // Asignar user_id
            if (!isset($validated['user_id'])) {
                $validated['user_id'] = auth()->id();
            }

            // Asignar publication_date si no existe
            if (!isset($validated['publication_date']) || empty($validated['publication_date'])) {
                $validated['publication_date'] = now()->format('Y-m-d');
            }

            // Si es admin/support, aprobar automáticamente
            $user = auth()->user();
            if ($user && in_array($user->role, ['admin', 'support'])) {
                $validated['approval_status'] = 'approved';
                $validated['visibility'] = 'published';
            }

            // 🔥 Extraer imágenes antes de crear propiedad
            $imagesData = $validated['images'] ?? null;
            unset($validated['images']);

            // Inicializar image_url como null
            $validated['image_url'] = null;

            Log::info('📝 Datos validados para crear propiedad:', [
                'title' => $validated['title'],
                'user_id' => $validated['user_id'],
                'included_services' => $validated['included_services'],
                'tiene_imagenes' => !empty($imagesData)
            ]);

            // Crear la propiedad
            $property = Property::create($validated);

            Log::info('✅ Propiedad creada con ID: ' . $property->id);

            // 🔥 PROCESAR MÚLTIPLES IMÁGENES BASE64
            if ($imagesData) {
                $imagesArray = json_decode($imagesData, true);

                if (is_array($imagesArray) && count($imagesArray) > 0) {
                    Log::info('📸 Procesando ' . count($imagesArray) . ' imágenes');

                    foreach ($imagesArray as $index => $base64Image) {
                        try {
                            // Decodificar base64
                            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                                $type = strtolower($type[1]);

                                // Validar tipo de imagen
                                if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                                    Log::warning("⚠️ Tipo de imagen no válido: {$type}");
                                    continue;
                                }

                                $imageData = base64_decode($base64Image);

                                if ($imageData === false) {
                                    Log::warning("⚠️ Error decodificando base64 para imagen {$index}");
                                    continue;
                                }

                                // Validar tamaño (10MB máximo)
                                $sizeInMB = strlen($imageData) / (1024 * 1024);
                                if ($sizeInMB > 10) {
                                    Log::warning("⚠️ Imagen {$index} excede 10MB: {$sizeInMB}MB");
                                    continue;
                                }

                                // Generar nombre único
                                $filename = 'property_' . $property->id . '_' . time() . '_' . $index . '.' . $type;
                                $path = 'properties/' . $filename;

                                // Guardar imagen en storage/public
                                Storage::disk('public')->put($path, $imageData);
                                $imageUrl = asset('storage/' . $path);

                                Log::info("✅ Imagen {$index} guardada: {$filename}");

                                // Crear registro en property_images
                                PropertyImage::create([
                                    'property_id' => $property->id,
                                    'image_url' => $imageUrl,
                                    'order' => $index,
                                    'is_main' => $index === 0
                                ]);

                                // Si es la primera imagen, actualizar image_url en properties
                                if ($index === 0) {
                                    $property->update(['image_url' => $imageUrl]);
                                    Log::info("✅ image_url principal actualizado");
                                }
                            } else {
                                Log::warning("⚠️ Formato base64 inválido para imagen {$index}");
                            }
                        } catch (\Exception $e) {
                            Log::error("❌ Error procesando imagen {$index}: " . $e->getMessage());
                            Log::error($e->getTraceAsString());
                        }
                    }
                } else {
                    Log::warning('⚠️ El campo images no contiene un array válido');
                }
            } else {
                Log::info('ℹ️ No se proporcionaron imágenes');
            }

            DB::commit();

            // Cargar relaciones
            $property->load([
                'user:id,name,email,phone,photo',
                'images' => function ($q) {
                    $q->orderBy('order');
                }
            ]);

            Log::info('🎉 Propiedad creada exitosamente con ' . $property->images->count() . ' imágenes');

            return response()->json([
                'success'  => true,
                'message'  => 'Propiedad creada exitosamente',
                'property' => $property
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('❌ Error de validación:', $e->errors());

            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error creando propiedad: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la propiedad',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Actualizar propiedad
     */
    public function update(Request $request, Property $property)
    {
        // 🔒 VALIDACIÓN DE PERMISO
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
            'included_services' => 'sometimes|string',
            'lat'               => 'sometimes|numeric',
            'lng'               => 'sometimes|numeric',
            'images'            => 'sometimes|string', // Nuevas imágenes en base64
            'delete_images'     => 'sometimes|array', // IDs de imágenes a eliminar
            'delete_images.*'   => 'integer|exists:property_images,id'
        ]);

        try {
            DB::beginTransaction();

            // Parsear included_services
            if (isset($validated['included_services']) && is_string($validated['included_services'])) {
                $validated['included_services'] = json_decode($validated['included_services'], true) ?? [];
            }

            // Eliminar imágenes marcadas
            if (isset($validated['delete_images'])) {
                $imagesToDelete = PropertyImage::whereIn('id', $validated['delete_images'])
                    ->where('property_id', $property->id)
                    ->get();

                foreach ($imagesToDelete as $image) {
                    // Eliminar archivo físico
                    $path = str_replace(asset('storage/'), '', $image->image_url);
                    Storage::disk('public')->delete($path);

                    // Eliminar registro
                    $image->delete();
                }
            }

            // Procesar nuevas imágenes
            if (isset($validated['images'])) {
                $imagesArray = json_decode($validated['images'], true);

                if (is_array($imagesArray) && count($imagesArray) > 0) {
                    $currentMaxOrder = PropertyImage::where('property_id', $property->id)->max('order') ?? -1;

                    foreach ($imagesArray as $index => $base64Image) {
                        try {
                            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                                $type = strtolower($type[1]);

                                if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                                    continue;
                                }

                                $base64Image = base64_decode($base64Image);

                                if ($base64Image === false) {
                                    continue;
                                }

                                $filename = time() . '_' . $index . '_' . uniqid() . '.' . $type;
                                $path = 'properties/' . $filename;

                                Storage::disk('public')->put($path, $base64Image);

                                PropertyImage::create([
                                    'property_id' => $property->id,
                                    'image_url' => asset('storage/' . $path),
                                    'order' => $currentMaxOrder + $index + 1,
                                    'is_main' => false
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::error('Error procesando imagen ' . $index . ': ' . $e->getMessage());
                        }
                    }
                }
            }

            // Remover campos no actualizables
            unset($validated['images'], $validated['delete_images']);

            // Actualizar propiedad
            $property->update($validated);

            DB::commit();

            // Cargar relaciones
            $property->load([
                'user:id,name,email,phone,photo',
                'images' => function ($q) {
                    $q->orderBy('order');
                }
            ]);

            return response()->json([
                'success'  => true,
                'message'  => 'Propiedad actualizada correctamente',
                'property' => $property
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando propiedad: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la propiedad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar propiedad
     */
    public function destroy(Property $property)
    {
        // 🔒 VALIDACIÓN DE PERMISO
        $user = auth()->user();
        $isOwner = $property->user_id === $user->id;
        $isAdmin = in_array($user->role, ['admin', 'support']);

        if (!$isOwner && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para eliminar esta propiedad'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Eliminar todas las imágenes asociadas
            $images = PropertyImage::where('property_id', $property->id)->get();

            foreach ($images as $image) {
                $path = str_replace(asset('storage/'), '', $image->image_url);
                Storage::disk('public')->delete($path);
                $image->delete();
            }

            // Eliminar imagen principal si existe (compatibilidad)
            if ($property->image_url) {
                try {
                    $path = str_replace(asset('storage/'), '', $property->image_url);
                    Storage::disk('public')->delete($path);
                } catch (\Exception $e) {
                    Log::error('Error deleting main image: ' . $e->getMessage());
                }
            }

            $property->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Propiedad eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error eliminando propiedad: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la propiedad'
            ], 500);
        }
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

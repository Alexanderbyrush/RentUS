<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /**
     * Listado con filtros, includes y paginación inteligente.
     */
    public function index()
    {
        $properties = Property::included()->filter()->sort()->getOrPaginate();
        return response()->json($properties);
    }

    /**
     * Mostrar propiedad por ID.
     * Usa Route Model Binding.
     */
    public function show(Property $property)
    {
        return response()->json($property);
    }
<<<<<<< HEAD
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'description'       => 'required|string',
            'address'           => 'required|string',
            'city'              => 'nullable|string',
            'status'            => 'nullable|string',
            'monthly_price'     => 'required|numeric',
            'area_m2'           => 'nullable|numeric',
            'num_bedrooms'      => 'nullable|integer',
            'num_bathrooms'     => 'nullable|integer',
            'included_services' => 'nullable|array',
            'publication_date'  => 'nullable|date',
            'image_url'         => 'nullable|string',
            'lat'               => 'nullable|numeric',
            'lng'               => 'nullable|numeric',
        ]);

        $validated['user_id'] = auth()->id(); // IMPORTANTE

        $property = Property::create($validated);

        return response()->json([
            'message' => 'Property created successfully',
            'property' => $property
        ], 201);
    }
=======

    /**
     * Crear nueva propiedad.
     * (NO TOCADO — se mantiene exactamente como lo tenías)
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
            'included_services' => 'nullable|array',
            'publication_date'  => 'nullable|date',
            'image_url'         => 'nullable|string',
            'lat'               => 'nullable|numeric',
            'lng'               => 'nullable|numeric',
        ]);

        $validated['user_id'] = auth()->id();

        $property = Property::create($validated);
>>>>>>> 6ae3f06e19d219ed2da797549a33b90f6ee70f7a

        return response()->json([
            'message'  => 'Property created successfully',
            'property' => $property
        ], 201);
    }

    /**
     * Editar / actualizar propiedad.
     * Compatible con PUT /properties/{property}
     */
    public function update(Request $request, Property $property)
    {
        // 🔒 VALIDACIÓN DE PERMISO → Solo el dueño puede editar
        if ($property->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'You are not allowed to update this property'
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
            'included_services' => 'sometimes|array',
            'publication_date'  => 'sometimes|date',
            'image_url'         => 'sometimes|string',
            'lat'               => 'sometimes|numeric',
            'lng'               => 'sometimes|numeric',
        ]);

        $property->update($validated);

        return response()->json([
            'message'  => 'Updated successfully',
            'property' => $property
        ]);
    }

    /**
     * Eliminar propiedad.
     * Compatible con DELETE /properties/{property}
     */
    public function destroy(Property $property)
    {
        // 🔒 VALIDACIÓN DE PERMISO → Solo el dueño puede eliminar
        if ($property->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'You are not allowed to delete this property'
            ], 403);
        }

        $property->delete();

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }

    /**
     * Guardar punto geográfico de la propiedad.
     */
    public function savePoint(Request $request, $id)
    {
        $validated = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $property = Property::findOrFail($id);

        // 🔒 VALIDACIÓN DE PERMISO → Solo el dueño actualiza mapa
        if ($property->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'You are not allowed to update location'
            ], 403);
        }

        $property->update([
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
        ]);

        return response()->json([
            'message'  => 'Point saved successfully',
            'property' => $property
        ]);
    }
<<<<<<< HEAD
=======

    /**
     * Contar total de propiedades.
     */
    public function count()
    {
        return response()->json([
            'count' => Property::count()
        ]);
    }
>>>>>>> 6ae3f06e19d219ed2da797549a33b90f6ee70f7a
}

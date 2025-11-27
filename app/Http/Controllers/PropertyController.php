<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::included()->filter()->sort()->getOrPaginate();
        return response()->json($properties);
    }

    public function show(Property $property)
    {
        return response()->json($property);
    }
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


    public function update(Request $request, Property $property)
    {
        $property->update($request->all());
        return response()->json([
            'message' => 'Updated successfully',
            'property' => $property
        ]);
    }

    public function destroy(Property $property)
    {
        $property->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    // === Guardar punto geográfico ===
    public function savePoint(Request $request, $id)
    {
        $validated = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $property = Property::findOrFail($id);

        $property->lat = $validated['lat'];
        $property->lng = $validated['lng'];
        $property->save();

        return response()->json([
            'message' => 'Point saved successfully',
            'property' => $property
        ]);
    }
}

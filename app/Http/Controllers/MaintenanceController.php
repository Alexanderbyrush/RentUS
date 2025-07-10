<?php

namespace App\Http\Controllers;
use App\Models\Maintenance;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{

    public function index()
    {
        // $maintenances = Maintenance::included()->get();
        // return response()->json($maintenances);

        $maintenances = Maintenance::included()->filter()->get();
        return response()->json($maintenances);
    }

    public function show(Maintenance $maintenance)
    {
        //
    }

    public function edit(Maintenance $maintenance)
    {
        //
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        //
    }

    public function destroy(Maintenance $maintenance)
    {
        //
    }
}



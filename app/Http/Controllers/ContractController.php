<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Traits\GeneratesPayments;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    use GeneratesPayments;
    public function index()
    {
        $userId = auth()->id();

        $contracts = Contract::with(['property', 'landlord', 'tenant'])
            ->where(function ($q) use ($userId) {
                $q->where('landlord_id', $userId)
                    ->orWhere('tenant_id', $userId);
            })
            ->get();

        return response()->json($contracts);
    }


    public function stats()
    {
        $userId = auth()->id();

        $active = Contract::where(function ($q) use ($userId) {
            $q->where('landlord_id', $userId)
                ->orWhere('tenant_id', $userId);
        })->where('status', 'active')->count();

        $pending = Contract::where(function ($q) use ($userId) {
            $q->where('landlord_id', $userId)
                ->orWhere('tenant_id', $userId);
        })->where('status', 'pending')->count();

        $total = Contract::where(function ($q) use ($userId) {
            $q->where('landlord_id', $userId)
                ->orWhere('tenant_id', $userId);
        })->count();

        return response()->json([
            'active' => $active,
            'pending' => $pending,
            'total' => $total,
        ]);
    }



    public function show(Contract $contract)
    {
        //
    }

    public function edit(Contract $contract)
    {
        //
    }

    public function update(Request $request, Contract $contract)
    {
      
        $request->validate([
            // tus reglas de validación
            'status' => 'required|string|in:pendiente_revision,activo,finalizado'
        ]);

        $oldStatus = $contract->status;
        $contract->update($request->all());

        // ✅ Lógica de generación de pagos al ACTIVAR el contrato
        if ($oldStatus !== 'activo' && $contract->status === 'activo') {
            $this->generateMonthlyPayments($contract);
        }

        return response()->json($contract->load(['property', 'landlord', 'tenant']));
    
    }

    public function destroy(Contract $contract)
    {
        //
    }
    
}

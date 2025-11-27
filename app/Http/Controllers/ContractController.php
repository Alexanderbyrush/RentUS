<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\Property;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ContractController extends Controller
{
    /**
     * Obtener todos los contratos del usuario (como inquilino o dueño)
     */
    public function index()
    {
        $userId = Auth::id();

        $contracts = Contract::where('tenant_id', $userId)
            ->orWhere('landlord_id', $userId)
            ->with(['property', 'tenant', 'landlord'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($contracts);
    }

    /**
     * Obtener estadísticas de contratos
     */
    public function stats()
    {
        $userId = Auth::id();

        $total = Contract::where('tenant_id', $userId)
            ->orWhere('landlord_id', $userId)
            ->count();

        $active = Contract::where(function($query) use ($userId) {
                $query->where('tenant_id', $userId)
                    ->orWhere('landlord_id', $userId);
            })
            ->where('status', 'active')
            ->count();

        $pending = Contract::where(function($query) use ($userId) {
                $query->where('tenant_id', $userId)
                    ->orWhere('landlord_id', $userId);
            })
            ->where('status', 'pending')
            ->count();

        return response()->json([
            'total' => $total,
            'active' => $active,
            'pending' => $pending,
        ]);
    }

    /**
     * Inquilino acepta el contrato
     */
    public function acceptContract($id)
    {
        $contract = Contract::findOrFail($id);

        // Verificar que es el inquilino
        if ($contract->tenant_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Verificar que está pendiente
        if ($contract->status !== 'pending') {
            return response()->json(['message' => 'El contrato no está pendiente'], 400);
        }

        // Actualizar contrato
        $contract->update([
            'status' => 'active',
            'accepted_by_tenant' => 'yes',
            'tenant_acceptance_date' => Carbon::now(),
        ]);

        // Actualizar estado de la propiedad
        Property::where('id', $contract->property_id)
            ->update(['status' => 'rented']);

        // Notificar al dueño
        Notification::create([
            'user_id' => $contract->landlord_id,
            'type' => 'contract_accepted',
            'title' => '✅ Contrato aceptado',
            'message' => '<strong>' . Auth::user()->name . '</strong> aceptó el contrato de <strong>' . $contract->property->title . '</strong>. El contrato está ahora activo.',
            'data' => json_encode([
                'contract_id' => $contract->id,
                'property_id' => $contract->property_id,
            ]),
        ]);

        return response()->json([
            'message' => 'Contrato aceptado exitosamente',
            'data' => $contract->load(['property', 'tenant', 'landlord'])
        ]);
    }

    /**
     * Inquilino rechaza el contrato
     */
    public function rejectContract($id)
    {
        $contract = Contract::findOrFail($id);

        // Verificar que es el inquilino
        if ($contract->tenant_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Verificar que está pendiente
        if ($contract->status !== 'pending') {
            return response()->json(['message' => 'El contrato no está pendiente'], 400);
        }

        // Actualizar contrato
        $contract->update([
            'status' => 'rejected',
            'accepted_by_tenant' => 'no',
        ]);

        // Notificar al dueño
        Notification::create([
            'user_id' => $contract->landlord_id,
            'type' => 'contract_rejected',
            'title' => '❌ Contrato rechazado',
            'message' => '<strong>' . Auth::user()->name . '</strong> rechazó el contrato de <strong>' . $contract->property->title . '</strong>.',
            'data' => json_encode([
                'contract_id' => $contract->id,
                'property_id' => $contract->property_id,
            ]),
        ]);

        return response()->json([
            'message' => 'Contrato rechazado',
            'data' => $contract
        ]);
    }

    /**
     * Obtener detalles de un contrato específico
     */
    public function show($id)
    {
        $contract = Contract::with(['property', 'tenant', 'landlord'])
            ->findOrFail($id);

        $userId = Auth::id();

        // Verificar que el usuario tiene permiso
        if ($contract->tenant_id !== $userId && $contract->landlord_id !== $userId) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json($contract);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use PDF; // Si usas Laravel Snappy o un generador de PDF (Simulación)

class PaymentController extends Controller
{
    // ... (Métodos CRUD: index, show, store, update, destroy)
    
    /**
     * Obtiene los pagos pendientes del usuario autenticado (inquilino).
     */
    public function myPendingPayments()
    {
        $tenantId = Auth::id();

        // 1. Obtener todos los pagos donde el usuario logueado es el inquilino del contrato
        // 2. Filtrar por estado 'pendiente'
        $payments = Payment::whereHas('contract', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->where('status', 'pendiente')
            ->with('contract.property') // Cargar info relevante
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json($payments);
    }

    /**
     * Simula el pago de un recibo.
     */
    public function simulatePayment(Request $request, Payment $payment)
    {
        $request->validate([
            'payment_method' => 'required|string|in:tarjeta,pse,nequi,bancolombia,daviplata',
            // Puedes agregar más validaciones aquí para la simulación (ej. token_pago)
        ]);

        // 1. Validar que el pago esté PENDIENTE
        if ($payment->status !== 'pendiente') {
            return response()->json(['error' => 'El recibo ya ha sido pagado.'], 400);
        }

        // 2. Validar que el usuario logueado sea el inquilino (seguridad)
        $tenantId = Auth::id();
        if ($payment->contract->tenant_id !== $tenantId) {
            return response()->json(['error' => 'No tienes permiso para pagar este recibo.'], 403);
        }

        // 3. Simular la transacción exitosa y actualizar el registro
        $payment->update([
            'status' => 'pagado',
            'payment_method' => $request->payment_method,
            'payment_date' => Carbon::now(),
            'receipt_path' => 'receipts/' . $payment->id . '-' . time() . '.pdf', // Simulación de ruta
        ]);
        
        // **OPCIONAL:** Aquí podrías enviar un evento o notificación de pago

        return response()->json([
            'message' => 'Pago simulado exitosamente.',
            'payment' => $payment->load('contract.property')
        ]);
    }

    /**
     * Genera y descarga un comprobante de pago simulado.
     */
    public function downloadReceipt(Payment $payment)
    {
        // 1. Validar que el pago esté REALIZADO
        if ($payment->status !== 'pagado') {
            return response()->json(['error' => 'El recibo no ha sido pagado y no tiene comprobante.'], 400);
        }
        
        // 2. Validar que el usuario logueado sea el inquilino (seguridad)
        $tenantId = Auth::id();
        if ($payment->contract->tenant_id !== $tenantId) {
            return response()->json(['error' => 'No tienes permiso para acceder a este comprobante.'], 403);
        }
        
        // 3. Simulación de generación de PDF
        // Si no tienes un generador de PDF (como Laravel Snappy), solo retorna la data.
        
        $data = [
            'title' => 'Comprobante de Pago de Arriendo',
            'date' => Carbon::now()->format('Y-m-d'),
            'payment' => $payment->load('contract.property', 'contract.landlord', 'contract.tenant'),
            'monto' => number_format($payment->amount, 2),
            'metodo' => $payment->payment_method,
            'propiedad' => $payment->contract->property->address,
        ];
        
        // return PDF::loadView('receipt_template', $data)->download('comprobante-pago-' . $payment->id . '.pdf');
        
        // Simulación: Retornar los datos del comprobante para el frontend
        return response()->json([
            'message' => 'Datos listos para generar el comprobante (simulación).',
            'receipt_data' => $data
        ]);
    }
}
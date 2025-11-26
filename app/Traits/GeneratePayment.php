<?php

namespace App\Traits;

use App\Models\Contract;
use App\Models\Payment;
use Carbon\Carbon;

trait GeneratesPayments
{
    /**
     * Genera los recibos de pago mensuales para un contrato activo.
     * @param Contract $contract
     */
    public function generateMonthlyPayments(Contract $contract)
    {
        // 1. Obtener el precio mensual de la propiedad
        $monthlyPrice = $contract->property->monthly_price;
        
        // 2. Obtener fechas del contrato
        $startDate = Carbon::parse($contract->start_date);
        $endDate = Carbon::parse($contract->end_date);

        // 3. Iterar de mes a mes para crear los pagos
        // Usamos el día del mes de inicio como el día de vencimiento (Ej: 5 de cada mes)
        $billingDay = $startDate->day;

        // Clonamos la fecha de inicio para iterar
        $currentDate = $startDate->copy();

        // Limite para evitar bucles infinitos por si las fechas no están bien (máximo 120 años = 1440 pagos)
        $limit = 0; 
        
        while ($currentDate->lessThanOrEqualTo($endDate) && $limit < 1440) {
            
            // Si es el primer mes, el pago vence un mes después de la fecha de inicio.
            // Si el contrato inicia el 10/11/2025, el primer pago vence el 10/12/2025.
            if ($currentDate->equalTo($startDate)) {
                $dueDate = $currentDate->copy()->addMonth()->setDay($billingDay);
            } else {
                $dueDate = $currentDate->copy()->setDay($billingDay);
            }

            // Asegurar que el día no exceda el fin del mes (Ej: 31 de Feb)
            if ($dueDate->month != $currentDate->month + 1 && $dueDate->month != $currentDate->month) {
                $dueDate = $dueDate->lastOfMonth();
            }

            // Solo generar hasta la fecha de fin del contrato
            if ($dueDate->greaterThan($endDate->endOfDay())) {
                break; 
            }

            Payment::create([
                'contract_id' => $contract->id,
                'due_date' => $dueDate,
                'amount' => $monthlyPrice,
                'status' => 'pendiente', // Por defecto es pendiente
                'payment_method' => null,
                'receipt_path' => null,
            ]);

            // Avanzar al siguiente mes
            $currentDate->addMonth();
            $limit++;
        }
    }
}
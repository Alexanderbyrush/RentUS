<?php

namespace App\Console\Commands;

use Illuminate\Console\Command; 
use App\Models\Contract;
use App\Models\Payment;
use Carbon\Carbon;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'invoices:generate';
    protected $description = 'Genera los recibos mensuales para contratos activos.';

    public function handle()
    {
        $fechaObjetivo = Carbon::today()->addMonth()->startOfMonth(); // Emitir el 1er del próximo mes

        // 1. Buscar contratos activos
        $contratosActivos = Contract::where('status', 'activo')->get(); 

        foreach ($contratosActivos as $contrato) {
            // Asume que la tabla 'contracts' tiene 'monto_mensual' y 'fecha_inicio'
            $monto = $contrato->monto_mensual; 

            // 2. Verificar si ya existe un recibo pendiente o pagado para este periodo
            $reciboExistente = Payment::where('contract_id', $contrato->id)
                ->where('due_date', $fechaObjetivo->format('Y-m-d'))
                ->exists();

            if (!$reciboExistente) {
                // 3. Crear el nuevo recibo
                Payment::create([
                    'contract_id' => $contrato->id,
                    'due_date' => $fechaObjetivo,
                    'amount' => $monto,
                    'status' => 'pendiente',
                    // payment_date, payment_method, receipt_path son null
                ]);
                $this->info("Recibo generado para Contrato #{$contrato->id} con vencimiento en {$fechaObjetivo->format('Y-m-d')}");
            }
        }
        return 0;
    }
}
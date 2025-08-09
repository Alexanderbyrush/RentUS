<?php

namespace App\Models;

use App\Traits\HasSmartScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contract extends Model
{
    use HasFactory, HasSmartScopes;

    // Define los campos que pueden ser asignados masivamente al modelo
    protected $fillable = [
        'start_date',                 // Fecha de inicio del contrato
        'end_date',                   // Fecha de finalización del contrato
        'status',                     // Estado actual del contrato
        'document_path',              // Ruta del documento del contrato
        'validated_by_support',       // Validación por parte del equipo de soporte (booleano)
        'support_validation_date',    // Fecha en que fue validado por soporte
        'accepted_by_tenant',         // Aceptación por parte del inquilino (booleano)
        'tenant_acceptance_date',     // Fecha en que fue aceptado por el inquilino
        'property_id',                // ID de la propiedad relacionada
        'user_id'                     // ID del usuario (inquilino o arrendador)
    ];

    public function property(){return $this->belongsTo(Property::class);}

    public function payments(){return $this->hasMany(Payment::class);}

    public function ratings(){return $this->hasMany(Rating::class);}
    
    public function user(){return $this->belongsTo(User::class);}

    public function rentalRequest(){return $this->hasOne(RentalRequest::class);}

}

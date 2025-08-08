<?php

namespace App\Models;

use App\Traits\HasSmartScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RentalRequest extends Model
{
    use HasFactory, HasSmartScopes;
    // Campos que se pueden asignar masivamente (por create, update, etc.)
    protected $fillable = [
        'contract_id',   // ID del contrato asociado a la solicitud de alquiler
        'property_id',   // ID de la propiedad asociada a la solicitud de alquiler
        'user_id'        // ID del usuario que realiza la solicitud
    ];

    public function contract(){return $this->belongsTo(Contract::class);}

    public function property(){return $this->hasMany(Property::class);}

    public function user(){return $this->belongsTo(User::class);}

}

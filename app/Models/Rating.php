<?php

namespace App\Models;

use App\Traits\HasSmartScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Rating extends Model
{
    use HasFactory, HasSmartScopes;

    // Campos que se pueden asignar masivamente (por create, update, etc.)
    protected $fillable = [
        'recipient_role',    // Rol del destinatario (inquilino, propietario, etc.)
        'score',             // Puntuación otorgada (ej: 1 a 5)
        'comment',           // Comentario adicional sobre la calificación
        'date',              // Fecha de la calificación
        'contract_id',       // ID del contrato asociado a la calificación
        'user_id'            // ID del usuario que realizó la calificación
    ];

    public function contract(){return $this->belongsTo(Contract::class);}

    public function user(){return $this->belongsTo(User::class);}
    
}

<?php

namespace App\Models;

use App\Traits\HasSmartScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasFactory, HasSmartScopes;

    // Campos que se pueden asignar masivamente (por create, update, etc.)
    protected $fillable = [
        'title',             // Título de la propiedad
        'description',       // Descripción de la propiedad
        'address',           // Dirección de la propiedad
        'city',              // Ciudad donde se encuentra la propiedad 
        'status',            // Estado de la propiedad (disponible, alquilada, etc.)
        'monthly_price',     // Precio mensual de la propiedad
        'area_m2',           // Área de la propiedad en metros cuadrados
        'num_bedrooms',      // Número de habitaciones
        'num_bathrooms',     // Número de baños
        'included_services', // Servicios incluidos (agua, luz, internet, etc.)
        'publication_date',  // Fecha de publicación de la propiedad
        'image_url',         // URL de la imagen de la propiedad
        'user_id'            // ID del usuario propietario de la propiedad
    ];

    function user(){return $this->belongsTo(User::class);}

    function maintenances(){return $this->hasMany(Maintenance::class);}

    function contracts(){return $this->hasMany(Contract::class);}

    function rentalRequests(){return $this->hasMany(RentalRequest::class);}

}

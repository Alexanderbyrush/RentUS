<?php

namespace App\Models;

use App\Traits\HasSmartScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasFactory, HasSmartScopes;

    // Campos asignables masivamente
    protected $fillable = [
        'title',
        'description',
        'address',
        'city',
        'status',
        'monthly_price',
        'area_m2',
        'num_bedrooms',
        'num_bathrooms',
        'included_services',
        'publication_date',
        'image_url',
        'user_id',

        // Necesarios para integrar Leaflet y registrar coordenadas
        'lat',
        'lng'
    ];

    // Cast para JSON
    protected $casts = [
        'included_services' => 'array',
        'publication_date'  => 'date',
        'monthly_price'     => 'decimal:2',
        'lat'               => 'decimal:7',
        'lng'               => 'decimal:7',
    ];

    function user() { return $this->belongsTo(User::class); }
    function maintenances() { return $this->hasMany(Maintenance::class); }
    function contracts() { return $this->hasMany(Contract::class); }
    function rentalRequests() { return $this->hasMany(RentalRequest::class); }
}

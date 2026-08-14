<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'name', 'monthly_price', 'annual_price', 'description', 'featured', 'active', 'features'
    ];

    protected $casts = [
        'features' => 'array',
        'featured' => 'boolean',
        'active' => 'boolean'
    ];
}

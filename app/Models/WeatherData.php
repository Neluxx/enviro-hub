<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Weather Data Model
 */
class WeatherData extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'weather_data';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'location',
        'temperature',
        'feels_like',
        'humidity',
        'pressure',
        'wind_speed',
        'description',
        'measured_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'temperature' => 'decimal:2',
        'feels_like' => 'decimal:2',
        'humidity' => 'integer',
        'pressure' => 'integer',
        'wind_speed' => 'decimal:2',
        'measured_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

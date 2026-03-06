<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sensor Data Model
 */
class SensorData extends Model
{
    use HasFactory;

    /**
     * @inheritdoc
     */
    protected $table = 'sensor_data';

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'node_id',
        'node_uuid',
        'temperature',
        'humidity',
        'pressure',
        'carbon_dioxide',
        'measured_at',
    ];

    /**
     * @inheritdoc
     */
    protected $casts = [
        'temperature' => 'decimal:2',
        'humidity' => 'decimal:2',
        'pressure' => 'integer',
        'carbon_dioxide' => 'integer',
        'measured_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * SensorData belongs to a Node
     */
    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Node Model
 */
class Node extends Model
{
    use HasFactory;

    /**
     * @inheritdoc
     */
    protected $table = 'nodes';

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'uuid',
        'title',
    ];

    /**
     * @inheritdoc
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * A Node has many SensorData entries
     */
    public function sensorData(): HasMany
    {
        return $this->hasMany(SensorData::class);
    }
}

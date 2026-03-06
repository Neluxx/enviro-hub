<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'home_id',
    ];

    /**
     * @inheritdoc
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * A Node belongs to a Home
     */
    public function home(): BelongsTo
    {
        return $this->belongsTo(Home::class);
    }

    /**
     * A Node has many SensorData entries
     */
    public function sensorData(): HasMany
    {
        return $this->hasMany(SensorData::class);
    }
}

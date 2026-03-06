<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Home Model
 */
class Home extends Model
{
    use HasFactory;

    /**
     * @inheritdoc
     */
    protected $table = 'homes';

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'title',
        'identifier',
    ];

    /**
     * @inheritdoc
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * A Home has many Nodes
     */
    public function nodes(): HasMany
    {
        return $this->hasMany(Node::class);
    }
}

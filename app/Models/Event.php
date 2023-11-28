<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

    protected $touches = ['package'];
    protected $fillable = [
        'datetime',
        'location',
        'status',
        'message',
        'subStatus',
    ];

    protected $casts = [
        'datetime' => 'datetime',
        'subStatus' => 'array',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'package_id'
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}

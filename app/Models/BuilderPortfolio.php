<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuilderPortfolio extends Model
{
    protected $fillable = ['content', 'completed_at', 'published_token'];

    protected function casts(): array
    {
        return ['content' => 'array', 'completed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

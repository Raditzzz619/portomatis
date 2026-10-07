<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublishedPortfolio extends Model
{
    protected $fillable = ['token', 'content'];

    protected function casts(): array
    {
        return ['content' => 'array'];
    }
}

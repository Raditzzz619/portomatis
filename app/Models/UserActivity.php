<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivity extends Model
{
    public const LABELS = [
        'registered' => 'Mendaftar akun', 'login' => 'Masuk', 'logout' => 'Keluar',
        'builder' => 'Membuka pembuat portofolio', 'builder.resume' => 'Melanjutkan portofolio',
        'builder.new' => 'Membuat portofolio baru', 'builder.step' => 'Membuka langkah',
        'builder.save' => 'Menyimpan data', 'builder.publish' => 'Memublikasikan portofolio',
        'builder.download' => 'Mengunduh HTML', 'builder.pdf' => 'Melihat PDF',
        'builder.download.pdf' => 'Mengunduh PDF', 'heartbeat' => 'Aktif di Portomatis',
    ];

    public $timestamps = false;

    protected $fillable = ['action', 'step', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

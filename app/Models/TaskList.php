<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model TaskList — mewakili list/project milik user.
 *
 * NOTE untuk Programmer 2/3:
 * - Menggunakan SoftDeletes. Gunakan withTrashed() jika perlu akses list yang sudah dihapus.
 * - Relasi ke tasks belum diimplementasikan (scope SRS-004+), tapi tabel ini sudah siap
 *   untuk ditambahkan foreignId('task_list_id') di migration tasks nanti.
 */
class TaskList extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the owner (user) of this list.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}

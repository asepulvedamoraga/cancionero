<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SongTone extends Model
{
    use HasFactory;

    protected $fillable = ['song_id', 'name', 'is_default'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(SongFile::class);
    }
}

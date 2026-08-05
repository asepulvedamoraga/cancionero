<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SongFile extends Model
{
    use HasFactory;

    protected $fillable = ['song_id', 'song_tone_id', 'original_name', 'stored_name', 'original_path', 'preview_path', 'mime_type', 'extension', 'file_type', 'file_size', 'page_number', 'sort_order', 'is_generated'];

    protected function casts(): array
    {
        return ['file_size' => 'integer', 'page_number' => 'integer', 'sort_order' => 'integer', 'is_generated' => 'boolean'];
    }

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    public function tone(): BelongsTo
    {
        return $this->belongsTo(SongTone::class, 'song_tone_id');
    }
}

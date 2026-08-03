<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Repertoire extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'name', 'slug', 'event_type', 'event_date', 'event_time', 'location', 'description', 'status', 'visibility', 'allow_public_download'];

    protected function casts(): array
    {
        return ['event_date' => 'date', 'event_time' => 'datetime:H:i', 'allow_public_download' => 'boolean'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class)->withPivot(['sort_order', 'notes'])->withTimestamps()->orderByPivot('sort_order');
    }
}

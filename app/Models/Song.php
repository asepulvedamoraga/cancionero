<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Song extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'title', 'slug', 'author', 'performer', 'musical_key', 'category_id', 'liturgical_moment_id', 'notes', 'source', 'video_url', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function liturgicalMoment(): BelongsTo
    {
        return $this->belongsTo(LiturgicalMoment::class);
    }

    public function liturgicalSeasons(): BelongsToMany
    {
        return $this->belongsToMany(LiturgicalSeason::class, 'song_liturgical_season')->withTimestamps();
    }

    public function files(): HasMany
    {
        return $this->hasMany(SongFile::class)->orderBy('sort_order');
    }

    public function repertoires(): BelongsToMany
    {
        return $this->belongsToMany(Repertoire::class)->withPivot(['sort_order', 'notes'])->withTimestamps()->orderByPivot('sort_order');
    }

    public function youtubeEmbedUrl(): ?string
    {
        $videoUrl = trim((string) $this->video_url);
        if ($videoUrl === '') {
            return null;
        }

        $parts = parse_url($videoUrl);
        if (! is_array($parts) || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $videoId = null;

        if (str_contains($host, 'youtu.be')) {
            $videoId = trim((string) ($parts['path'] ?? ''), '/');
        } elseif (str_contains($host, 'youtube.com') || str_contains($host, 'youtube-nocookie.com')) {
            if (! empty($parts['query'])) {
                parse_str($parts['query'], $query);
                $videoId = $query['v'] ?? null;
            }

            if (! $videoId && ! empty($parts['path']) && preg_match('#^/(embed|shorts)/([^/?]+)#', $parts['path'], $matches)) {
                $videoId = $matches[2];
            }
        }

        if (! is_string($videoId) || ! preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)) {
            return null;
        }

        return 'https://www.youtube-nocookie.com/embed/'.$videoId;
    }
}

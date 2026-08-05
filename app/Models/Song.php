<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;

class Song extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'title', 'slug', 'author', 'performer', 'musical_key', 'category_id', 'liturgical_moment_id', 'notes', 'source', 'video_url', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::created(function (Song $song): void {
            $song->ensureDefaultTone();
        });
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

    public function tones(): HasMany
    {
        return $this->hasMany(SongTone::class)->orderByDesc('is_default')->orderBy('name');
    }

    public function repertoires(): BelongsToMany
    {
        return $this->belongsToMany(Repertoire::class)->withPivot(['sort_order', 'notes', 'song_tone_id'])->withTimestamps()->orderByPivot('sort_order');
    }

    public function ensureDefaultTone(): SongTone
    {
        $default = $this->tones()->where('is_default', true)->first();
        if ($default) {
            return $default;
        }

        $first = $this->tones()->first();
        if ($first) {
            $first->update(['is_default' => true]);

            return $first;
        }

        return $this->tones()->create([
            'name' => $this->defaultToneName(),
            'is_default' => true,
        ]);
    }

    public function resolveToneId(?int $candidate): ?int
    {
        if ($candidate && $this->tones()->whereKey($candidate)->exists()) {
            return $candidate;
        }

        return $this->ensureDefaultTone()->id;
    }

    public function selectedTone(?int $candidate = null): SongTone
    {
        $toneId = $this->resolveToneId($candidate);

        return $this->tones()->whereKey($toneId)->firstOrFail();
    }

    public function filesForTone(int $toneId): Collection
    {
        $files = $this->files;
        $toneFiles = $files->where('song_tone_id', $toneId);

        return $toneFiles->isNotEmpty() ? $toneFiles->values() : $files->values();
    }

    private function defaultToneName(): string
    {
        $name = trim((string) $this->musical_key);

        return $name !== '' ? $name : 'Original';
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

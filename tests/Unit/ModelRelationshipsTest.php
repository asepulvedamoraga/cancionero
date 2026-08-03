<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\LiturgicalMoment;
use App\Models\LiturgicalSeason;
use App\Models\Repertoire;
use App\Models\Song;
use App\Models\SongFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    public function test_domain_relationships_are_declared(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Category)->songs());
        $this->assertInstanceOf(HasMany::class, (new LiturgicalMoment)->songs());
        $this->assertInstanceOf(BelongsToMany::class, (new LiturgicalSeason)->songs());
        $this->assertInstanceOf(HasMany::class, (new User)->songs());
        $this->assertInstanceOf(HasMany::class, (new User)->repertoires());
        $this->assertInstanceOf(BelongsTo::class, (new Song)->owner());
        $this->assertInstanceOf(BelongsTo::class, (new Song)->category());
        $this->assertInstanceOf(BelongsTo::class, (new Song)->liturgicalMoment());
        $this->assertInstanceOf(BelongsToMany::class, (new Song)->liturgicalSeasons());
        $this->assertInstanceOf(HasMany::class, (new Song)->files());
        $this->assertInstanceOf(BelongsTo::class, (new SongFile)->song());
        $this->assertInstanceOf(BelongsTo::class, (new Repertoire)->owner());
        $this->assertInstanceOf(BelongsToMany::class, (new Repertoire)->songs());
    }
}

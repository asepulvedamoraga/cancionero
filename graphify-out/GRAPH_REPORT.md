# Graph Report - .  (2026-08-07)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 754 nodes · 1672 edges · 94 communities (79 shown, 15 thin omitted)
- Extraction: 89% EXTRACTED · 11% INFERRED · 0% AMBIGUOUS · INFERRED: 190 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `7fc1f484`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- TestCase
- SongFile
- Illuminate\Foundation\Http\FormRequest
- User
- ToneCatalog
- Controller
- Repertoire
- Illuminate\Database\Eloquent\Factories\Factory
- SongTone
- app.js
- Song
- Illuminate\Http\RedirectResponse
- Illuminate\View\View
- Illuminate\Http\Request
- Illuminate\Database\Seeder
- RepertoireExportService
- package.json
- CheckProductionReadiness
- RepertoirePageService
- scripts
- 2026_08_06_000100_create_tone_catalogs_and_link_song_tones.php
- RepertoirePolicy
- composer.json
- require-dev
- PublicRepertoireTest
- CatalogController
- PublicLandingController.php
- config
- VerifyEmailNotification.php
- AppServiceProvider
- require
- RepertoireExportTest
- PublicSongController.php
- psr-4
- extra
- 2026_08_05_000003_repair_song_tone_assignments.php
- ExampleTest
- ListingPaginationTest
- autoload-dev
- laravel-boost
- presentation.js
- repertoires/create.blade.php
- repertoires/edit.blade.php
- songs/create.blade.php
- songs/edit.blade.php

## God Nodes (most connected - your core abstractions)
1. `Song` - 133 edges
2. `Repertoire` - 100 edges
3. `User` - 87 edges
4. `SongFile` - 50 edges
5. `Controller` - 45 edges
6. `TestCase` - 34 edges
7. `SongFileService` - 23 edges
8. `SongController` - 19 edges
9. `RepertoireManagementTest` - 19 edges
10. `SongManagementTest` - 18 edges

## Surprising Connections (you probably didn't know these)
- `RepertoireExportTest` --references--> `User`  [EXTRACTED]
  tests/Feature/RepertoireExportTest.php → app/Models/User.php
- `RepertoireManagementTest` --references--> `User`  [EXTRACTED]
  tests/Feature/RepertoireManagementTest.php → app/Models/User.php
- `SongManagementTest` --references--> `User`  [EXTRACTED]
  tests/Feature/SongManagementTest.php → app/Models/User.php
- `ProfileController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Account/ProfileController.php → app/Http/Controllers/Controller.php
- `CatalogController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Admin/CatalogController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (94 total, 15 thin omitted)

### Community 0 - "TestCase"
Cohesion: 0.05
Nodes (21): UpdateRepertoireRequest, Category, LiturgicalMoment, LiturgicalSeason, CatalogManagementService, SongFactory, Illuminate\Contracts\Pagination\LengthAwarePaginator, Illuminate\Database\Eloquent\Factories\HasFactory (+13 more)

### Community 1 - "SongFile"
Cohesion: 0.08
Nodes (9): PublicRepertoireFileController, SongFileController, SongFile, PdfConversionService, PublicRepertoireAccessService, SongFileService, Illuminate\Http\UploadedFile, Symfony\Component\HttpFoundation\StreamedResponse (+1 more)

### Community 2 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.06
Nodes (10): RepertoireSongController, AddRepertoireSongsRequest, LoginRequest, ReorderRepertoireSongsRequest, ReorderSongFilesRequest, ReplaceSongFileRequest, StoreRepertoireRequest, UpdateRepertoireSongRequest (+2 more)

### Community 3 - "User"
Cohesion: 0.09
Nodes (7): User, SongPolicy, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, AccountManagementTest, OwnershipAuthorizationTest

### Community 4 - "ToneCatalog"
Cohesion: 0.09
Nodes (8): EnsureUserIsActive, EnsureUserIsAdmin, StoreSongRequest, UpdateSongRequest, ToneCatalog, Closure, self, Symfony\Component\HttpFoundation\Response

### Community 5 - "Controller"
Cohesion: 0.11
Nodes (10): SettingsController, EmailVerificationNotificationController, EmailVerificationPromptController, PasswordResetLinkController, RegisteredUserController, VerifyEmailController, Controller, PublicRepertoireDownloadController (+2 more)

### Community 6 - "Repertoire"
Cohesion: 0.14
Nodes (3): Repertoire, RepertoireManagementTest, RepertoireRecoveryTest

### Community 7 - "Illuminate\Database\Eloquent\Factories\Factory"
Cohesion: 0.12
Nodes (8): CategoryFactory, LiturgicalMomentFactory, LiturgicalSeasonFactory, RepertoireFactory, SongFileFactory, UserFactory, Illuminate\Database\Eloquent\Factories\Factory, static

### Community 8 - "SongTone"
Cohesion: 0.14
Nodes (3): SongToneController, SongTone, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 9 - "app.js"
Cohesion: 0.15
Nodes (11): initCopyButtons(), initFilePreview(), initRepertoireSelector(), initRepertoireSorter(), saveOrder(), initSongDuplicateCheck(), initSongFileSorter(), saveOrder() (+3 more)

### Community 10 - "Song"
Cohesion: 0.16
Nodes (3): Song, SharedSongLibraryTest, RepertoirePageServiceTest

### Community 11 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.18
Nodes (3): AuthenticatedSessionController, RepertoireController, Illuminate\Http\RedirectResponse

### Community 12 - "Illuminate\View\View"
Cohesion: 0.22
Nodes (4): DashboardController, SongController, Illuminate\Database\Eloquent\Builder, Illuminate\View\View

### Community 13 - "Illuminate\Http\Request"
Cohesion: 0.18
Nodes (5): ProfileController, UserController, NewPasswordController, Illuminate\Http\JsonResponse, Illuminate\Http\Request

### Community 14 - "Illuminate\Database\Seeder"
Cohesion: 0.16
Nodes (6): AdminUserSeeder, CategorySeeder, DatabaseSeeder, LiturgicalMomentSeeder, LiturgicalSeasonSeeder, Illuminate\Database\Seeder

### Community 15 - "RepertoireExportService"
Cohesion: 0.27
Nodes (4): RepertoireExportController, RepertoireExportService, setasign\Fpdi\Fpdi, Symfony\Component\HttpFoundation\BinaryFileResponse

### Community 16 - "package.json"
Cohesion: 0.13
Nodes (14): axios, laravel-vite-plugin, allowScripts, fsevents@2.3.3, devDependencies, axios, laravel-vite-plugin, vite (+6 more)

### Community 17 - "CheckProductionReadiness"
Cohesion: 0.27
Nodes (3): CheckProductionReadiness, CleanupRepertoireExports, Illuminate\Console\Command

### Community 18 - "RepertoirePageService"
Cohesion: 0.21
Nodes (4): PresentationController, PublicPresentationController, PublicRepertoireController, RepertoirePageService

### Community 19 - "scripts"
Cohesion: 0.17
Nodes (12): scripts, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump, @php artisan key:generate --ansi, @php artisan migrate --ansi (+4 more)

### Community 20 - "2026_08_06_000100_create_tone_catalogs_and_link_song_tones.php"
Cohesion: 0.32
Nodes (10): backfillSongTones(), definitions(), dropSongToneNameUniqueIndex(), mergeDuplicateTones(), normalize(), resolveCatalog(), seedCatalog(), syncSongsMusicalKey() (+2 more)

### Community 22 - "composer.json"
Cohesion: 0.20
Nodes (9): description, keywords, license, minimum-stability, name, prefer-stable, type, framework (+1 more)

### Community 23 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, fakerphp/faker, laravel/boost, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, phpunit/phpunit (+2 more)

### Community 27 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 28 - "VerifyEmailNotification.php"
Cohesion: 0.47
Nodes (3): VerifyEmailNotification, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Notifications\Messages\MailMessage

### Community 30 - "require"
Cohesion: 0.33
Nodes (6): require, laravel/framework, laravel/tinker, php, setasign/fpdf, setasign/fpdi

### Community 33 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 34 - "extra"
Cohesion: 0.40
Nodes (5): dev-master, extra, branch-alias, laravel, dont-discover

### Community 35 - "2026_08_05_000003_repair_song_tone_assignments.php"
Cohesion: 0.60
Nodes (3): repairRepertoirePivotForSong(), repairSongFilesForSong(), up()

### Community 38 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

## Knowledge Gaps
- **54 isolated node(s):** `php`, `name`, `type`, `description`, `laravel` (+49 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **15 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Song` connect `Song` to `PublicSongController.php`, `SongFile`, `TestCase`, `Illuminate\Foundation\Http\FormRequest`, `ToneCatalog`, `Controller`, `User`, `Illuminate\Database\Eloquent\Factories\Factory`, `SongTone`, `ListingPaginationTest`, `Repertoire`, `Illuminate\Http\RedirectResponse`, `Illuminate\View\View`, `Illuminate\Http\Request`, `PublicRepertoireTest`, `PublicLandingController.php`, `RepertoireExportTest`?**
  _High betweenness centrality (0.143) - this node is a cross-community bridge._
- **Why does `Repertoire` connect `Repertoire` to `TestCase`, `SongFile`, `Illuminate\Foundation\Http\FormRequest`, `User`, `Controller`, `ListingPaginationTest`, `SongTone`, `Song`, `Illuminate\Http\RedirectResponse`, `Illuminate\View\View`, `RepertoireExportService`, `RepertoirePageService`, `RepertoirePolicy`, `PublicRepertoireTest`, `PublicLandingController.php`, `RepertoireExportTest`?**
  _High betweenness centrality (0.089) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `TestCase`, `SongFile`, `Controller`, `ListingPaginationTest`, `Illuminate\Database\Eloquent\Factories\Factory`, `Repertoire`, `Song`, `Illuminate\Http\Request`, `Illuminate\Database\Seeder`, `RepertoirePolicy`, `PublicRepertoireTest`, `RepertoireExportTest`?**
  _High betweenness centrality (0.089) - this node is a cross-community bridge._
- **Are the 56 inferred relationships involving `Song` (e.g. with `.__invoke()` and `.edit()`) actually correct?**
  _`Song` has 56 INFERRED edges - model-reasoned connections that need verification._
- **Are the 34 inferred relationships involving `Repertoire` (e.g. with `.__invoke()` and `.__invoke()`) actually correct?**
  _`Repertoire` has 34 INFERRED edges - model-reasoned connections that need verification._
- **Are the 48 inferred relationships involving `User` (e.g. with `.store()` and `.definition()`) actually correct?**
  _`User` has 48 INFERRED edges - model-reasoned connections that need verification._
- **What connects `php`, `name`, `type` to the rest of the system?**
  _54 weakly-connected nodes found - possible documentation gaps or missing edges._
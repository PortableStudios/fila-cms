# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`portable/fila-cms` is a **Laravel package** (not a standalone app) that ships a Filament-based CMS. It is consumed by host Laravel apps via `composer require portable/fila-cms`. Because it's a package, there is no `artisan` binary — development and testing run through **Orchestra Testbench**, which boots a throwaway host app from the `workbench/` directory.

PHP namespace roots (PSR-4): `Portable\FilaCms\` → `src/`, `Portable\FilaCms\Database\` → `database/`, `Portable\FilaCms\Tests\` → `tests/`, `Workbench\App\` → `workbench/app/`.

## Commands

### PHP / Laravel
```bash
./vendor/bin/pest                          # run the full test suite (Pest)
./vendor/bin/pest tests/Feature/Foo.php    # run a single test file
./vendor/bin/pest --filter="test name"     # run tests matching a name
./vendor/bin/phpunit                        # equivalent (composer "test" script)
composer lint                               # pint (PSR-12) + phpstan (larastan, level in phpstan.neon)
./vendor/bin/pint                           # auto-fix code style only
./vendor/bin/phpstan analyse                # static analysis only
./vendor/bin/testbench serve                # run the CMS locally at http://localhost:8000/admin
```
Local admin login when serving: `admin@test.com` / `password`. For mail testing, run MailHog (`docker run -p 8025:8025 -p 1025:1025 mailhog/mailhog`) and view at http://localhost:8025.

Tests use SQLite in-memory and `RefreshDatabase`. `tests/TestCase.php` runs the full `fila-cms:install` flow **once per process** (`$hasInstalled` guard) and then runs `pnpm run build` — so the JS toolchain must be installed for tests to pass.

### Frontend assets (pnpm — never npm/yarn)
Node 22+ (`.nvmrc` = 22.19.0) and pnpm 11+ are enforced via `engine-strict=true` in `.npmrc`.
```bash
pnpm install --frozen-lockfile   # CI install
pnpm run build                   # production asset build
pnpm run dev                     # vite dev server
```
Security baseline: `pnpm-workspace.yaml` sets `minimumReleaseAge: 10080` (refuse deps younger than 7 days). Rationale: `docs/superpowers/plans/2026-06-11-npm-to-pnpm-migration.md`.

> Note: per global instructions, do not launch dev/serve/watch processes — provide instructions only. Asset *builds* (`pnpm run build`) are fine and are required for tests.

## Architecture

### Service provider is the composition root
`src/Providers/FilaCmsServiceProvider.php` wires everything: registers the 10 `fila-cms:*` artisan commands, loads `routes/filacms-routes.php` (always) and `routes/frontend-routes.php` (when `publish_content_routes`), registers Spatie Health checks, Fortify/2FA response bindings, Socialite (incl. a custom LinkedIn provider), schedules daily jobs (sitemap, schedule-monitor sync, prune), and conditionally registers `FilaCmsAdminPanelProvider` (the Filament panel at `/admin`) when `fila-cms.use_admin_panel` is true. The `FilaCms` and `MediaLibrary` services are bound here and exposed via facades in `src/Facades/`.

### `FilaCms` service facade (`src/FilaCms.php`)
Central runtime registry and helpers. Key responsibilities: discovers **content resources/models** (any resource extending `AbstractContentResource`), defines all frontend route groups (`contentRoutes()`, `shortUrlRoutes()`, `formRoutes()`, `ssoRoutes()`, `profileRoutes()`), HTML purification (`purifyHtml` via stevebauman/purify, config in `fila-cms.purify`), image thumbnailing, and the `systemUser()` (`system@filacms`) used for attributing automated changes.

### The AbstractContent pattern (the core extensibility model)
Custom content types are the primary way host apps extend the CMS. The contract:
- **Model** extends `Portable\FilaCms\Models\AbstractContentModel` — all content rows live in a single shared `contents` table (single-table inheritance by convention). The base model composes a large trait stack: `Versionable` (snapshot strategy, TipTap-aware via `FilaCmsVersion`), `SoftDeletes`, `HasLocks` (kenepa/resource-lock), `HasSEO`, plus FilaCms traits `HasExcerpt`, `HasTaxonomies`, `HasAuthors`, `HasShortUrl`, `HasContentRoles`, `HasSlug`, `Searchable`, `ProvidesSearchSettings`. A `PublishedScope` enforces draft/publish_at/expire_at visibility.
- **Resource** extends `AbstractContentResource` (which itself uses `IsProtectedResource`).
- **Plugin**: register the resource in an `App\Plugins\*` Filament `Plugin` class, then list it in `config/fila-cms.php` under `admin_plugins`.

Scaffolding commands generate these: `make:filament-resource` then switch the base classes, or use `fila-cms:make-content*` (`MakeContentModel`, `MakeContentMigration`, `MakeContentResource`, `MakeContentPermissionSeeder`, `MakeContents`).

### Permissions / protected resources
Authorization uses spatie/laravel-permission. Add the `IsProtectedResource` trait (`src/Filament/Traits/`) to any Filament resource to auto-enforce `view <resource>` / `manage <resource>` permissions. `ContentRoleMiddleware` gates frontend content by role.

### Forms builder
A dynamic form system (`src/Filament/FormBlocks/`) built from block classes extending `AbstractFormBlock`. Submissions are stored as `FormEntry` models and exportable via `FormEntryExporter`. Available blocks are registered in `config/fila-cms.forms.blocks`.

### Search
Laravel Scout + Meilisearch. The `Searchable` / `ProvidesSearchSettings` traits drive indexing; `fila-cms:sync-search` (and `CommandStartingListener`/`CommandFinishedListener`) manage index settings around Scout console commands. The service provider force-registers Scout commands when not running in console so queued/sync jobs work.

### Settings
Key/value `Setting` model loaded into config at boot (`loadSettings()`), edited through the Filament `EditSettings` page. Field definitions are assembled in `registerSettingsFields()` and documented in `documentation/Settings.md`.

### Other integrations worth knowing
- **Auth**: Laravel Fortify (2FA via `TwoFactorMiddleware`), Socialite SSO (`SSOController`, `UserSsoLink`), lab404 impersonation (session password-hash swap wired via events in the provider).
- **Editor**: TipTap (awcodes/filament-tiptap-editor) with custom extensions in `src/TiptapExtensions/` and blocks in `src/Filament/Blocks/`.
- **Media**: `MediaLibrary` service + `Media` model; thumbnails and downloads served from `routes/filacms-routes.php`, sizes configured in `fila-cms.media_library.thumbnails`.

## Tests are split into three suites
`phpunit.xml` defines `Unit` (`tests/Unit`), `Main` (`tests/Feature`), and `Filament` (`tests/Filament`). Test factories live in `tests/Factories`; the workbench host app (models, seeders, migrations) is under `workbench/` and `testbench.yaml` declares the providers/migrations/seeders the test app boots with.

## Conventions
- Code style is PSR-12 via Pint with `short` array syntax and `no_unused_imports` enforced (`pint.json`). Run `composer lint` before considering work done.
- Commit messages: Conventional Commits (`type(scope): summary`), no AI attribution footers.
- Superpowers docs (specs/plans/qa/etc.) live under `docs/superpowers/`.

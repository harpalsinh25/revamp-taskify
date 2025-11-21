# Taskify Plugin Development Guide

## Table of Contents

1. [Introduction](#introduction)
2. [Plugin System Overview](#plugin-system-overview)
3. [Plugin Directory Structure](#plugin-directory-structure)
4. [Plugin Manifest (plugin.json)](#plugin-manifest-pluginjson)
5. [Service Provider](#service-provider)
6. [Routes & Controllers](#routes--controllers)
7. [Database & Migrations](#database--migrations)
8. [Models & Relationships](#models--relationships)
9. [Views & Templates](#views--templates)
10. [Translations & Labels](#translations--labels)
11. [Assets (JavaScript & CSS)](#assets-javascript--css)
12. [Menu Integration](#menu-integration)
13. [Permissions & Access Control](#permissions--access-control)
14. [Commands & Scheduled Tasks](#commands--scheduled-tasks)
15. [Services & Business Logic](#services--business-logic)
16. [Installation & Activation](#installation--activation)
17. [Best Practices & Conventions](#best-practices--conventions)
18. [Examples & Templates](#examples--templates)
19. [Troubleshooting](#troubleshooting)

---

## Introduction

Taskify uses a plugin-based architecture that allows you to extend the core functionality with custom features. Plugins are self-contained modules that can be easily installed, enabled, disabled, or removed without affecting the core system.

### Key Features of the Plugin System

- **Auto-discovery**: Plugins are automatically discovered and loaded when enabled
- **Service Provider Pattern**: Each plugin uses Laravel's Service Provider for registration
- **Asset Management**: Automatic asset publishing and management
- **Menu Integration**: Plugins can add menu items to the main navigation
- **Permission System**: Built-in permission integration
- **Translation Support**: Multi-language label support
- **Isolated Structure**: Plugins are self-contained with their own controllers, models, views, and routes

---

## Plugin System Overview

### How Plugins Work

1. **Discovery**: The system scans the `plugins/` directory for plugin folders
2. **Registration**: Each plugin must have a `plugin.json` manifest file
3. **Loading**: Enabled plugins load their Service Provider during application boot
4. **Integration**: Plugins register routes, views, migrations, menus, and assets

### Plugin Loading Process

The plugin loading happens in `AppServiceProvider::loadPlugins()`:

```php
// app/Providers/AppServiceProvider.php (lines 535-570)
private function loadPlugins()
{
    $pluginsPath = base_path('plugins');

    if (File::exists($pluginsPath)) {
        $pluginDirs = File::directories($pluginsPath);

        foreach ($pluginDirs as $pluginDir) {
            $pluginJson = $pluginDir . '/plugin.json';

            if (File::exists($pluginJson)) {
                $pluginConfig = json_decode(File::get($pluginJson), true);

                if (!empty($pluginConfig['enabled']) && !empty($pluginConfig['provider'])) {
                    $providerClass = $pluginConfig['provider'];
                    // Register the service provider
                    app()->register($providerClass);
                }
            }
        }
    }
}
```

---

## Plugin Directory Structure

A plugin should follow this standard directory structure:

```
YourPluginName/
├── Controllers/                    # Plugin controllers
│   ├── YourPluginController.php
│   └── SettingsController.php     # Optional: settings controller
├── Models/                        # Eloquent models
│   └── YourModel.php
├── Database/
│   └── Migrations/                # Database migrations
│       └── YYYY_MM_DD_HHMMSS_create_table_name.php
├── Providers/
│   └── YourPluginServiceProvider.php  # Service provider
├── routes/
│   ├── web.php                    # Web routes
│   └── api.php                    # API routes (optional)
├── Resources/
│   ├── views/                     # Blade views
│   │   └── your-plugin-name/
│   │       ├── index.blade.php
│   │       └── create.blade.php
│   └── lang/
│       └── plugin_labels.php      # Translation labels
├── public/
│   ├── js/                        # JavaScript files
│   │   └── your-plugin.js
│   ├── css/                       # CSS files (optional)
│   │   └── your-plugin.css
│   └── img/                       # Images (optional)
│       └── icon.png
├── Services/                      # Business logic services (optional)
│   └── YourService.php
├── Commands/                      # Artisan commands (optional)
│   └── YourCommand.php
├── Middleware/                    # Custom middleware (optional)
│   └── YourMiddleware.php
├── menus.php                      # Menu configuration
├── plugin.json                    # Plugin manifest (REQUIRED)
└── README.md                      # Plugin documentation
```

### Directory Naming Conventions

- **Plugin Folder**: Use PascalCase (e.g., `SocialMediaManagement`, `AssetManagement`)
- **Controllers**: PascalCase with `Controller` suffix
- **Models**: PascalCase (singular)
- **Views**: kebab-case or snake_case folder names
- **Routes**: snake_case route names with plugin prefix

---

## Plugin Manifest (plugin.json)

The `plugin.json` file is the plugin manifest that defines the plugin's metadata and configuration.

### Required Fields

```json
{
    "name": "Taskify - Your Plugin Name",
    "slug": "your-plugin-slug",
    "description": "Brief description of your plugin",
    "version": "1.0.0",
    "enabled": true,
    "provider": "Plugins\\YourPluginName\\Providers\\YourPluginServiceProvider"
}
```

### Optional Fields

```json
{
    "publish_tag": "your-plugin-assets"  // For asset publishing
}
```

### Field Descriptions

| Field | Required | Description |
|-------|----------|-------------|
| `name` | Yes | Display name of the plugin |
| `slug` | Yes | URL-friendly identifier (kebab-case) |
| `description` | Yes | Brief description of plugin functionality |
| `version` | Yes | Semantic version (e.g., "1.0.0") |
| `enabled` | Yes | Boolean - whether plugin is enabled |
| `provider` | Yes | Fully qualified Service Provider class name |
| `publish_tag` | No | Tag for asset publishing via `php artisan vendor:publish` |

### Example plugin.json

```json
{
    "name": "Taskify - Social Media Management",
    "slug": "social-media-management",
    "description": "Manages social media posts, scheduling, analytics, and multi-platform publishing with AI-powered content creation.",
    "version": "1.0.0",
    "enabled": true,
    "provider": "Plugins\\SocialMediaManagement\\Providers\\SocialMediaServiceProvider",
    "publish_tag": "social-assets"
}
```

---

## Service Provider

The Service Provider is the heart of your plugin. It registers routes, views, migrations, translations, assets, commands, and services.

### Basic Service Provider Structure

```php
<?php

namespace Plugins\YourPluginName\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class YourPluginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                // YourCommand::class,
            ]);
        }

        // Register service bindings
        // $this->app->singleton('your.service', function ($app) {
        //     return new YourService();
        // });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'your-plugin-namespace');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'your-plugin-namespace');

        // Publish assets
        $this->publishes([
            __DIR__ . '/../public/js' => public_path('assets/js/your-plugin'),
            __DIR__ . '/../public/css' => public_path('assets/css/your-plugin'),
        ], ['your-plugin-assets', 'public']);

        // Auto-publish assets (optional)
        $this->autoPublishAssets();

        // Log plugin version
        if (file_exists(__DIR__ . '/../plugin.json')) {
            $pluginJson = json_decode(file_get_contents(__DIR__ . '/../plugin.json'), true);
            Log::info('Your Plugin Loaded - Version: ' . ($pluginJson['version'] ?? 'unknown'));
        }

        // Schedule tasks (optional)
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            // $schedule->command('your:command')->daily();
        });
    }

    /**
     * Auto-publish assets if they don't exist
     */
    private function autoPublishAssets(): void
    {
        // Implementation below
    }
}
```

### Loading Components

#### Routes

```php
$this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
```

#### Views

```php
// Namespace your views (use when rendering: 'your-plugin-namespace::view-name')
$this->loadViewsFrom(__DIR__ . '/../Resources/views', 'your-plugin-namespace');
```

#### Migrations

```php
$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
```

#### Translations

```php
$this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'your-plugin-namespace');
```

### Asset Publishing

#### Define Publishable Assets

```php
$this->publishes([
    __DIR__ . '/../public/js' => public_path('assets/js/your-plugin'),
    __DIR__ . '/../public/css' => public_path('assets/css/your-plugin'),
], ['your-plugin-assets', 'public']);
```

#### Auto-Publish Assets

```php
private function autoPublishAssets(): void
{
    $sourcePathJs = __DIR__ . '/../public/js';
    $destinationPathJs = public_path('assets/js/your-plugin');

    if (\Illuminate\Support\Facades\File::exists($sourcePathJs)) {
        if (!\Illuminate\Support\Facades\File::exists($destinationPathJs) ||
            $this->assetsNeedUpdate($sourcePathJs, $destinationPathJs)) {
            \Illuminate\Support\Facades\File::ensureDirectoryExists($destinationPathJs);
            \Illuminate\Support\Facades\File::copyDirectory($sourcePathJs, $destinationPathJs);
        }
    }

    // Repeat for CSS, images, etc.
}

private function assetsNeedUpdate(string $sourcePath, string $destinationPath): bool
{
    if (!\Illuminate\Support\Facades\File::exists($destinationPath)) {
        return true;
    }

    $sourceTime = \Illuminate\Support\Facades\File::lastModified($sourcePath);
    $destTime = \Illuminate\Support\Facades\File::lastModified($destinationPath);

    return $sourceTime > $destTime;
}
```

### Service Bindings

Register services in the `register()` method:

```php
public function register(): void
{
    $this->app->singleton('your.service', function ($app) {
        return new YourService();
    });

    // Or with dependencies
    $this->app->singleton('your.service', function ($app) {
        return new YourService(
            $app->make('dependency.service')
        );
    });
}
```

### Extending Core Models

You can add relationships to core models (e.g., User):

```php
use App\Models\User;
use Plugins\YourPlugin\Models\YourModel;

public function boot(): void
{
    // ... other boot code ...

    // Add relationship to User model
    User::resolveRelationUsing('yourModels', function ($userModel) {
        return $userModel->hasMany(YourModel::class, 'user_id');
    });
}
```

---

## Routes & Controllers

### Route Structure

Routes are defined in `routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use Plugins\YourPluginName\Controllers\YourPluginController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('your-plugin-slug')->group(function () {
        Route::get('/', [YourPluginController::class, 'index'])->name('your-plugin.index');
        Route::get('/create', [YourPluginController::class, 'create'])->name('your-plugin.create');
        Route::post('/store', [YourPluginController::class, 'store'])->name('your-plugin.store');
        Route::get('/edit/{id}', [YourPluginController::class, 'edit'])->name('your-plugin.edit');
        Route::post('/update/{id}', [YourPluginController::class, 'update'])->name('your-plugin.update');
        Route::delete('/destroy/{id}', [YourPluginController::class, 'destroy'])->name('your-plugin.destroy');
    });
});
```

### Route Middleware

Common middleware patterns:

```php
// Authentication
Route::middleware(['web', 'auth'])->group(function () {
    // Routes here
});

// Permission-based access
Route::middleware(['web', 'auth', 'customcan:manage_items'])->group(function () {
    // Routes requiring 'manage_items' permission
});

// Role-based access (check in controller)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin', [Controller::class, 'admin'])->name('admin');
    // Check isAdminOrHasAllDataAccess() in controller
});
```

### Controller Structure

```php
<?php

namespace Plugins\YourPluginName\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Plugins\YourPluginName\Models\YourModel;

class YourPluginController extends Controller
{
    public function index()
    {
        // Check permissions
        if (!isAdminOrHasAllDataAccess() && !auth()->user()->can('view_items')) {
            abort(403);
        }

        return view('your-plugin-namespace::index', [
            'items' => YourModel::all(),
        ]);
    }

    public function create()
    {
        return view('your-plugin-namespace::create');
    }

    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // ... other fields
        ]);

        // Create model
        $item = YourModel::create($validated);

        return redirect()->route('your-plugin.index')
            ->with('success', get_label('item_created', 'Item created successfully'));
    }
}
```

### Route Naming Conventions

- Use kebab-case for route names
- Prefix with plugin slug: `your-plugin.action`
- Examples: `your-plugin.index`, `your-plugin.create`, `your-plugin.store`

---

## Database & Migrations

### Migration File Naming

Follow Laravel's migration naming convention:
- `YYYY_MM_DD_HHMMSS_create_table_name.php`
- Example: `2025_01_15_120000_create_your_items_table.php`

### Creating Tables

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('your_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Insert permissions
        DB::table('permissions')->insert([
            ['name' => 'manage_items', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'create_items', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'edit_items', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'delete_items', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        // Remove permissions
        DB::table('permissions')->whereIn('name', [
            'manage_items',
            'create_items',
            'edit_items',
            'delete_items',
        ])->delete();

        // Drop table
        Schema::dropIfExists('your_items');
    }
};
```

### Permission Naming Conventions

- Use snake_case
- Pattern: `{action}_{resource}` (e.g., `manage_items`, `create_items`, `edit_items`, `delete_items`)
- Common actions: `manage`, `view`, `create`, `edit`, `delete`

### Running Migrations

Migrations are automatically loaded when the plugin is enabled. They run with:

```bash
php artisan migrate
```

To rollback plugin migrations:

```bash
php artisan migrate:rollback --path=plugins/YourPluginName/Database/Migrations
```

---

## Models & Relationships

### Model Structure

```php
<?php

namespace Plugins\YourPluginName\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class YourModel extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // JSON fields
        'metadata' => 'array',
    ];

    /**
     * Relationship with User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
```

### Using Spatie Media Library

If your plugin needs file uploads:

```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class YourModel extends Model implements HasMedia
{
    use InteractsWithMedia;

    // In your controller
    public function store(Request $request)
    {
        $item = YourModel::create($request->validated());

        // Handle file upload
        if ($request->hasFile('image')) {
            $item->addMediaFromRequest('image')
                ->toMediaCollection('images');
        }

        return redirect()->route('your-plugin.index');
    }
}
```

---

## Views & Templates

### View Namespace

When loading views in Service Provider:

```php
$this->loadViewsFrom(__DIR__ . '/../Resources/views', 'your-plugin-namespace');
```

Render views using:

```php
return view('your-plugin-namespace::index', ['data' => $data]);
```

### View Structure

```
Resources/
└── views/
    └── your-plugin-namespace/
        ├── index.blade.php
        ├── create.blade.php
        ├── edit.blade.php
        └── partials/
            └── _form.blade.php
```

### Using Translations in Views

**Always use `get_label()` for translations:**

```blade
{{-- ✅ Correct --}}
<h1>{{ get_label('your_plugin_title', 'Your Plugin Title') }}</h1>
<button>{{ get_label('create', 'Create') }}</button>

{{-- ❌ Wrong - Don't hardcode text --}}
<h1>Your Plugin Title</h1>
```

### Blade Example

```blade
@extends('layouts.app')

@section('title', get_label('your_plugin', 'Your Plugin'))

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>{{ get_label('your_plugin', 'Your Plugin') }}</h4>
        </div>
        <div class="card-body">
            <a href="{{ route('your-plugin.create') }}" class="btn btn-primary">
                {{ get_label('create', 'Create') }}
            </a>

            <table class="table">
                <thead>
                    <tr>
                        <th>{{ get_label('name', 'Name') }}</th>
                        <th>{{ get_label('actions', 'Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>
                            <a href="{{ route('your-plugin.edit', $item->id) }}">
                                {{ get_label('edit', 'Edit') }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
```

### Important View Guidelines

1. **No Inline CSS/JS**: Don't add `<style>` or `<script>` tags in blade files
2. **Use Existing Classes**: Use Bootstrap or Sneat classes
3. **Custom CSS**: Add to `custom.css` or create plugin-specific CSS file
4. **JavaScript**: Use page-specific JS files or `custom.js`

---

## Translations & Labels

### Creating Plugin Labels

Create `Resources/lang/plugin_labels.php`:

```php
<?php

return [
    'your_plugin' => 'Your Plugin',
    'your_plugin_title' => 'Your Plugin Title',
    'create' => 'Create',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'name' => 'Name',
    'description' => 'Description',
    // ... more labels
];
```

### Using Labels in PHP

```php
// In controllers
return redirect()->route('your-plugin.index')
    ->with('success', get_label('item_created', 'Item created successfully'));

// In models (if needed)
public function getLabelAttribute()
{
    return get_label('item_type', 'Item Type');
}
```

### Using Labels in Blade

```blade
{{ get_label('your_plugin', 'Your Plugin') }}
{!! get_label('your_html_label', 'Your <strong>HTML</strong> Label') !!}
```

### Adding Labels to Settings

Plugin labels are automatically loaded and displayed in the language settings page (`resources/views/settings/languages.blade.php`). The system uses `PluginHelper::getPluginLabels()` to gather all plugin labels from `Resources/lang/plugin_labels.php` files.

**Automatic Integration**: Labels defined in `Resources/lang/plugin_labels.php` are automatically:
- Available via `get_label()` function in PHP and Blade
- Displayed in the language settings page for translation management
- Merged with core system labels

### JavaScript Labels

For labels that need to be available in JavaScript, you have two options:

#### Option 1: Pass Through Blade File

Pass labels directly in your blade file:

```blade
@section('scripts')
<script>
    window.pluginLabels = {
        'create': '{{ get_label('create', 'Create') }}',
        'edit': '{{ get_label('edit', 'Edit') }}',
        'delete': '{{ get_label('delete', 'Delete') }}',
    };
</script>
<script src="{{ asset('assets/js/your-plugin.js') }}"></script>
@endsection
```

#### Option 2: Add to Global Labels File

For commonly used labels, add them to `resources/views/labels.blade.php`:

```blade
<script>
    var label_your_plugin_create = "{{ get_label('your_plugin_create', 'Create') }}";
    var label_your_plugin_edit = "{{ get_label('your_plugin_edit', 'Edit') }}";
</script>
```

**Note**: The global labels file (`labels.blade.php`) is included in the main layout and available across all pages.

### Label Loading Process

1. **Automatic Discovery**: The system scans all plugins for `Resources/lang/plugin_labels.php` files
2. **Merging**: Labels are merged via `PluginHelper::getPluginLabels()`
3. **Settings Integration**: Plugin labels appear in the language settings page for translation
4. **Runtime Access**: Labels are accessible via `get_label()` function throughout the application

### Label File Structure

```php
<?php
// Resources/lang/plugin_labels.php

return [
    // Plugin-specific labels
    'your_plugin' => 'Your Plugin',
    'your_plugin_title' => 'Your Plugin Title',

    // Common action labels (if not already in core)
    'create' => 'Create',
    'edit' => 'Edit',
    'delete' => 'Delete',

    // Field labels
    'name' => 'Name',
    'description' => 'Description',
    'status' => 'Status',

    // More labels...
];
```

**Important**: Always provide fallback text in `get_label()` calls:
```php
get_label('key', 'Fallback Text')
```

---

## Assets (JavaScript & CSS)

### Asset Structure

```
public/
├── js/
│   └── your-plugin.js
├── css/
│   └── your-plugin.css    # Optional
└── img/
    └── icon.png           # Optional
```

### JavaScript Files

#### Page-Specific JavaScript

If the JavaScript is used on multiple pages, create a common file:
```
public/js/your-plugin.js
```

If it's page-specific:
```
public/js/your-plugin-create.js
```

#### JavaScript Example

```javascript
// public/js/your-plugin.js

document.addEventListener('DOMContentLoaded', function() {
    // Your plugin JavaScript here

    // Example: Form submission
    const form = document.getElementById('your-plugin-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Handle form submission
        });
    }
});
```

#### Including JavaScript in Views

```blade
@extends('layouts.app')

@section('scripts')
<script src="{{ asset('assets/js/your-plugin.js') }}"></script>
@endsection
```

### CSS Files

#### Creating Plugin CSS

Add to `public/css/your-plugin.css`:

```css
/* Your plugin-specific styles */
.your-plugin-container {
    padding: 20px;
}

.your-plugin-item {
    margin-bottom: 10px;
}
```

**Important**: Use existing Bootstrap/Sneat classes first. Only add custom CSS if necessary.

#### Including CSS in Views

```blade
@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/your-plugin.css') }}">
@endsection
```

### Asset Publishing

Assets are automatically published to:
- JS: `public/assets/js/your-plugin/`
- CSS: `public/assets/css/your-plugin/`
- Images: `public/assets/img/your-plugin/`

The Service Provider handles auto-publishing when the plugin loads.

### Asset Guidelines

1. **No Inline Styles/Scripts**: Never add `<style>` or `<script>` tags directly in blade files
2. **Use Existing Classes**: Leverage Bootstrap, Sneat, or `custom.css` classes
3. **Plugin-Specific**: Only create plugin CSS/JS if truly plugin-specific
4. **Page-Specific**: Use page-specific JS files for isolated functionality

---

## Menu Integration

### Creating menus.php

Create a `menus.php` file in your plugin root:

```php
<?php

return [
    [
        'id' => 'your_plugin_menu',
        'label' => get_label('your_plugin', 'Your Plugin'),
        'url' => '',  // Empty for parent with submenus
        'icon' => 'bx bx-icon-name',  // Boxicons icon class
        'class' => 'menu-item' . (request()->is('your-plugin-slug*') ? ' active open' : ''),
        'category' => 'utilities',  // Menu category
        'show' => isAdminOrHasAllDataAccess() || auth()->user()->can('manage_items') ? 1 : 0,
        'badge' => '<span class="badge rounded-pill bg-label-info text-uppercase ms-2">' . get_label('plugin', 'Plugin') . '</span>',
        'submenus' => [
            [
                'id' => 'plugin_items',
                'label' => get_label('items', 'Items'),
                'url' => route('your-plugin.index'),
                'class' => 'menu-item' . (request()->is('your-plugin-slug') ? ' active' : ''),
                'show' => isAdminOrHasAllDataAccess() || auth()->user()->can('manage_items') ? 1 : 0,
            ],
            [
                'id' => 'plugin_create',
                'label' => get_label('create', 'Create'),
                'url' => route('your-plugin.create'),
                'class' => 'menu-item' . (request()->is('your-plugin-slug/create') ? ' active' : ''),
                'show' => isAdminOrHasAllDataAccess() || auth()->user()->can('create_items') ? 1 : 0,
            ],
        ],
    ],
];
```

### Menu Structure

- **Parent Menu**: If it has submenus, use empty `url` and provide `submenus` array
- **Single Menu Item**: Provide `url` directly (no `submenus`)
- **Icons**: Use Boxicons classes (e.g., `bx bx-home`, `bx bx-user`)
- **Categories**: Common categories include `utilities`, `management`, `reports`

### Menu Properties

| Property | Required | Description |
|----------|----------|-------------|
| `id` | Yes | Unique identifier for the menu item |
| `label` | Yes | Menu label (use `get_label()`) |
| `url` | Yes* | Route URL (empty if parent with submenus) |
| `icon` | No | Boxicons icon class |
| `class` | No | CSS classes (include active state logic) |
| `category` | Yes | Menu category for grouping |
| `show` | Yes | Visibility condition (1 or 0) |
| `badge` | No | HTML badge (e.g., "Plugin" badge) |
| `submenus` | No | Array of submenu items |

### Menu Visibility

Use permission checks or role checks:

```php
'show' => isAdminOrHasAllDataAccess() || auth()->user()->can('manage_items') ? 1 : 0,
```

### Active State

Detect active route:

```php
'class' => 'menu-item' . (request()->is('your-plugin-slug*') ? ' active open' : ''),
```

### Menu Loading

Menus are automatically loaded by the system. They're merged with core menus in:
- `resources/views/components/menu.blade.php` (lines 147-177)
- `app/Http/Controllers/PreferenceController.php` (lines 22-49)

---

## Permissions & Access Control

### Creating Permissions

Create permissions in your migration's `up()` method:

```php
DB::table('permissions')->insert([
    ['name' => 'manage_items', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'create_items', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'edit_items', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ['name' => 'delete_items', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
]);
```

### Removing Permissions

Remove in migration's `down()` method:

```php
DB::table('permissions')->whereIn('name', [
    'manage_items',
    'create_items',
    'edit_items',
    'delete_items',
])->delete();
```

### Using Permissions in Routes

```php
Route::middleware(['web', 'auth', 'customcan:manage_items'])->group(function () {
    // Routes requiring 'manage_items' permission
});
```

### Using Permissions in Controllers

```php
public function index()
{
    if (!isAdminOrHasAllDataAccess() && !auth()->user()->can('view_items')) {
        abort(403);
    }

    // Controller logic
}
```

### Using Permissions in Views

```blade
@can('create_items')
    <a href="{{ route('your-plugin.create') }}">{{ get_label('create', 'Create') }}</a>
@endcan
```

### Helper Functions

- `isAdminOrHasAllDataAccess()`: Check if user is admin or has all data access
- `auth()->user()->can('permission_name')`: Check specific permission
- `auth()->user()->hasRole('role_name')`: Check specific role

---

## Commands & Scheduled Tasks

### Creating Artisan Commands

#### Command Class

```php
<?php

namespace Plugins\YourPluginName\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class YourCommand extends Command
{
    protected $signature = 'your-plugin:command-name';
    protected $description = 'Description of what the command does';

    public function handle()
    {
        $this->info('Starting command...');

        try {
            // Your command logic here

            $this->info('Command completed successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Your Command Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
```

#### Registering Commands

In your Service Provider's `register()` method:

```php
public function register(): void
{
    if ($this->app->runningInConsole()) {
        $this->commands([
            YourCommand::class,
        ]);
    }
}
```

### Scheduling Commands

In your Service Provider's `boot()` method:

```php
public function boot(): void
{
    // ... other boot code ...

    $this->app->booted(function () {
        $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);

        $schedule->command(YourCommand::class)
            ->dailyAt('00:00')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('Your command completed successfully.');
            })
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Your command failed.');
            });
    });
}
```

### Schedule Frequency Options

```php
->everyMinute()          // Every minute
->everyFiveMinutes()     // Every 5 minutes
->everyTenMinutes()      // Every 10 minutes
->hourly()               // Every hour
->daily()                // Daily at 00:00
->dailyAt('13:00')       // Daily at specific time
->weekly()               // Weekly
->monthly()              // Monthly
->weeklyOn(1, '8:00')    // Weekly on Monday at 8:00
->cron('0 0 * * *')      // Custom cron expression
```

### Example: Scheduled Posts Command

```php
// plugins/SocialMediaManagement/Commands/PublishScheduledPosts.php
$schedule->command(PublishScheduledPosts::class)
    ->everyMinute()
    ->withoutOverlapping()
    ->onSuccess(function () {
        Log::info('social:publish-scheduled command completed successfully.');
    })
    ->onFailure(function () {
        Log::error('social:publish-scheduled command failed.');
    });
```

---

## Services & Business Logic

### Creating Services

Organize complex business logic in service classes:

```php
<?php

namespace Plugins\YourPluginName\Services;

class YourService
{
    public function processData(array $data): array
    {
        // Business logic here
        return $processedData;
    }

    public function calculateMetrics(): array
    {
        // Calculations
        return $metrics;
    }
}
```

### Registering Services

In Service Provider's `register()` method:

```php
public function register(): void
{
    $this->app->singleton('your.service', function ($app) {
        return new \Plugins\YourPluginName\Services\YourService();
    });
}
```

### Using Services

In controllers:

```php
public function index()
{
    $service = app('your.service');
    $data = $service->processData($request->all());

    // Or inject via constructor
    // public function __construct(YourService $service) { }
}
```

### Service with Dependencies

```php
public function register(): void
{
    $this->app->singleton('your.service', function ($app) {
        return new YourService(
            $app->make('dependency.service')
        );
    });
}
```

---

## Installation & Activation

### Installation Process

1. **Place Plugin**: Copy plugin folder to `plugins/YourPluginName/`
2. **Verify plugin.json**: Ensure `plugin.json` exists and is valid
3. **Enable Plugin**: Set `"enabled": true` in `plugin.json`
4. **Run Migrations**: Execute `php artisan migrate`
5. **Clear Cache**: Run `php artisan cache:clear` and `php artisan config:clear`

### Plugin Manager

Plugins can be managed via the Plugin Manager interface (if available) or manually:

#### Enable Plugin

```php
use App\Helpers\PluginHelper;

PluginHelper::updateStatus('your-plugin-slug', true);
```

#### Disable Plugin

```php
PluginHelper::updateStatus('your-plugin-slug', false);
```

#### Check Plugin Status

```php
$plugin = PluginHelper::get('your-plugin-slug');
if ($plugin && $plugin['enabled']) {
    // Plugin is enabled
}
```

### Uninstallation

The system handles uninstallation via `PluginHelper::delete()`:

1. Disable plugin first (`"enabled": false`)
2. Rollback migrations
3. Clean up database entries (permissions, settings)
4. Delete plugin directory

---

## Best Practices & Conventions

### Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| Plugin Folder | PascalCase | `SocialMediaManagement` |
| Plugin Slug | kebab-case | `social-media-management` |
| Controllers | PascalCase + Controller | `SocialMediaController` |
| Models | PascalCase (singular) | `SocialPost` |
| Routes | kebab-case | `social-media-scheduler` |
| Route Names | kebab-case | `social.index`, `social.create` |
| Permissions | snake_case | `manage_posts`, `create_posts` |
| Database Tables | snake_case (plural) | `social_posts` |
| View Namespace | kebab-case | `social-media-scheduler` |

### Code Organization

1. **Separation of Concerns**: Keep controllers thin, move business logic to services
2. **DRY Principle**: Don't repeat code; use helpers and services
3. **Single Responsibility**: Each class should have one responsibility
4. **Consistent Structure**: Follow the standard plugin directory structure

### Error Handling

```php
try {
    // Your code
} catch (\Exception $e) {
    \Illuminate\Support\Facades\Log::error('Plugin Error: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);

    return redirect()->back()
        ->with('error', get_label('something_went_wrong', 'Something went wrong. Please try again.'));
}
```

### Logging

Use Laravel's Log facade:

```php
use Illuminate\Support\Facades\Log;

Log::info('Plugin action completed', ['data' => $data]);
Log::warning('Plugin warning', ['issue' => $issue]);
Log::error('Plugin error', ['error' => $error]);
```

### Security Considerations

1. **Input Validation**: Always validate user input
2. **Permission Checks**: Check permissions in controllers and routes
3. **SQL Injection**: Use Eloquent ORM, not raw queries
4. **XSS Prevention**: Escape output in Blade using `{{ }}` (not `{!! !!}` unless necessary)
5. **CSRF Protection**: Use `@csrf` in forms

### Performance Optimization

1. **Eager Loading**: Use `with()` to prevent N+1 queries
2. **Caching**: Cache expensive operations
3. **Database Indexes**: Add indexes to frequently queried columns
4. **Asset Optimization**: Minify JS/CSS for production

### Translation Best Practices

1. **Always Use get_label()**: Never hardcode text
2. **Provide Fallback**: Always provide fallback text: `get_label('key', 'Fallback Text')`
3. **Consistent Keys**: Use consistent naming: `action_resource` (e.g., `create_item`)
4. **Organize Labels**: Group related labels in `plugin_labels.php`

---

## Examples & Templates

### Minimal Plugin Template

#### plugin.json

```json
{
    "name": "Taskify - Example Plugin",
    "slug": "example-plugin",
    "description": "A minimal example plugin",
    "version": "1.0.0",
    "enabled": true,
    "provider": "Plugins\\ExamplePlugin\\Providers\\ExamplePluginServiceProvider"
}
```

#### Service Provider

```php
<?php

namespace Plugins\ExamplePlugin\Providers;

use Illuminate\Support\ServiceProvider;

class ExamplePluginServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'example-plugin');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'example-plugin');
    }

    public function register(): void
    {
        //
    }
}
```

#### Routes

```php
<?php

use Illuminate\Support\Facades\Route;
use Plugins\ExamplePlugin\Controllers\ExampleController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('example-plugin')->group(function () {
        Route::get('/', [ExampleController::class, 'index'])->name('example.index');
    });
});
```

#### Controller

```php
<?php

namespace Plugins\ExamplePlugin\Controllers;

use App\Http\Controllers\Controller;

class ExampleController extends Controller
{
    public function index()
    {
        return view('example-plugin::index', [
            'title' => get_label('example_plugin', 'Example Plugin'),
        ]);
    }
}
```

#### View

```blade
@extends('layouts.app')

@section('title', get_label('example_plugin', 'Example Plugin'))

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>{{ get_label('example_plugin', 'Example Plugin') }}</h4>
        </div>
        <div class="card-body">
            <p>{{ get_label('welcome', 'Welcome to the example plugin!') }}</p>
        </div>
    </div>
</div>
@endsection
```

#### Labels

```php
<?php
// Resources/lang/plugin_labels.php

return [
    'example_plugin' => 'Example Plugin',
    'welcome' => 'Welcome to the example plugin!',
];
```

#### menus.php

```php
<?php

return [
    [
        'id' => 'example_plugin_menu',
        'label' => get_label('example_plugin', 'Example Plugin'),
        'url' => route('example.index'),
        'icon' => 'bx bx-code-alt',
        'class' => 'menu-item' . (request()->is('example-plugin*') ? ' active' : ''),
        'category' => 'utilities',
        'show' => isAdminOrHasAllDataAccess() ? 1 : 0,
        'badge' => '<span class="badge rounded-pill bg-label-info text-uppercase ms-2">' . get_label('plugin', 'Plugin') . '</span>',
    ],
];
```

### Complete Plugin Example

For a complete working example, refer to existing plugins in the codebase:

- **SocialMediaManagement**: Complex plugin with commands, services, scheduled tasks, and multi-platform integration
- **AssetManagement**: Good example of CRUD operations, permissions, and asset tracking
- **TimeTracker**: Example of API routes, middleware, and scheduled commands
- **Letter**: Simple plugin with templates and variable management

#### Key Files to Study

1. **Service Provider**: `plugins/SocialMediaManagement/Providers/SocialMediaServiceProvider.php`
2. **Routes**: `plugins/SocialMediaManagement/routes/web.php`
3. **Controller**: `plugins/SocialMediaManagement/Controllers/SocialMediaController.php`
4. **Model**: `plugins/SocialMediaManagement/Models/SocialPost.php`
5. **Migration**: `plugins/SocialMediaManagement/Database/Migrations/2025_07_25_060726_create_social_posts_table.php`
6. **Menu**: `plugins/SocialMediaManagement/menus.php`
7. **Labels**: `plugins/SocialMediaManagement/Resources/lang/plugin_labels.php`

---

## Troubleshooting

### Plugin Not Loading

**Problem**: Plugin doesn't appear in the system.

**Solutions**:
1. Check `plugin.json` exists and is valid JSON
2. Verify `"enabled": true` in `plugin.json`
3. Check Service Provider class exists and namespace is correct
4. Clear cache: `php artisan cache:clear && php artisan config:clear`
5. Check Laravel logs: `storage/logs/laravel.log`

### Routes Not Working

**Problem**: Routes return 404.

**Solutions**:
1. Verify routes are loaded in Service Provider: `$this->loadRoutesFrom(...)`
2. Check route names match (use `php artisan route:list`)
3. Clear route cache: `php artisan route:clear`
4. Verify middleware is correct

### Views Not Found

**Problem**: View not found error.

**Solutions**:
1. Check view namespace in Service Provider matches usage
2. Verify view file exists in correct directory
3. Clear view cache: `php artisan view:clear`
4. Check file permissions

### Assets Not Loading

**Problem**: CSS/JS files return 404.

**Solutions**:
1. Check assets are published: `php artisan vendor:publish --tag=your-plugin-assets`
2. Verify asset paths in views match published paths
3. Check auto-publish logic in Service Provider
4. Clear public asset cache

### Migrations Not Running

**Problem**: Migrations don't run automatically.

**Solutions**:
1. Verify `loadMigrationsFrom()` is called in Service Provider
2. Run migrations manually: `php artisan migrate`
3. Check migration file naming convention
4. Verify database connection

### Permissions Not Created

**Problem**: Permissions don't exist after migration.

**Solutions**:
1. Check migration `up()` method inserts permissions
2. Verify `permissions` table exists
3. Check for errors in migration rollback/rerun
4. Manually insert permissions if needed

### Menu Not Appearing

**Problem**: Plugin menu doesn't show in navigation.

**Solutions**:
1. Verify `menus.php` exists in plugin root
2. Check `show` condition returns `1`
3. Verify user has required permissions/role
4. Clear application cache
5. Check menu structure matches expected format

### Translation Labels Not Working

**Problem**: Labels show as keys instead of text.

**Solutions**:
1. Verify `plugin_labels.php` exists and returns array
2. Check labels are added to `settings/language.blade.php` if needed for JS
3. Verify `get_label()` function is used correctly
4. Clear cache

### Commands Not Found

**Problem**: Artisan command not found.

**Solutions**:
1. Verify command is registered in Service Provider `register()` method
2. Check command class exists and extends `Command`
3. Clear cache: `php artisan cache:clear`
4. Verify command signature matches usage

---

## Additional Resources

### Core Files Reference

- **Plugin Loading**: `app/Providers/AppServiceProvider.php` (lines 535-570)
- **Plugin Helper**: `app/Helpers/PluginHelper.php`
- **Menu Integration**: `resources/views/components/menu.blade.php` (lines 147-177)
- **Plugin Manager**: `app/Http/Controllers/PluginManagerController.php`

### Real-World Examples

Study existing plugins in the codebase for real-world patterns:

#### SocialMediaManagement Plugin

A comprehensive example with multiple integration points:

- **Service Provider**: `plugins/SocialMediaManagement/Providers/SocialMediaServiceProvider.php`
  - Complex boot() method with scheduled tasks, service bindings, and relationship extensions
  - Auto-publishing assets with version checking

- **Routes**: `plugins/SocialMediaManagement/routes/web.php`
  - Permission-based route groups
  - RESTful resource routes

- **Controller**: `plugins/SocialMediaManagement/Controllers/SocialMediaController.php`
  - Dependency injection with services
  - Complex business logic handling

- **Model**: `plugins/SocialMediaManagement/Models/SocialPost.php`
  - Spatie Media Library integration
  - JSON field casting
  - Model events and boot methods
  - Custom accessor methods

- **Migration**: `plugins/SocialMediaManagement/Database/Migrations/2025_07_25_060726_create_social_posts_table.php`
  - Permission creation in up() method
  - Permission cleanup in down() method

- **Commands**: `plugins/SocialMediaManagement/Commands/PublishScheduledPosts.php`
  - Scheduled command for automatic publishing
  - Error handling and logging

#### AssetManagement Plugin

Excellent example of CRUD operations and relationships:

- **Service Provider**: `plugins/AssetManagement/Providers/AssetServiceProvider.php`
  - Simple structure with auto-publishing

- **Model**: `plugins/AssetManagement/Models/Asset.php`
  - Status constants
  - Relationship definitions
  - Spatie Media Library integration

- **Routes**: `plugins/AssetManagement/routes/web.php`
  - Nested routes (category routes within asset routes)
  - Multiple resource operations

#### TimeTracker Plugin

Example of API routes and middleware:

- **Service Provider**: `plugins/TimeTracker/Providers/TimeTrackerServiceProvider.php`
  - Middleware registration
  - Config publishing
  - API route loading

- **Middleware**: `plugins/TimeTracker/Middleware/IsDevice.php`
  - Custom middleware implementation

#### Letter Plugin

Simple plugin example:

- **Service Provider**: `plugins/Letter/Providers/LetterServiceProvider.php`
  - Minimal structure
  - Clean implementation

### Laravel Documentation

For Laravel-specific features used in plugins:

- [Service Providers](https://laravel.com/docs/providers)
- [Database Migrations](https://laravel.com/docs/migrations)
- [Eloquent ORM](https://laravel.com/docs/eloquent)
- [Blade Templates](https://laravel.com/docs/blade)
- [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary)

---

## Conclusion

This guide covers everything you need to create plugins for Taskify. Follow the structure, conventions, and best practices outlined here to build robust, maintainable plugins that integrate seamlessly with the Taskify ecosystem.

For questions or issues, refer to existing plugins or check the troubleshooting section above.

Happy plugin development!


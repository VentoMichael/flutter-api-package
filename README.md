# MakeFlutterCommand Package

## Overview

The **MakeFlutterCommand** package provides a Laravel artisan command to generate essential files and structure for a Flutter component in a Laravel project. This command streamlines the development process by automatically creating the necessary resources, migrations, models, controllers, and routes, following Laravel's best practices.

## Installation

### 1. Require the Package

To get started, you need to require the package in your Laravel project using Composer:

    composer require your-vendor/make-flutter-command

### 2. Service Provider (If Not Auto-Discovered)

If your package is not auto-discovered by Laravel, you need to manually register the service provider. Open the `config/app.php` file and add the service provider to the `providers` array:

    'providers' => [
        // Other Service Providers
    
        Vendor\Package\YourServiceProvider::class,
    ]

### 3. Publish Configuration (Optional)

If your package includes configuration files that need to be published to the Laravel application's config directory, you can publish them using the following command:

    php artisan vendor:publish --provider="Vendor\Package\YourServiceProvider"

## Usage

### Make a Flutter Component

You can generate a Flutter component using the following artisan command:
```
    php artisan make:flutter {name} {--seeder}
```
- `{name}`: The name of the component you want to create.
- `--seeder`: (Optional) If this option is provided, a seeder file will also be generated.

### Example

To create a Flutter component named "Product" and include a seeder, use the command:
```
    php artisan make:flutter Product --seeder
```
This command will create the following files and updates:

1. **Resource**: `app/Http/Resources/ProductResource.php`
2. **Migration**: `database/migrations/xxxx_xx_xx_create_products_table.php`
3. **Helper**: `app/Helpers/ProductHelper.php`
4. **Model**: `app/Models/Product.php`
5. **Controller**: `app/Http/Controllers/Api/ProductController.php`
6. **Routes**: Adds RESTful API routes in `routes/api.php`
7. **Seeder**: (If `--seeder` is provided) `database/seeders/ProductSeeder.php`

### Generated Controller Actions

The package creates a controller with the following actions:

- **json()**: Fetches all instances of the model and returns them as a JSON response.
- **show($id)**: Fetches a specific instance by its ID.
- **store(Request $request)**: Stores a new instance in the database.
- **update(Request $request, $id)**: Updates an existing instance by its ID.
- **destroy($id)**: Deletes an instance by its ID.

### Generated API Routes

The generated routes can be found in ```routes/api.php``` and include:
```
Route::prefix('products')
    ->controller(App\Http\Controllers\Api\ProductController::class)
    ->group(function () {
        Route::get('/json', 'json');
        Route::get('/{id}', 'show');
        Route::post('/', 'store');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });
```
## Changelog

All notable changes to this project will be documented in this section.

## Contributing

If you'd like to contribute, please fork the repository and use a feature branch. Pull requests are welcome.

## License

This package is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).

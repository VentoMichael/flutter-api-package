## Changelog

### 1.0.0 (2024-08-27)

- **Initial Release**
    - Added `make:flutter` artisan command to automate the creation of:
        - **Resource**: Generates a resource class for transforming model data.
        - **Migration**: Creates a new migration file for the specified model's table.
        - **Helper**: Adds a helper function file for utility functions related to the model.
        - **Model**: Creates a new Eloquent model with soft deletes and table name setup.
        - **Controller**: Generates a RESTful API controller with standard CRUD operations.
        - **Routes**: Automatically adds RESTful API routes for the new controller.
        - **Seeder**: (Optional) Generates a seeder file for populating the model's table with sample data.
    - Updated `routes/api.php` to include RESTful routes for the new model.
    - Added logging and error handling in the generated controller methods.
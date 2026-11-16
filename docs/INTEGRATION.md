# Existing spices CRUD application

This repository is a standalone Laravel 12 Lab 5 implementation. It is not a backup or a recovered copy of the original spices application, and it does not claim that Lab 4 was finished.

An older local `laravel-crud-activity-2-main` copy inspected during preparation declares Laravel 9. Its ProductController validates basic name/quantity/price values, but that copy has no product image handling, role checks, or search/pagination. Another newer copy may exist. Verify the intended project before marking Lab 4 complete.

Lab 4 requires input validation (including optional description), optional JPEG/PNG/WebP images up to 2 MB with safe replacement and deletion, admin/user authorization on the server, search with preserved pagination, tests, a report, and the required demonstration.

To integrate Lab 5 into a verified existing Laravel 12 app:

1. Back up the app and database or create a working Git branch.
2. Add the SensorReading model/migration, API and dashboard controllers, AuthenticateDevice middleware, config/iot.php, and the dashboard view.
3. Merge the three API routes and dashboard route into existing route files. Preserve all CRUD and authentication routes. Register API routing in bootstrap/app.php if absent.
4. Add IOT_DEVICE_TOKEN and IOT_DISPLAY_TIMEZONE to local configuration. Do not replace the existing APP_KEY or database credentials.
5. Run php artisan migrate to add the sensor table to the intended database. Do not use migrate:fresh on existing data.
6. Add a dashboard link to the existing navigation and apply the app's existing login rules where appropriate. Re-run CRUD, role, file-upload, and IoT tests.

For a Laravel 9 app, do not copy the Laravel 12 bootstrap file over its existing bootstrap/Kernel structure. Confirm the framework-version requirement with the instructor and make a separate migration/adaptation plan. This repository lets you work through Lab 5 on the specified framework while retaining the older application.

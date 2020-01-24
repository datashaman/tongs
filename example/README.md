# tongs

## example

Install Composer dependencies:

    composer install

To require another plugin:

    composer require example/tongs-plugin

Install npm dependencies:

    npm install

Generate the build folder:

    vendor/bin/tongs

Serve the build folder:

    php artisan serve -S localhost:8080 -t build

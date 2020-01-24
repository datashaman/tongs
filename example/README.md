# tongs

## example

Install tongs globally:

    composer global require datashaman/tongs

Or (better) install all tongs packages into one namespaced bin:

    composer global require bamarni/composer-bin-plugin
    composer global bin tongs require datashaman/tongs

To add another plugin to the bin:

    composer global bin tongs require example/tongs-plugin

Install npm dependencies:

    npm install

Generate the build folder:

    tongs

Serve the build folder:

    php artisan serve -S localhost:8080 -t build

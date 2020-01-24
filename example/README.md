# tongs

## example

Install node-sass globally:

    sudo -H npm install -g node-sass

Install tongs globally:

    composer global require datashaman/tongs

Or (better) install all tongs packages into one namespaced bin:

    composer global require bamarni/composer-bin-plugin
    composer global bin tongs require datashaman/tongs

To add another plugin to the bin:

    composer global bin tongs require example/tongs-plugin

Generate the build folder:

    tongs

Serve the build folder:

    php artisan serve -S localhost:8080 -t build

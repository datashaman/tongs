<?php

pcntl_async_signals(true);

// If PHP < 7.1 use this: declare(ticks=1);

pcntl_signal(SIGINT, function () {
    exit;
});

class Test {
    function __construct() {
        echo "Construct\n";
    }

    function __destruct() {
        echo "Destruct\n";
    }
}

$t = new Test();

sleep(600);

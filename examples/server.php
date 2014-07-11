<?php

$server = new Server($loop);
$server->addFunction('add', function ($a, $b) {
    return $a + $b;
});

$loop->run();

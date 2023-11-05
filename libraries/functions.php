<?php

function dump(...$args): void
{
    echo '<pre>';
    var_dump($args);
    echo '</pre>';
}
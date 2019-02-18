<?php

function dd($arg)
{
    echo '<pre>';
    die(var_dump($arg));
    echo '</pre>';
}
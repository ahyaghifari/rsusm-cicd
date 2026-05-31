<?php

if (! function_exists('current_rumahsakit')) {

    function current_rumahsakit()
    {
        return app('currentRumahSakit');
    }
}

if (! function_exists('rumahsakit_route')) {

    function rumahsakit_route($name, $parameters = [])
    {
        return route($name, array_merge([
            'rumahsakit' => current_rumahsakit()->slug
        ], $parameters));
    }
}
<?php

if (!function_exists('rumahsakit_route')) {

    function rumahsakit_route($name, $parameters = [])
    {
        $hospital = app('currentHospital');

        return route($name, array_merge([
            'rumahsakit' => $hospital->slug
        ], $parameters));
    }
}
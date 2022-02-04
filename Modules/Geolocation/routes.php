<?php

# Получение данных по IP
use Core\Services\Routing\API\APIRoute;

APIRoute::api('get', '/api/v1/geo/', [
    'controller'	=>  'GeolocationController',
    'action'		=>  'get',
    'assets'        =>  'international',
]);
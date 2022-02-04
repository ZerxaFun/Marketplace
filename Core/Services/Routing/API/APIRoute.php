<?php
/**
 *=====================================================
 * Majestic Engine - by Zerxa Fun (Majestic Studio)   =
 *-----------------------------------------------------
 * @url: http://majestic-studio.ru/                   -
 *-----------------------------------------------------
 * @copyright: 2020 Majestic Studio and ZerxaFun      -
 *=====================================================
 *                                                    =
 *                                                    =
 *                                                    =
 *=====================================================
 */


namespace Core\Services\Routing\API;


use Core\Services\Routing\RouteAbstract;


/**
 * Class APIRoute
 * @package Core\Services\Routing\API
 */
class APIRoute extends RouteAbstract
{

    public string $assets;
    /**
     * Устанавливает маршрут GET.
     *
     * @param $method
     * @param string $uri - URI для маршрутизации.
     * @param array $options - варианты маршрута.
     * @return bool
     */
    public static function api($method, string $uri, array $options): bool
    {
        return static::add($method, $uri, $options);
    }
}
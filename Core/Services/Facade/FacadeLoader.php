<?php
declare(strict_types = 1);

namespace Core\Services\Facade;

use Core\Services\Config\Config;
use Core\Services\Container\Facade;

class FacadeLoader
{

    public function __construct()
    {
        self::init(Config::group('facades'));
    }

    public static function init(array $serviceLocator): void
    {
        $facades = Facade::instance()->set($serviceLocator);

        Facade::instance()->setObject($facades->facade);
        FacadeAccessor::setServiceLocator($facades);
    }
}

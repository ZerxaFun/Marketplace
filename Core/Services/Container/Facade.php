<?php

declare(strict_types=1);

/**
 *=====================================================
 * Majestic Engine                                    =
 *=====================================================
 * @package Core\Services\Container                  =
 *-----------------------------------------------------
 * @url http://majestic-studio.com/                   =
 *-----------------------------------------------------
 * @copyright 2021 Majestic Studio                    =
 *=====================================================
 * @author ZerxaFun aKa Zerxa                         =
 *=====================================================
 * @license GPL version 3                             =
 *=====================================================
 *                                                    =
 *                                                    =
 *=====================================================
 */

namespace Core\Services\Container;


/**
 * Класс зависимостей Facade
 *
 * @Package Core/Services/Container
 */
class Facade
{
    private static ?Facade $instance = null;

    public array $facade;

    public static function instance(): Facade
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    final public function get(string $key): mixed
    {
        return $this->has($key) ? $this->facade[$key] : self::instance();
    }

    /**
     * Добавление элементов в контейнер фасадов
     * @use Facade::instance()->set(mixed(array/string) facade)
     *
     * @param mixed $facade
     */
    final public function setObject(mixed $facade): void
    {
        DI::instance()->set('facade', $facade);
    }

    /**
     * Добавление элементов в контейнер фасадов
     * @usa Facade::instance()->set(string key name, mixed(array/string) value)
     *
     * @param mixed $value
     * @return $this
     */
    final public static function set(mixed $value): self
    {
        self::instance()->facade = $value;

        return self::instance();
    }

    /**
     * Просмотр, есть ли в контейнере фасад
     * @use Facade::instance()->has(string name injection)
     *
     * @param string $key
     * @return object
     */
    final public function has(string $key): object
    {
        return $this->facade[$key];
    }
}

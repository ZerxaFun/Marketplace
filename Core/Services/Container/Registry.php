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
 * Класс зависимостей Registry
 *
 * @Package Core/Services/Container
 */
class Registry
{
    /**
     * Массив зависимостей
     *
     * @var array
     */
    private static array $dependencies = [];

    /**
     * Массив реестра
     *
     * @var array
     */
    private static array $aliases = [];

    /**
     * @param object $class
     * @return Registry
     */
    public static function bind(object $class): Registry
    {
        self::$dependencies[self::className($class::class)] = $class;

        self::$aliases[self::className($class::class)] = [
            'return'    => self::$dependencies[self::className($class::class)],
            'namespace' => $class::class,
            'aliases'   => self::isAlias(self::className($class::class))
        ];

        return new self();
    }

    /**
     * @param string $name
     * @return mixed
     */
    public static function getBind(string $name): mixed
    {
        return self::$aliases[$name]['return'];
    }
    /**
     * Получение названия класса по его экземпляру объекта
     *
     * @param string $class
     * @return string
     */
    private static function className(string $class): string
    {
        return basename(str_replace('\\', '/', $class));
    }

    /**
     * Проверка, есть ли указанный класс в зависимости
     *
     * @param string $name
     * @return bool
     */
    private static function isAlias(string $name): bool
    {
        return class_exists($name);
    }

    /**
     * Получение всех зависимостей регистра
     *
     * @return array
     */
    public static function getDependencies(): array
    {
        return self::$aliases;
    }
}

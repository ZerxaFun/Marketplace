<?php

declare(strict_types=1);

/**
 *=====================================================
 * Majestic Engine                                    =
 *=====================================================
 * @package Core\Services\Container                    =
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

use JetBrains\PhpStorm\Pure;

/**
 * Класс зависимостей Dependency Injection
 *
 * @property facade
 * @package Core\Services\Container
 */
class DI
{
    /**
     * Массив объекта контейнера.
     * Содержит в себе все зависимости.
     * При подключении пакета содержит null.
     *
     * @var DI|null
     */
    private static ?DI $instance = null;

    /**
     * Инициализация, либо вывод всех зависимостей проекта.
     * @use DI::instance()
     *
     * @return DI
     */
    public static function instance(): DI
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Получение зависимости контейнера
     * @use DI::instance()->get(string name injection)
     *
     * @param string $key
     * @return mixed
     */
    #[Pure] final public function get(string $key): mixed
    {
        return $this->has($key) ? $this->$key : false;
    }

    /**
     * Добавление элементов в контейнер зависимостей
     * @usa DI::instance()->set(string key name, mixed(array/string) value)
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    final public function set(string $key, mixed $value): DI
    {
        $this->$key = $value;

        return $this;
    }

    /**
     * Просмотр, есть ли в контейнере зависимости
     * @use DI::instance()->has(string name injection)
     *
     * @param string $key
     * @return bool
     */
    final public function has(string $key): bool
    {
        return isset($this->$key);
    }
}

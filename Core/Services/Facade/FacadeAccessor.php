<?php
declare(strict_types = 1);

namespace Core\Services\Facade;

use LogicException;
use RuntimeException;


/**
 * Мост, позволяющий перенаправлять статические вызовы от псевдонима экземпляру, возвращаемому локатором службы.
 *
 * @author ZerxaFun <zerxafun@gmail.com>
 */
abstract class FacadeAccessor
{
    /**
     * Экземпляр объекта
     *
     * @var object|null
     */
    public static ?object $serviceLocator = null;

    /**
     * Вызов метода экземпляра
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    final public static function __callStatic(string $name, array $arguments): string
    {
        if(!method_exists(self::getService(), $name)) {
            throw new RuntimeException(
                sprintf('Метода: %s не существует', $name)
            );

        }
        return self::getService()->$name(...$arguments);
    }

    /**
     * Sets the service locator
     * @param object $serviceLocator
     */
    final public static function setServiceLocator(object $serviceLocator): void
    {
        self::$serviceLocator = $serviceLocator;
    }

    /**
     * Returns the service from the service locator
     * @return object
     *
     * @throws LogicException If no service locator has been set
     */
    private static function getService(): object
    {
        if (!isset(self::$serviceLocator)) {
            throw new LogicException("Service locator has not been set yet");
        }

        return self::$serviceLocator->get(self::register());
    }

    /**
     * Возвращает строковый идентификатор, который будет использоваться для получения экземпляра из локатора службы.
     *
     * @return string
     */
    abstract public static function register(): string;
}

<?php

namespace Core\Services\Session;


/**
 * Class Session
 * @package Core\Services\Session
 */
class Session
{

    /**
     * @var bool - сессия инициализирована.
     */
    private static bool $initialized = false;

    /**
     * @var SessionDriver - активный драйвер сеанса.
     */
    private static SessionDriver $driver;

    /**
     * Создать новую сессию.
     *
     * @return void
     */
    public function __construct()
	{
		# Нужно ли инициализировать сессию?
        if (!static::$initialized) {
        	# Получить имя драйвера из конфигурации приложения и получить
			# имя класса для нас, чтобы создать экземпляр.
            $driver = 'native';
            $class  = 'Core\\Services\\Session\\Driver\\' . ucfirst(strtolower($driver));

            # Создание экземпляра драйвера.
            static::$driver = new $class;

 			# Инициализируем сессию.
            if (static::driver()->initialize()) {
                static::$initialized = true;
            }
        }
    }

    /**
     * Уничтожает сессию.
     *
     * @return void
     */
    public function __destruct()
	{
        static::driver()->finalize();
    }

    /**
     * Gets the active session driver.
     *
     * @return SessionDriver|SessionInterface
     */
    public static function driver(): SessionInterface|SessionDriver
    {
        return static::$driver;
    }

}

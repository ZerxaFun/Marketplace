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


namespace Core\Services\Database;


use PDO;
use PDOException;
use Core\Services\Config\Config;
use Exception;
use RuntimeException;


/**
 * Class Database
 * @package Flexi\Database
 */
class Database
{

    /**
     * @var $connection|null
     */
    private static mixed $connection;

    /**
     * @return PDO
     */
    public static function connection(): PDO
    {
        return static::$connection;
    }

    /**
     * Инициализация и подключение к базе данных
     *
     * @return void
     * @throws Exception
     */
    public static function initialize(): void
    {
        /**
         * Подключение к базе данных
         */
        static::$connection = static::connect();
    }

    /**
     * Отключение от базы данных
     *
     * @return void
     */
    public static function finalize(): void
    {
        /**
         * Закрытие соеденения
         */
        static::$connection = null;
    }

    /**
     * Подключение к базе данных
     *
     * @return null|PDO
     * @throws Exception
     */
    private static function connect(): ?PDO
    {
        /**
         * Стартовые данные для подключения
         */
        $driver     = $_ENV['db_driver'];
        $host       = $_ENV['db_host'];
        $username   = $_ENV['db_username'];
        $password   = $_ENV['db_password'];
        $name       = $_ENV['db_name'];
        $charset    = $_ENV['db_charset'];

        $dsn        = sprintf('%s:host=%s;dbname=%s;charset=%s', $driver, $host, $name, $charset);
        $options    = [
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION
        ];

        /**
         * Возвращаем null, если у нас не указано какое-либо из полей подключения
         * к базе данных
         */
        if ($driver === '' || $username === '' || $name === '') {
            return null;
        }


        /**
         * Пытаемся подключиться к базе данных
         * В случаи неудачи возвращаем Exception
         */
        try {
            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $error) {
            throw new RuntimeException($error->getMessage());
        }
    }

    /**
     * Получение индикатора последней вставленной записи в базу данных
     *
     * @return int
     */
    public static function insertId(): int
    {
        return (int) static::$connection->lastInsertId();
    }
}

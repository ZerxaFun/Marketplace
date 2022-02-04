<?php
declare(strict_types=1);

/**
 *=====================================================
 * Majestic ReactFox Engine                           =
 *=====================================================
 * @url http://majestic-studio.com/                   =
 *-----------------------------------------------------
 * @copyright 2021 Majestic Studio                    =
 *=====================================================
 * @author ZerxaFun aKa Zerxa                         =
 *=====================================================
 * @license GPL version 3                             =
 *=====================================================
 * @package Core\Services\Request                     =
 *-----------------------------------------------------
 * Получение, проверка и обработка HTTP Method        =
 *=====================================================
 */


namespace Core\Services\Http;


use Exception;
use JetBrains\PhpStorm\Pure;


final class Method
{
    /**
     * Список доступных методов запроса.
     *
     * @var array|string[]
     */
    private array $allowMethod = [
        'POST'      => true, /** @POST используется для отправки сущностей к определённому ресурсу.
                                 Часто вызывает изменение состояния или какие-то побочные эффекты на сервере. */
        'GET'       => true, /** @Метод GET запрашивает представление ресурса.
                                 Запросы с использованием этого метода могут только извлекать данные. */
        'CLI'       => true, /** @CLI обращение к приложению через консоль */
        'PUT'       => true, /** @PUT заменяет все текущие представления ресурса данными запроса. */
        'PATCH'     => true, /** @PATCH используется для частичного изменения ресурса. */
        'DELETE'    => true, /** @DELETE удаляет указанный ресурс. */
    ];

    /**
     * Переменная определяющая разрешен ли запрос
     *
     * @var bool
     */
    public bool $allow = false;

    /**
     * Имя метода для отправки системе
     *
     * @var string|null
     */
    public ?string $method = null;

    /**
     * Контейнер Request содержащий в себе все объекты
     *
     * @var Method|null
     */
    private static ?Method $instance = null;

    /**
     * Инициализация Method class.
     *
     * @return Method
     */
    private static function instance(): Method
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Инициализация запроса через контейнер self::instance().
     *
     * @throws Exception
     */
    public static function method(): Method
    {
        return self::instance()->getMethod();
    }

    /**
     * Получение типа и обработка текущего типа запроса.
     *
     * @throws Exception
     */
    private function getMethod(): Method
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'CLI');

        $this->validationMethod($method);

        return self::instance();
    }


    /**
     * Проверяем, разрешен ли тип Method запроса.
     * Список разрешенных запросов содержится в массиве:
     * @var $this->allowMethod[]
     *
     * В случаи пустого метода (ошибки отправки) или метода, который не разрешен
     * в $this->allowMethod[] выбрасываем исключение с нужным сообщением.
     *
     * @throws Exception
     */
    private function validationMethod(string $method): void
    {
        if($method === '') {
            throw new Exception('Отправлен пустой метод запроса.');
        } elseif(!array_key_exists($method, $this->allowMethod)) {
            throw new Exception(
                sprintf('Запрос отправлен не верно, метод "%s" не поддерживается', $method)
            );
        } elseif($this->allowMethod[$method] === false) {
            throw new Exception(
                sprintf('HTTP метод %s отключен для выполнения на сервере', $method)
            );
        }

        $this->method = $method;
        $this->allow = true;
    }

    /**
     * Проверка, является ли запрос определенным методом.
     *
     * @param string $method - Метод запроса для проверки.
     *
     * @return bool
     * @throws Exception
     */
    #[Pure] public static function is(string $method): bool
    {
        return match (strtolower($method)) {
            'https' => self::https(),
            'ajax' => self::ajax(),
            'cli' => self::cli(),
            default => $method === self::thisMethod(),
        };
    }

    /**
     * Получение текущего запроса.
     *
     * @return string
     */
    public static function thisMethod(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD'] ?? 'cli');
    }

    /**
     * Проверьте, если запрос через соединение https.
     *
     * @return bool
     */
    private static function https(): bool
	{
        return ($_SERVER['HTTPS'] ?? '') === 'on';
    }

    /**
     * Проверка, является ли запрос AJAX-запросом.
     *
     * @return bool
     */
    private static function ajax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    /**
     * Проверка, является ли запрос запросом CLI.
     *
     * @return bool
     */
    private static function cli(): bool
	{
        return (PHP_SAPI === 'cli' || defined('STDIN'));
    }

    /**
     * Отключение определенных методов HTTP запроса.
     *
     * @param string $method
     */
    public static function disableMethod(string $method): void
    {
        self::instance()->allowMethod[$method] = false;
    }

    /**
     * @throws Exception
     */
    public static function methodString(): string
    {
        return strtolower(self::method()->method);
    }
}

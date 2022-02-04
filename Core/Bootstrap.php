<?php
declare(strict_types=1);
/**
 *=====================================================
 * Majestic Engine                                    =
 *=====================================================
 * @package Core\Bootstrap                            =
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

namespace Core;


use Core\Application\SystemEnvironmentAnalysis\SEA;
use Core\Services\Container\DI;
#use Core\Services\Database\Database;
use Core\Services\Environment\Dotenv;
use Core\Services\ErrorHandler\ErrorHandler;
use Core\Services\Http\Uri;
use Core\Services\Orm\Query;
use Core\Services\Routing\Controller;
use Core\Services\Routing\Route;
use Core\Services\Routing\Router;
use Core\Services\Session\Facades\Session;
use Core\Services\Template\Layout;
use Core\Services\Template\View;
use Exception;

class Bootstrap
{
    /**
     * @throws Exception
     */
    public static function run(): void
    {
        /**
         * Загрузка классов необходимых для работы
         */
        class_alias(DI::class, 'DI');
        class_alias(Controller::class, 'Controller');
        class_alias(Layout::class, 'Layout');
        class_alias(Route::class, 'Route');
        class_alias(Query::class, 'Query');
        class_alias(View::class, 'View');
        /**
         * Правильный вывод ошибок
         */
        ErrorHandler::initialize();
        /**
         * Парсинг .env файлов окружения
         */
        Dotenv::initialize();



        Router::initialize();


        SEA::folder();

        /**
         * Инициализация URI.
         */
        Uri::initialize();

        /**
         * Подключение к базе данных.
         */
        #Database::initialize();
        /**
         * Инициализация сессий.
         */
        Session::initialize();
    }

}
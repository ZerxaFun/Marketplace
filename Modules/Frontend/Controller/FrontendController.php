<?php

namespace Modules\Frontend\Controller;

use Controller;

use Core\Services\Client\Client;
use Core\Services\Localization\I18n;
use Core\Services\Localization\Language;
use Core\Services\Routing\Router;
use DI;
use Modules\Frontend;
use Exception;

/**
 * Class FrontendController
 * @package Modules\Frontend\Controller
 */
class FrontendController extends Controller
{
    /**
     * FrontendController constructor.
     * @throws Exception
     */
    public function __construct()
    {
        /**
         * Подключение языка Frontend по умолчанию.
         */
        Client::language();

        I18n::instance()->load('main/main');

        /**
         * Подключение запрошенного модуля
         */
        $module = new Router();
        $module = $module::module()->module;

        /**
         * Запись запроса в массив $data[]
         */
        $this->setData('module', $module);
    }
}

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


namespace Core\Services\Routing;


use Core\Services\API\API;
use Core\Services\Http\Header;
use Exception;
use Core\Services\Config\Config;
use RuntimeException;
use Setting;


/**
 * Class Module
 * @package Core\Services\Routing
 */
class Module
{
    /**
     * @var Controller - контроллер.
     */
    protected Controller $instance;

    /**
     * @var mixed - ответ действий.
     */
    protected $response;

    /**
     * @var string - активный модуль.
     */
    public string $module = '';

    /**
     * @var string - активный контроллер.
     */
    public string $controller = '';

    /**
     * @var string - активное действие.
     */
    public string $action = '';

    /**
     * @var array - активные параментры.
     */
    public array $parameters = [];

    /**
     * @var string - тема.
     */
    public string $theme = '';


    /**
     * @var string
     */
    public string $viewPath = '';

    /**
     * @var string|null - права доступа к разделу
     */
    public ?string $assets = null;

    /**
     * Конструктор
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $header = new Header();

        foreach ($config as $key => $value) {
            $this->$key = $value;
        }

       # $header->header($config['header']);

        /**
         * Если модуль - API, то удаляем ненужных переменных и выводим специальный заголовок JSON
         */
        if($config['module'] === 'API') {
            unset($this->viewPath, $this->theme, $this->response);

            $API = new API();

            $header->header('json');
            $API->assets($this);
        }

    }

    /**
     * Возвращает экземпляр контроллера.
     *
     * @return Controller
     */
    public function instance(): Controller
    {
        return $this->instance;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function url(): string
    {
        return Config::item('base_url') . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $this->module . DIRECTORY_SEPARATOR;
    }


    /**
     * @return string
     * @throws Exception
     */
    public function urlTheme(): string {
        $theme = Setting::value('active_theme', 'theme');

        if ($theme === '') {
            $theme = Config::item('defaultTheme');
        }

        return Config::item('base_url') . DIRECTORY_SEPARATOR . 'Content' . DIRECTORY_SEPARATOR . 'themes' .  DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR;
    }

    /**
     * Запускает активное действие контроллера.
     *
     * @throws Exception
     */
    public function run()
    {
        /**
         * Построение имени класса
         */
        $class = '\\Modules\\' . $this->module . '\Controller\\' . $this->controller;

        /**
         * Проверка на существование класса.
         */
        if (class_exists($class)) {
            $this->instance = new $class;
            $this->response = call_user_func_array([$this->instance, $this->action], $this->parameters);

            /**
             * Возвращение ответа.
            */
             return $this->response;
        }

        throw new RuntimeException(
            sprintf('Контроллер <strong>%s</strong> не найден.', $class)
        );
    }
}

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



use Core\Services\Http\Redirect;
use Core\Services\Http\Request;
use Core\Services\Http\Uri;
use Core\Services\Path\Path;
use Core\Services\Template\Layout;
use Exception;
use DI;
use Route;


/**
 * Class Router
 * @package Flexi\Routing
 */
class Router
{

	/**
	 * @var Module - активный модуль.
	 */
	protected static Module $module;

	/**
	 * Возвращение активного модуля
	 *
	 * @return Module
	 */
	public static function module(): Module
	{
		return static::$module;
	}

	/**
	 * Инициализация системы.
	 *
	 * @throws Exception
	 */
	public static function initialize(): void
    {
		# Загрузка роутера
		static::routes();

        $url = Uri::base() . '/' . Uri::segmentString();

		# Поиск текущего маршрута
		$route = Repository::retrieve(Request::method(), Uri::segmentString());

        # Если роутер не был передан.
        if (empty($route)) {
            Redirect::go($url);
        }

		# Создание модуля.
		static::$module = $module = new Module($route);


		# Подключение моделя к DI
		DI::instance()->set('module', static::$module);

		# Запуск моделя
		$response = $module->run();

		# Получение ответа
		if (is_object($response) && method_exists($response, 'respond')) {
            $response->respond();
        }

		# Если есть макет для обработки, то подключаем его
		$layout = $module->instance()->layout;

		# Вывод данных
		if ($layout !== '') {
            echo Layout::get($layout);
        }

		# Выход
		#Run::close();
	}

	/**
	 * Загрузка маршрутизатора приложения
	 */
    private static function routes(): void
    {

        $modules = Path::module();


        # Загружаем файл маршрутов из каждого модуля, в котором он есть.
        foreach (scandir($modules) as $module) {
            # Убедитесь, что это не скрытая папка.
            if (in_array($module, ['.', '..'], true)) {
                continue;
            }

            # Установка модуля
            Route::$module = $module;

            # Если файл существует и доступен
            if (is_file($path = $modules . $module . '/routes.php')) {
                require_once $path;
            }
        }


        # Переписываем маршрут приложения
        static::rewrite();
    }

	/**
	 * Переписывает маршруты приложения.
	 *
	 * @return void
	 */
	private static function rewrite(): void
    {
		foreach (Repository::stored() as $method => $routes) {
			foreach ($routes as $uri => $options) {
				$segments   = explode('/', $uri);
				$rewrite    = false;


				foreach ($segments as $key => $segment) {
					$matches = [];

                    /**
                     * Получить сегменты маршрута URI, которые мы должны переписать.
                     */
					preg_match('/\(([0-9a-z]+):([a-z]+)\)/i', $segment, $matches);

                    /**
                     * У нас есть matches?
                     */
					if (!empty($matches)) {

                        /**
                         * Получить реальное значение для этого сегмента и проверить его против правила.
                         */
						$value  = Uri::segment(($key + 1));
						$rule   = $matches[2];
						$valid  = false;


						/**
                         * Сегменты URI, их правила и опции
                         */
						if ($rule === 'int' && is_numeric($value)) {
                            $valid = true;
                        } else if ($rule === 'any') {
                            $valid = true;
                        }

                        /**
                         * Если сегмент дйствителен, то присваиваем ему зачение.
						*/
                        if ($valid === true) {
                            $segments[$key] = $value;
                        }

                        /**
                         * Добавление параметров
                         */
						if (!isset($options['parameters'])) {
                            $options['parameters'] = [$key => $value];
                        } else {
                            $options['parameters'][] = [$key => $value];
                        }

                        /**
                         * Перезапись URL
                         */
						$rewrite = true;
					}

				}

                /**
                 * Если нужно перезаписывать URL
                 */
				if ($rewrite) {
                    /**
                     * Удаление старого URI
                     */
					Repository::remove($method, $uri);

                    /**
                     * Добавление нового URI
                     */
					Repository::store($method, implode('/', $segments), $options);
				}
			}
		}
	}
}
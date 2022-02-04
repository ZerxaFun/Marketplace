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


namespace Core\Services\Localization;


use Core\Services\Config\Config;
use Core\Services\Path\Path;
use DI;
use JsonException;


/**
 * Класс для работы с локализацией проекта
 *
 * Class I18n
 */
class I18n
{

    private static $instance;

    /**
     * @return I18n
     */
    public static function instance(): I18n
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param string $key
     * @param array $data
     * @return string
     */
    final public function get(string $key, array $data = []): string
    {
        $lang = DI::instance()->get('lang');
        $text = $lang[$key] ?? '';

        if (!empty($data)) {
            $text = sprintf($text, ...$data);
        }

        return $text;
    }

    /**
     * @param string $file
     * @param string $module
     * @return I18n
     */
    final public function load(string $file, string $module = ''): static
    {
        $path    = static::path($module) . $file . '.ini';
        $content = parse_ini_file($path, true);

        $lang = DI::instance()->get('lang') ?: [];

        foreach ($content as $key => $value) {
            $keyLang = str_replace('/', '.', $file) . '.' . $key;

            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $lang[$keyLang . '.' . $k] = $v;
                }
            } else {
                $lang[$keyLang] = $value;
            }
        }


        DI::instance()->set('lang', $lang);

        return $this;
    }

    /**
     * Gets all the valid modules.
     *
     * @return array
     * @throws JsonException
     */
    final public function all(): array
    {
        $module = DI::instance()->get('module');

        $localizations = [];

        $path = Path::module() . sprintf('/%s/Language/', $module->module);

        foreach (scandir($path) as $localization) {
            if ($localization === '.' || $localization === '..') {
                continue;
            }

            // Does the language have a valid lang.php?
            $local = $path . $localization . '/lang.json';
            if (is_file($local)) {
                // Add it to the lang array.
                $localizations[] = json_decode(file_get_contents($local), true, 512, JSON_THROW_ON_ERROR);
            }
        }

        return $localizations;
    }

    /**
     * @return string
     * @param string $moduleName
     */
    private static function path(string $moduleName = ''): string
    {
        $activeLanguage = Config::item('defaultLanguage');

        if ($activeLanguage === '') {
            $activeLanguage = Config::item('default_lang');
        }


        $module = DI::instance()->get('module');

        $moduleModuleName = $module->module;

        if ($moduleName !== '') {
            $moduleModuleName = $moduleName;
        }

        $path = new Path();
        return $path->Module() . sprintf('/%s/Language/%s/', $moduleModuleName, $activeLanguage);
    }
}
<?php
declare(strict_types=1);

/**
 *=====================================================
 * Majestic Next Engine                               =
 *=====================================================
 * @package   http.php                                =
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

namespace Core\Services\Path;

use ErrorException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\NoReturn;

class http
{
    public static array $access = [
        'css' => [
            'header' => 'text/css',
        ],
        'js' => [
            'header' => 'application/javascript',
        ],
        'json' => [
            'header' => 'application/json',
        ],
        'png' => [
            'header' => 'image/png',
        ],
        'jpg' => [
            'header' => 'image/jpeg',
        ],
        'gif' => [
            'header' => 'image/gif',
        ],
        'svg' => [
            'header' => 'image/svg+xml',
        ],
        'xml' => [
            'header' => 'text/xml',
        ],
        'pdf' => [
            'header' => 'application/pdf',
        ],
    ];


    /**
     * @throws ErrorException
     */
    #[NoReturn]
    final public static function content(string $path = ''): string
    {
        $base = self::get($path);

        ob_clean();

        header_remove();
        $length = strlen($base['content']);

        header('Content-Length: '.$length);
        header('Content-type: ' . $base['header'], true);
        # TODO:: вес файла
        #header("Content-length: $size");
        exit($base['content']);
    }
    /**
     * @throws ErrorException
     */
    #[ArrayShape(
        ['access' => "bool",
            'type' => "string",
            'header' => "string",
            'content' => "false|string"]
    )]
    private static function get(string $path): array
    {
        $resource = str_replace('/', DIRECTORY_SEPARATOR, Path::base() . $path);
        if(!file_exists($resource)) {
            throw new ErrorException(
                sprintf('Указан неверный путь к файлу. Файла по пути %s не существует', $resource)
            );
        }

        $validation = self::type($resource);
        $fileType = self::fileType($resource);

        return [
            'access'    => $validation,
            'type'      => $fileType,
            'header'    => self::$access[$fileType]['header'],
            'content'   => file_get_contents($resource)
        ];
    }

    /**
     * Проверка, есть ли у файла расширение.
     *
     * @throws ErrorException
     */
    private static function type(string $file): bool
    {
        # Проверка на наличии в имени файла символа точки
        if (strrpos($file, '.') !== false) {
            return self::validationFile(self::fileType($file));
        }

        throw new ErrorException(
            sprintf('Файл: %s не является доступным файлом. Необходимо явно указать расширение файла', $file)
        );
    }

    private static function fileType(string $file): string
    {
        # Вырезаем часть строки после последнего символа точки в имени файла
        return substr($file, strrpos($file, '.') + 1);
    }

    /**
     * @throws ErrorException
     */
    private static function validationFile(string $fileType): bool
    {
        $validate = array_key_exists($fileType, self::$access) ?? false;

        if($validate === true) {
            return true;
        }

        throw new ErrorException(
            sprintf('Недопустимый формат файла %s', $fileType)
        );

    }

}
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


namespace Core\Services\Template;


use Core\Services\Routing\Router;
use JetBrains\PhpStorm\Pure;


/**
 * Class Engine
 * @package Core\Services\Template
 */
class Engine
{
    /**
     * @return string
     */
    #[Pure] final public function ViewDirectory(): string
    {
        $module = Router::module();
        return sprintf('Modules'. DIRECTORY_SEPARATOR . '%s'. DIRECTORY_SEPARATOR . 'View'. DIRECTORY_SEPARATOR, $module->module);
    }
}

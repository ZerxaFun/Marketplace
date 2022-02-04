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


/**
 * Class AbstractController
 * @package Core\Services\Routing
 */
abstract class AbstractController
{
    /**
     * @var string - макет для использования
     */
    public string $layout = '';

    public bool $vue = false;
    /**
     * @var array - массив data
     */
    public array $data = [];

    /**
     * @var string
     */
    public string $theme = '';
}
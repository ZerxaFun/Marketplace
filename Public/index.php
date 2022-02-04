<?php declare(strict_types=1);


use Core\Bootstrap;
use Core\Services\Debug\Bar\JavascriptRenderer;
use Core\Services\Debug\Bar\ToolBar;
use Core\Services\Routing\Module;

/**
 *=====================================================
 * Majestic Next Engine - by Zerxa Fun                =
 *-----------------------------------------------------
 * @url: http://majestic-studio.com/                  =
 *-----------------------------------------------------
 * @copyright: 2021 Majestic Studio and ZerxaFun      =
 *=====================================================
 * @license GPL version 3                             =
 *=====================================================
 * index.php - исполняемый файл и точка входа         =
 * в систему.                                         =
 * Подключение composer и констант фреймворка         =
 *=====================================================
 */


require '../vendor/autoload.php';


Bootstrap::run(
    dirname(__DIR__)
);

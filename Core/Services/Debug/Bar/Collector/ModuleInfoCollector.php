<?php
/*
 * This file is part of the DebugBar package.
 *
 * (c) 2013 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Services\Debug\Bar\Collector;

use Core\Define;
use Core\Services\Container\DI;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Collects info about PHP
 */
class MVCInfoCollector extends DataCollector implements Renderable
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'controller';
    }

    /**
     * @return array
     */
    #[ArrayShape(['controller' => "string"])]  final public function collect(): array
    {
        $module = DI::instance()->get('module')->module;
        $controller = DI::instance()->get('module')->controller;
        $method = DI::instance()->get('module')->action;
        return [
            'controller' =>  'Module: ' . $module . ', ' . $controller. '@' . $method
        ];
    }

    /**
     * {@inheritDoc}
     */
    #[ArrayShape(["controller" => "string[]"])] final public function getWidgets(): array
    {
        return [
            "controller" => [
                "icon" => "code-merge",
                "tooltip" => "Controller and method",
                "map" => "controller.controller",
                "default" => ""
            ],
        ];
    }
}

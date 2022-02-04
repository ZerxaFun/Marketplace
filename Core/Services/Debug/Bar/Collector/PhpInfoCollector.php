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

use JetBrains\PhpStorm\ArrayShape;

/**
 * Collects info about PHP
 */
class PhpInfoCollector extends DataCollector implements Renderable
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'php';
    }

    /**
     * @return array
     */
    public function collect(): array
    {
        return [
            'version' => implode('.', [PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION]),
            'interface' => PHP_SAPI
        ];
    }

    /**
     * {@inheritDoc}
     */
    #[ArrayShape(["php_version" => "string[]"])] public function getWidgets(): array
    {
        return [
            "php_version" => [
                "icon" => "code",
                "tooltip" => "PHP version",
                "map" => "php.version",
                "default" => ""
            ],
        ];
    }
}

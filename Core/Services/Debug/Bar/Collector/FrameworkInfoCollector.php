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
use JetBrains\PhpStorm\ArrayShape;

/**
 * Collects info about PHP
 */
class FrameworkInfoCollector extends DataCollector implements Renderable
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'majestic';
    }

    /**
     * @return array
     */
    public function collect(): array
    {
        $version = Define::version;
        $branch = Define::branch;
        return [
            'version' => $version . '-' . $branch
        ];
    }

    /**
     * {@inheritDoc}
     */
    #[ArrayShape(["majestic_version" => "string[]"])] public function getWidgets(): array
    {
        return [
            "majestic_version" => [
                "icon" => "code-merge",
                "tooltip" => "Majestic Version",
                "map" => "majestic.version",
                "default" => ""
            ],
        ];
    }
}

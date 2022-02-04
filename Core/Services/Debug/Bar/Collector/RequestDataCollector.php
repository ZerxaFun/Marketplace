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

use Core\Services\Container\DI;
use ErrorException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

/**
 * Collects info about the current request
 */
class RequestDataCollector extends DataCollector implements Renderable, AssetProvider
{
    // The HTML var dumper requires debug bar users to support the new inline assets, which not all
    // may support yet - so return false by default for now.
    protected bool $useHtmlVarDumper = false;

    /**
     * Indicates whether the Symfony HtmlDumper will be used to dump variables for rich variable
     * rendering.
     *
     * @return bool
     */
    final public function isHtmlVarDumperUsed(): bool
    {
        return $this->useHtmlVarDumper;
    }

    /**
     * @return array
     * @throws ErrorException
     */
    final public function collect(): array
    {
        $GLOBALS['_DI'] = (array) DI::instance();
        $vars = ['_ENV', '_GET', '_POST', '_SESSION', '_COOKIE', '_SERVER', '_DI'];
        $data = [];

        foreach ($vars as $var) {
            if (isset($GLOBALS[$var])) {
                $key = "$" . $var;
                if ($this->isHtmlVarDumperUsed()) {
                    $data[$key] = $this->getVarDumper()->renderVar($GLOBALS[$var]);
                } else {
                    $data[$key] = $this->getDataFormatter()->formatVar($GLOBALS[$var]);
                }
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    final public function getName(): string
    {
        return 'request';
    }

    /**
     * @return array
     */
    final public function getAssets(): array
    {
        return $this->isHtmlVarDumperUsed() ? $this->getVarDumper()->getAssets() : array();
    }

    /**
     * @return array
     */
    #[Pure] #[ArrayShape([
        "request" => "string[]"
    ])] final public function getWidgets(): array
    {
        $widget = $this->isHtmlVarDumperUsed()
            ? "ToolBar.Widgets.HtmlVariableListWidget"
            : "ToolBar.Widgets.VariableListWidget";
        return [
            "request" => [
                "icon" => "tags",
                "widget" => $widget,
                "map" => "request",
                "default" => "{}"
            ]
        ];
    }
}

<?php
/*
 * This file is part of the DebugBar package.
 *
 * (c) 2013 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Services\Debug\Bar;

use Core\Services\Debug\Bar\Collector\Renderable;
use ErrorException;
use Exception;
use JsonException;

/**
 * Renders the debug bar using the client side javascript implementation
 *
 * Generates all the needed initialization code of controls
 */
class JavascriptRenderer
{
    public const INITIALIZE_CONSTRUCTOR = 2;

    public const INITIALIZE_CONTROLS = 4;

    public const RELATIVE_PATH = 'path';

    public const RELATIVE_URL = 'url';

    private ToolBar $debugBar;

    private ?string $baseUrl;

    private ?string $basePath;

    private array $cssVendors = [
        'fontawesome' => 'vendor/font-awesome/css/font-awesome.min.css',
        'highlightjs' => 'vendor/highlightjs/styles/github.css'
    ];

    private array $jsVendors = [
        'jquery' => 'vendor/jquery/dist/jquery.min.js',
        'highlightjs' => 'vendor/highlightjs/highlight.pack.js'
    ];

    private bool $includeVendors = true;

    private array $cssFiles = [
        'debugbar.css',
        'widgets.css',
        'openhandler.css'
    ];

    private array $jsFiles = [
        'debugbar.js',
        'widgets.js',
        'openhandler.js'
    ];


    private string $javascriptClass = 'ToolBar.DebugBar';

    private string $variableName = 'toolBar';

    private bool $enableJqueryNoConflict = true;

    private bool $useRequireJs = false;

    private int $initialization;

    private array $controls = [];

    private array $ignoredCollectors = [];

    private string $ajaxHandlerClass = 'ToolBar.AjaxHandler';

    private bool $ajaxHandlerBindToFetch = false;

    private bool $ajaxHandlerBindToJquery = true;

    private bool $ajaxHandlerBindToXHR = false;

    private bool $ajaxHandlerAutoShow = true;

    private string $openHandlerClass = 'ToolBar.OpenHandler';

    private $openHandlerUrl;

    /**
     * @param ToolBar    $debugBar
     * @param string|null $baseUrl
     * @param string|null $basePath
     */
    public function __construct(ToolBar $debugBar, string $baseUrl = null, string $basePath = null)
    {
        $this->debugBar = $debugBar;

        if ($baseUrl === null) {
            $baseUrl = 'Resources';
        }
        $this->baseUrl = $baseUrl;

        if ($basePath === null) {
            $basePath = __DIR__ . DIRECTORY_SEPARATOR . 'Resources';
        }
        $this->basePath = $basePath;

        // bitwise operations cannot be done in class definition :(
        $this->initialization = self::INITIALIZE_CONSTRUCTOR | self::INITIALIZE_CONTROLS;
    }


    /**
     * Adds a control to initialize
     *
     * Possible options:
     *  - icon: icon name
     *  - tooltip: string
     *  - widget: widget class name
     *  - title: tab title
     *  - map: a property name from the data to map the control to
     *  - default: a js string, default value of the data map
     *
     * "icon" or "widget" are at least needed
     *
     * @param string $name
     * @param array  $options
     *
     * @return JavascriptRenderer
     * @throws ErrorException
     */
    final public function addControl(string $name, array $options): static
    {
        if (count(array_intersect(array_keys($options), ['icon', 'widget', 'tab', 'indicator'])) === 0) {
            throw new ErrorException("Not enough options for control '$name'");
        }
        $this->controls[$name] = $options;
        return $this;
    }

    /**
     * Returns the list of asset files
     *
     * @param string|null $type       'css', 'js', 'inline_css', 'inline_js', 'inline_head', or null for all
     * @param string      $relativeTo The type of path to which filenames must be relative (path, url or null)
     *
     * @return array
     */
    final public function getAssets(string $type = null, string $relativeTo = self::RELATIVE_PATH): array
    {
        $cssFiles = $this->cssFiles;
        $jsFiles = $this->jsFiles;
        $inlineCss = [];
        $inlineJs = [];
        $inlineHead = [];

        if ($this->includeVendors !== false) {
            if (!($this->includeVendors !== true && !in_array('css', (array)$this->includeVendors, true))) {
                $cssFiles = array_merge($this->cssVendors, $cssFiles);
            }
            if (!($this->includeVendors !== true && !in_array('js', (array)$this->includeVendors, true))) {
                $jsFiles = array_merge($this->jsVendors, $jsFiles);
            }
        }

        if ($relativeTo) {
            $root = $this->getRelativeRoot($relativeTo, $this->basePath, $this->baseUrl);
            $cssFiles = $this->makeUriRelativeTo($cssFiles, $root);
            $jsFiles = $this->makeUriRelativeTo($jsFiles, $root);
        }


            $basePath = $assets['base_path'] ?? null;
            $baseUrl = $assets['base_url'] ?? null;
            $root = $this->getRelativeRoot($relativeTo,
                $this->makeUriRelativeTo($basePath, $this->basePath),
                $this->makeUriRelativeTo($baseUrl, $this->baseUrl));
            if (isset($assets['css'])) {
                $cssFiles = array_merge($cssFiles, $this->makeUriRelativeTo((array) $assets['css'], $root));
            }
            if (isset($assets['js'])) {
                $jsFiles = array_merge($jsFiles, $this->makeUriRelativeTo((array) $assets['js'], $root));
            }

            if (isset($assets['inline_css'])) {
                $inlineCss = array_merge($inlineCss, (array) $assets['inline_css']);
            }
            if (isset($assets['inline_js'])) {
                $inlineJs = array_merge($inlineJs, (array) $assets['inline_js']);
            }
            if (isset($assets['inline_head'])) {
                $inlineHead = array_merge($inlineHead, (array) $assets['inline_head']);
            }


        // Deduplicate files
        $cssFiles = array_unique($cssFiles);
        $jsFiles = array_unique($jsFiles);

        return $this->filterAssetArray(array($cssFiles, $jsFiles, $inlineCss, $inlineJs, $inlineHead), $type);
    }

    /**
     * Returns the correct base according to the type
     *
     * @param string $relativeTo
     * @param string $basePath
     * @param string $baseUrl
     *
     * @return string|null
     */
    private function getRelativeRoot(string $relativeTo, string $basePath, string $baseUrl): ?string
    {
        if ($relativeTo === self::RELATIVE_PATH) {
            return $basePath;
        }
        if ($relativeTo === self::RELATIVE_URL) {
            return $baseUrl;
        }
        return null;
    }

    /**
     * Makes a URI relative to another
     *
     * @param array|string $uri
     * @param string       $root
     *
     * @return string|array
     */
    private function makeUriRelativeTo(mixed $uri, string $root): string|array
    {
        if (!$root) {
            return $uri;
        }

        if (is_array($uri)) {
            $uris = [];
            foreach ($uri as $u) {
                $uris[] = $this->makeUriRelativeTo($u, $root);
            }
            return $uris;
        }

        if (str_starts_with($uri, '/') || preg_match('/^([a-zA-Z]+:\/\/|[a-zA-Z]:\/|[a-zA-Z]:\\\)/', $uri)) {
            return $uri;
        }
        return rtrim($root, '/') . "/$uri";
    }

    /**
     * Filters a tuple of (css, js, inline_css, inline_js, inline_head) assets according to $type
     *
     * @param array       $array
     * @param string|null $type 'css', 'js', 'inline_css', 'inline_js', 'inline_head', or null for all
     *
     * @return array
     */
    private function filterAssetArray(array $array, string $type = null): array
    {
        $types = array('css', 'js', 'inline_css', 'inline_js', 'inline_head');
        $typeIndex = array_search(strtolower($type), $types);
        return $typeIndex !== false ? $array[$typeIndex] : $array;
    }

    /**
     * Renders the html to include needed assets
     *
     * Only useful if Assetic is not used
     *
     * @return string
     */
    final public function renderHead(): string
    {
        [$cssFiles, $jsFiles, $inlineCss, $inlineJs, $inlineHead] = $this->getAssets(null, self::RELATIVE_URL);
        $html = '';


        foreach ($cssFiles as $file) {
            $html .= sprintf('<link rel="stylesheet" type="text/css" href="%s">' . "\n", $file);
        }

        foreach ($inlineCss as $content) {
            $html .= sprintf('<style>%s</style>' . "\n", $content);
        }

        foreach ($jsFiles as $file) {
            $html .= sprintf('<script type="text/javascript" src="%s"></script>' . "\n", $file);
        }

        foreach ($inlineJs as $content) {
            $html .= sprintf('<script type="text/javascript">%s</script>' . "\n", $content);
        }

        foreach ($inlineHead as $content) {
            $html .= $content . "\n";
        }

        if ($this->enableJqueryNoConflict && !$this->useRequireJs) {
            $html .= '<script type="text/javascript">jQuery.noConflict(true);</script>' . "\n";
        }

        return $html;
    }

    /**
     * Returns the code needed to display the debug bar
     *
     * AJAX request should not render the initialization code.
     *
     * @param boolean $initialize Whether to render the debug bar initialization code
     *
     * @return string
     * @throws JsonException
     * @throws Exception
     */
    final public function render(bool $initialize = true): string
    {
        $js = '';

        if ($initialize) {
            $js = $this->getJsInitializationCode();
        }


        $suffix = !$initialize ? '(ajax)' : null;
        $js .= $this->getAddDatasetCode($this->debugBar->getCurrentRequestId(), $this->debugBar->getData(), $suffix);

        if ($this->useRequireJs){
            return "<script type=\"text/javascript\">\nrequire(['debugbar'], function(ToolBar){ $js });\n</script>\n";
        }

        return "<script type=\"text/javascript\">\n$js\n</script>\n";

    }

    /**
     * Returns the js code needed to initialize the debug bar
     *
     * @return string
     * @throws JsonException
     */
    private function getJsInitializationCode(): string
    {
        $js = '';

        if (($this->initialization & self::INITIALIZE_CONSTRUCTOR) === self::INITIALIZE_CONSTRUCTOR) {
            $js .= sprintf("var %s = new %s();\n", $this->variableName, $this->javascriptClass);
        }

        if (($this->initialization & self::INITIALIZE_CONTROLS) === self::INITIALIZE_CONTROLS) {
            $js .= $this->getJsControlsDefinitionCode($this->variableName);
        }

        if ($this->ajaxHandlerClass) {
            $js .= sprintf("%s.ajaxHandler = new %s(%s, undefined, %s);\n",
                $this->variableName,
                $this->ajaxHandlerClass,
                $this->variableName,
                $this->ajaxHandlerAutoShow ? 'true' : 'false'
            );
            if ($this->ajaxHandlerBindToFetch) {
                $js .= sprintf("%s.ajaxHandler.bindToFetch();\n", $this->variableName);
            }
            if ($this->ajaxHandlerBindToXHR) {
                $js .= sprintf("%s.ajaxHandler.bindToXHR();\n", $this->variableName);
            } elseif ($this->ajaxHandlerBindToJquery) {
                $js .= sprintf("if (jQuery) %s.ajaxHandler.bindToJquery(jQuery);\n", $this->variableName);
            }
        }

        if ($this->openHandlerUrl !== null) {
            $js .= sprintf("%s.setOpenHandler(new %s(%s));\n", $this->variableName,
                $this->openHandlerClass,
                json_encode(["url" => $this->openHandlerUrl], JSON_THROW_ON_ERROR));
        }

        return $js;
    }

    /**
     * Returns the js code needed to initialize the controls and data mapping of the debug bar
     *
     * Controls can be defined by collectors themselves or using {@see addControl()}
     *
     * @param string $varName Debug bar's variable name
     *
     * @return string
     * @throws JsonException
     */
    private function getJsControlsDefinitionCode(string $varName): string
    {
        $js = '';
        $dataMap = [];
        $excludedOptions = ['indicator', 'tab', 'map', 'default', 'widget', 'position'];

        // Find controls provided by collectors
        $widgets = [];
        foreach ($this->debugBar->getCollectors() as $collector) {
            if (($collector instanceof Renderable) && !in_array($collector->getName(), $this->ignoredCollectors, true)) {
                $w = $collector->getWidgets();
                $widgets = array_merge($widgets, $w);
            }
        }
        $controls = array_merge($widgets, $this->controls);

        foreach (array_filter($controls) as $name => $options) {
            $opts = array_diff_key($options, array_flip($excludedOptions));

            if (isset($options['tab']) || isset($options['widget'])) {
                if (!isset($opts['title'])) {
                    $opts['title'] = ucfirst(str_replace('_', ' ', $name));
                }
                $js .= sprintf("%s.addTab(\"%s\", new %s({%s%s}));\n",
                    $varName,
                    $name,
                    $options['tab'] ?? 'ToolBar.DebugBar.Tab',
                    substr(json_encode($opts, JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT), 1, -1),
                    isset($options['widget']) ? sprintf('%s"widget": new %s()', count($opts) ? ', ' : '', $options['widget']) : ''
                );
            } elseif (isset($options['indicator']) || isset($options['icon'])) {
                $js .= sprintf("%s.addIndicator(\"%s\", new %s(%s), \"%s\");\n",
                    $varName,
                    $name,
                    $options['indicator'] ?? 'ToolBar.DebugBar.Indicator',
                    json_encode($opts, JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT),
                    $options['position'] ?? 'right'
                );
            }

            if (isset($options['map'], $options['default'])) {
                $dataMap[$name] = [
                    $options['map'],
                    $options['default']
                ];
            }
        }

        // creates the data mapping object
        $mapJson = [];
        foreach ($dataMap as $name => $values) {
            $mapJson[] = sprintf('"%s": ["%s", %s]', $name, $values[0], $values[1]);
        }
        $js .= sprintf("%s.setDataMap({\n%s\n});\n", $varName, implode(",\n", $mapJson));

        // activate state restoration
        $js .= sprintf("%s.restoreState();\n", $varName);

        return $js;
    }

    /**
     * Returns the js code needed to add a dataset
     *
     * @param string     $requestId
     * @param array      $data
     * @param mixed|null $suffix
     *
     * @return string
     * @throws JsonException
     */
    private function getAddDatasetCode(string $requestId, array $data, mixed $suffix = null): string
    {
        return sprintf("%s.addDataSet(%s, \"%s\"%s);\n",
            $this->variableName,
            json_encode($data, JSON_THROW_ON_ERROR),
            $requestId,
            $suffix ?  : ''
        );
    }

}

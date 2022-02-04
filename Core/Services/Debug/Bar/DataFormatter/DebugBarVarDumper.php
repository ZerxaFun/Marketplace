<?php

namespace Core\Services\Debug\Bar\DataFormatter;

use Core\Services\Debug\Bar\Collector\AssetProvider;
use Core\Services\Debug\Bar\DataFormatter\VarDumper\DebugBarHtmlDumper;
use Core\Services\DevDumper\Cloner\Data;
use Core\Services\DevDumper\Cloner\VarCloner;
use ErrorException;
use JetBrains\PhpStorm\ArrayShape;


/**
 * Clones and renders variables in HTML format using the Symfony VarDumper component.
 *
 * Cloning is decoupled from rendering, so that dumper users can have the fastest possible cloning
 * performance, while delaying rendering until it is actually needed.
 */
class DebugBarVarDumper implements AssetProvider
{
    private static array $defaultClonerOptions = [];

    private static array $defaultDumperOptions = [
        'expanded_depth' => 0,
        'styles' => [
            'default' => 'word-wrap: break-word; white-space: pre-wrap; word-break: normal',
            'num' => 'font-weight:bold; color:#1299DA',
            'const' => 'font-weight:bold',
            'str' => 'font-weight:bold; color:#3A9B26',
            'note' => 'color:#1299DA',
            'ref' => 'color:#7B7B7B',
            'public' => 'color:#000000',
            'protected' => 'color:#000000',
            'private' => 'color:#000000',
            'meta' => 'color:#B729D9',
            'key' => 'color:#3A9B26',
            'index' => 'color:#1299DA',
            'ellipsis' => 'color:#A0A000',
        ],
    ];

    private array $clonerOptions;

    private array $dumperOptions;

    /** @var VarCloner|null */
    private ?VarCloner $cloner;

    /** @var DebugBarHtmlDumper|null */
    private ?DebugBarHtmlDumper $dumper;

    /**
     * Gets the VarCloner instance with configuration options set.
     *
     * @return VarCloner
     */
    private function getCloner(): VarCloner
    {
        if (!$this->cloner) {
            $clonerOptions = $this->getClonerOptions();
            if (isset($clonerOptions['casters'])) {
                $this->cloner = new VarCloner($clonerOptions['casters']);
            } else {
                $this->cloner = new VarCloner();
            }
            if (isset($clonerOptions['additional_casters'])) {
                $this->cloner->addCasters($clonerOptions['additional_casters']);
            }
            if (isset($clonerOptions['max_items'])) {
                $this->cloner->setMaxItems($clonerOptions['max_items']);
            }
            if (isset($clonerOptions['max_string'])) {
                $this->cloner->setMaxString($clonerOptions['max_string']);
            }
            // setMinDepth was added to Symfony 3.4:
            if (isset($clonerOptions['min_depth']) && method_exists($this->cloner, 'setMinDepth')) {
                $this->cloner->setMinDepth($clonerOptions['min_depth']);
            }
        }
        return $this->cloner;
    }

    /**
     * Gets the DebugBarHtmlDumper instance with configuration options set.
     *
     * @return DebugBarHtmlDumper
     */
    private function getDumper(): DebugBarHtmlDumper
    {
        if (!$this->dumper) {
            $this->dumper = new DebugBarHtmlDumper();
            $dumperOptions = $this->getDumperOptions();
            if (isset($dumperOptions['styles'])) {
                $this->dumper->setStyles($dumperOptions['styles']);
            }
        }
        return $this->dumper;
    }

    /**
     * Gets the array of non-default VarCloner configuration options.
     *
     * @return array
     */
    final public function getClonerOptions(): array
    {
        if ($this->clonerOptions === null) {
            $this->clonerOptions = self::$defaultClonerOptions;
        }
        return $this->clonerOptions;
    }

    /**
     * Gets the array of non-default HtmlDumper configuration options.
     *
     * @return array
     */
    final public function getDumperOptions(): array
    {
        if ($this->dumperOptions === null) {
            $this->dumperOptions = self::$defaultDumperOptions;
        }
        return $this->dumperOptions;
    }

    /**
     * Gets the display options for the HTML dumper.
     *
     * @return array
     */
    private function getDisplayOptions(): array
    {
        $displayOptions = array();
        $dumperOptions = $this->getDumperOptions();
        // Only used by Symfony 3.2 and newer:
        if (isset($dumperOptions['expanded_depth'])) {
            $displayOptions['maxDepth'] = $dumperOptions['expanded_depth'];
        }
        // Only used by Symfony 3.2 and newer:
        if (isset($dumperOptions['max_string'])) {
            $displayOptions['maxStringLength'] = $dumperOptions['max_string'];
        }
        // Only used by Symfony 3.2 and newer:
        if (isset($dumperOptions['file_link_format'])) {
            $displayOptions['fileLinkFormat'] = $dumperOptions['file_link_format'];
        }
        return $displayOptions;
    }

    /**
     * Captures and renders the data from a variable to HTML and returns it as a string.
     *
     * @param mixed $data The variable to capture and render.
     *
     * @return string HTML rendering of the variable.
     * @throws ErrorException
     */
    final public function renderVar(mixed $data): string
    {
        return $this->dump($this->getCloner()->cloneVar($data));
    }

    /**
     * Returns assets required for rendering variables.
     *
     * @return array
     */
    #[ArrayShape(['inline_head' => "array"])] final public function getAssets(): array
    {
        $dumper = $this->getDumper();
        $dumper->resetDumpHeader(); // this will cause the default dump header to regenerate
        return [
            'inline_head' => [
                'html_var_dumper' => $dumper->getDumpHeaderByDebugBar(),
            ],
        ];
    }

    /**
     * Helper function to dump a Data object to HTML.
     *
     * @param Data $data
     *
     * @return string
     */
    private function dump(Data $data): string
    {
        $dumper = $this->getDumper();
        $output = fopen('php://memory', 'r+b');
        $dumper->setOutput($output);
        $dumper->setDumpHeader(''); // we don't actually want a dump header
        // NOTE:  Symfony 3.2 added the third $extraDisplayOptions parameter.  Older versions will
        // safely ignore it.
        $dumper->dump($data, null, $this->getDisplayOptions());
        $result = stream_get_contents($output, -1, 0);
        fclose($output);
        return $result;
    }
}

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

use Closure;
use ErrorException;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Collects info about the request duration as well as providing
 * a way to log duration of any operations
 */
class TimeDataCollector extends DataCollector implements Renderable
{
    /**
     * @var float
     */
    protected float $requestStartTime;

    /**
     * @var float
     */
    protected $requestEndTime;

    /**
     * @var array
     */
    protected array $startedMeasures = [];

    /**
     * @var array
     */
    protected array $measures = [];

    /**
     * @param float|null $requestStartTime
     */
    public function __construct(float $requestStartTime = null)
    {
        if ($requestStartTime === null) {
            $requestStartTime = $_SERVER['REQUEST_TIME_FLOAT'];
        }
        $this->requestStartTime = (float)$requestStartTime;
    }

    /**
     * Starts a measure
     *
     * @param string      $name      Internal name, used to stop the measure
     * @param string|null $label     Public name
     * @param string|null $collector The source of the collector
     */
    final public function startMeasure(string $name, string $label = null, string $collector = null): void
    {
        $start = microtime(true);
        $this->startedMeasures[$name] = array(
            'label' => $label ?: $name,
            'start' => $start,
            'collector' => $collector
        );
    }

    /**
     * Check a measure exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasStartedMeasure(string $name): bool
    {
        return isset($this->startedMeasures[$name]);
    }

    /**
     * Stops a measure
     *
     * @param string $name
     * @param array  $params
     *
     * @throws ErrorException
     */
    final public function stopMeasure(string $name, array $params = []): void
    {
        $end = microtime(true);
        if (!$this->hasStartedMeasure($name)) {
            throw new ErrorException("Failed stopping measure '$name' because it hasn't been started");
        }
        $this->addMeasure(
            $this->startedMeasures[$name]['label'],
            $this->startedMeasures[$name]['start'],
            $end,
            $params,
            $this->startedMeasures[$name]['collector']
        );
        unset($this->startedMeasures[$name]);
    }

    /**
     * Adds a measure
     *
     * @param string             $label
     * @param float              $start
     * @param float              $end
     * @param array              $params
     * @param string|null $collector
     */
    final public function addMeasure(string $label, float $start, float $end, array $params = [], string $collector = null): void
    {
        $this->measures[] = array(
            'label' => $label,
            'start' => $start,
            'relative_start' => $start - $this->requestStartTime,
            'end' => $end,
            'relative_end' => $end - $this->requestEndTime,
            'duration' => $end - $start,
            'duration_str' => $end - $start,
            'params' => $params,
            'collector' => $collector
        );
    }

    /**
     * Utility function to measure the execution of a Closure
     *
     * @param string      $label
     * @param Closure     $closure
     * @param string|null $collector
     *
     * @return mixed
     * @throws ErrorException
     */
    final public function measure(string $label, Closure $closure, string $collector = null): mixed
    {
        $name = spl_object_hash($closure);
        $this->startMeasure($name, $label, $collector);
        $result = $closure();
        $params = is_array($result) ? $result : array();
        $this->stopMeasure($name, $params);
        return $result;
    }

    /**
     * Returns the duration of a request
     *
     * @return float
     */
    final public function getRequestDuration(): float
    {
        if ($this->requestEndTime !== null) {
            return $this->requestEndTime - $this->requestStartTime;
        }
        return microtime(true) - $this->requestStartTime;
    }

    /**
     * @return array
     * @throws ErrorException
     */
    #[ArrayShape([
        'start' => "float",
        'end' => "float",
        'duration' => "float",
        'duration_str' => "string",
        'measures' => "array"
    ])] final public function collect(): array
    {
        $this->requestEndTime = microtime(true);
        foreach (array_keys($this->startedMeasures) as $name) {
            $this->stopMeasure($name);
        }

        usort($this->measures, static function($a, $b) {
            if ($a['start'] === $b['start']) {
                return 0;
            }
            return $a['start'] < $b['start'] ? -1 : 1;
        });

        return [
            'start' => $this->requestStartTime,
            'end' => $this->requestEndTime,
            'duration' => $this->getRequestDuration(),
            'duration_str' => $this->getDataFormatter()->formatDuration($this->getRequestDuration()),
            'measures' => array_values($this->measures)
        ];
    }

    /**
     * @return string
     */
    final public function getName(): string
    {
        return 'time';
    }

    /**
     * @return array
     */
    #[ArrayShape([
        "time" => "string[]",
        "timeline" => "string[]"
    ])] final public function getWidgets(): array
    {
        return [
            "time" => [
                "icon" => "clock-o",
                "tooltip" => "Request Duration",
                "map" => "time.duration_str",
                "default" => "'0ms'"
            ],
            "timeline" => [
                "icon" => "tasks",
                "widget" => "ToolBar.Widgets.TimelineWidget",
                "map" => "time",
                "default" => "{}"
            ]
        ];
    }
}

<?php
/*
 * This file is part of the ToolBar package.
 *
 * (c) 2013 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Services\Debug\Bar;

use ArrayAccess;
use Core\Services\Debug\Bar\Collector\DataCollectorInterface;
use Core\Services\Debug\Bar\Collector\FrameworkInfoCollector;
use Core\Services\Debug\Bar\Collector\MemoryCollector;
use Core\Services\Debug\Bar\Collector\MessagesCollector;
use Core\Services\Debug\Bar\Collector\MVCInfoCollector;
use Core\Services\Debug\Bar\Collector\PhpInfoCollector;
use Core\Services\Debug\Bar\Collector\RequestDataCollector;
use Core\Services\Debug\Bar\Collector\TimeDataCollector;
use Exception;


/**
 * Main ToolBar object
 *
 * Manages data collectors. ToolBar provides an array-like access
 * to collectors by name.
 *
 * <code>
 *     $debugbar = new ToolBar();
 *     $debugbar->addCollector(new DataCollector\MessagesCollector());
 *     $debugbar['messages']->addMessage("foobar");
 * </code>
 */
class ToolBar implements ArrayAccess
{
    protected array $collectors = [];

    protected $data;

    protected $jsRenderer;

    protected $requestId;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->addCollector(new MVCInfoCollector());
        $this->addCollector(new PhpInfoCollector());
        $this->addCollector(new MessagesCollector());
        $this->addCollector(new RequestDataCollector());
        $this->addCollector(new TimeDataCollector());
        $this->addCollector(new MemoryCollector());
        $this->addCollector(new FrameworkInfoCollector());

    }

    /**
     * Adds a data collector
     *
     * @param DataCollectorInterface $collector
     *
     * @throws Exception
     * @return $this
     */
    final public function addCollector(DataCollectorInterface $collector): self
    {
        if ($collector->getName() === '__meta') {
            throw new Exception("'__meta' is a reserved name and cannot be used as a collector name");
        }
        if (isset($this->collectors[$collector->getName()])) {
            throw new Exception("'{$collector->getName()}' is already a registered collector");
        }
        $this->collectors[$collector->getName()] = $collector;
        return $this;
    }

    /**
     * Checks if a data collector has been added
     *
     * @param string $name
     *
     * @return boolean
     */
    final public function hasCollector(string $name): bool
    {
        return isset($this->collectors[$name]);
    }

    /**
     * Returns a data collector
     *
     * @param string $name
     *
     * @return DataCollectorInterface
     * @throws Exception
     */
    final public function getCollector(string $name): DataCollectorInterface
    {
        if (!isset($this->collectors[$name])) {
            throw new Exception("'$name' is not a registered collector");
        }
        return $this->collectors[$name];
    }

    /**
     * Returns an array of all data collectors
     *
     * @return array[DataCollectorInterface]
     */
    final public function getCollectors(): array
    {
        return $this->collectors;
    }

    /**
     * Returns the id of the current request
     *
     * @return string
     * @throws Exception
     */
    final public function getCurrentRequestId(): string
    {
        if ($this->requestId === null) {
            $this->requestId = bin2hex(random_bytes(16));
        }
        return $this->requestId;
    }
    /**
     * Collects the data from the collectors
     *
     * @return array
     * @throws Exception
     */
    final public function collect(): array
    {
        if (PHP_SAPI === 'cli') {
            $ip = gethostname();
            if ($ip) {
                $ip = gethostbyname($ip);
            } else {
                $ip = '127.0.0.1';
            }
            $request_variables = array(
                'method' => 'CLI',
                'uri' => isset($_SERVER['SCRIPT_FILENAME']) ? realpath($_SERVER['SCRIPT_FILENAME']) : null,
                'ip' => $ip
            );
        } else {
            $request_variables = [
                'method' => $_SERVER['REQUEST_METHOD'] ?? null,
                'uri' => $_SERVER['REQUEST_URI'] ?? null,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null
            ];
        }
        $this->data = [
            '__meta' => array_merge(
                [
                    'id' => $this->getCurrentRequestId(),
                    'datetime' => date('Y-m-d H:i:s'),
                    'uptime' => microtime(true)
                ],
                $request_variables
            )
        ];

        foreach ($this->collectors as $name => $collector) {
            $this->data[$name] = $collector->collect();
        }

        // Remove all invalid (non UTF-8) characters
        array_walk_recursive($this->data, static function (&$item) {
                if (is_string($item) && !mb_check_encoding($item, 'UTF-8')) {
                    $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
                }
            });

        return $this->data;
    }

    /**
     * Returns collected data
     *
     * Will collect the data if none have been collected yet
     *
     * @return array
     * @throws Exception
     */
    final public function getData(): array
    {
        if ($this->data === null) {
            $this->collect();
        }
        return $this->data;
    }

    /**
     * Returns a JavascriptRenderer for this instance
     *
     * @param string|null $baseUrl
     * @param string|null $basePath
     *
     * @return JavascriptRenderer
     */
    final public function getJavascriptRenderer(string $baseUrl = null, string $basePath = null): JavascriptRenderer
    {
        if ($this->jsRenderer === null) {
            $this->jsRenderer = new JavascriptRenderer($this, $baseUrl, $basePath);
        }
        return $this->jsRenderer;
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @throws Exception
     */
    final public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new Exception("ToolBar[] is read-only");
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset
     *
     * @return DataCollectorInterface
     * @throws Exception
     */
    final public function offsetGet(mixed $offset): DataCollectorInterface
    {
        return $this->getCollector($offset);
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset
     *
     * @return bool
     */
    final public function offsetExists(mixed $offset): bool
    {
        return $this->hasCollector($offset);
    }

    /**
     * ArrayAccess implementation
     *
     * @throws Exception
     */
    final public function offsetUnset(mixed $offset): void
    {
        throw new Exception("ToolBar[] is read-only");
    }
}

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

use ArrayAccess;
use ErrorException;

/**
 * Aggregates data from multiple collectors
 *
 * <code>
 * $aggcollector = new AggregateCollector('foobar');
 * $aggcollector->addCollector(new MessagesCollector('msg1'));
 * $aggcollector->addCollector(new MessagesCollector('msg2'));
 * $aggcollector['msg1']->addMessage('hello world');
 * </code>
 */
class AggregatedCollector implements DataCollectorInterface, ArrayAccess
{
    private string $name;

    private ?string $mergeProperty;

    private bool|string $sort;

    private array $collectors = [];

    /**
     * @param string      $name
     * @param string|null $mergeProperty
     * @param boolean     $sort
     */
    public function __construct(string $name, string $mergeProperty = null, string|bool $sort = false)
    {
        $this->name = $name;
        $this->mergeProperty = $mergeProperty;
        $this->sort = $sort;
    }

    /**
     * @param DataCollectorInterface $collector
     */
    final public function addCollector(DataCollectorInterface $collector): void
    {
        $this->collectors[$collector->getName()] = $collector;
    }

    /**
     * @return array
     */
    final public function collect(): array
    {
        $aggregate = [];
        foreach ($this->collectors as $collector) {
            $data = $collector->collect();
            if ($this->mergeProperty !== null) {
                $data = $data[$this->mergeProperty];
            }
            $aggregate = array_merge($aggregate, $data);
        }

        return $this->sort($aggregate);
    }

    /**
     * Sorts the collected data
     *
     * @param array $data
     *
     * @return array
     */
    private function sort(array $data): array
    {
        if (is_string($this->sort)) {
            $p = $this->sort;
            usort($data, static function ($a, $b) use ($p) {
                if ($a[$p] === $b[$p]) {
                    return 0;
                }
                return $a[$p] < $b[$p] ? -1 : 1;
            });
        } elseif ($this->sort === true) {
            sort($data);
        }
        return $data;
    }

    /**
     * @return string
     */
    final public function getName(): string
    {
        return $this->name;
    }

    // --------------------------------------------
    // ArrayAccess implementation

    /**
     * @param       $offset
     * @param mixed $value
     *
     * @throws ErrorException
     */
    final public function offsetSet($offset, $value)
    {
        throw new ErrorException("AggregatedCollector[] is read-only");
    }

    /**
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->collectors[$key];
    }

    /**
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return isset($this->collectors[$key]);
    }

    /**
     * @param mixed $key
     * @throws DebugBarException
     */
    public function offsetUnset($key)
    {
        throw new DebugBarException("AggregatedCollector[] is read-only");
    }
}

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
 * Collects info about memory usage
 */
class MemoryCollector extends DataCollector implements Renderable
{
    protected bool $realUsage = false;

    protected int $peakUsage = 0;

    /**
     * Updates the peak memory usage value
     */
    final public function updatePeakUsage(): void
    {
        $this->peakUsage = memory_get_peak_usage($this->realUsage);
    }

    /**
     * @return array
     */
    #[ArrayShape(['peak_usage' => "int", 'peak_usage_str' => "string"])] final public function collect(): array
    {
        $this->updatePeakUsage();
        return [
            'peak_usage' => $this->peakUsage,
            'peak_usage_str' => $this->getDataFormatter()->formatBytes($this->peakUsage, 0)
        ];
    }

    /**
     * @return string
     */
    final public function getName(): string
    {
        return 'memory';
    }

    /**
     * @return array
     */
    #[ArrayShape(["memory" => "string[]"])] final public function getWidgets(): array
    {
        return array(
            "memory" => [
                "icon" => "cogs",
                "tooltip" => "Memory Usage",
                "map" => "memory.peak_usage_str",
                "default" => "'0B'"
            ]
        );
    }
}

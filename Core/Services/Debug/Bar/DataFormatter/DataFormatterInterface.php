<?php
/*
 * This file is part of the DebugBar package.
 *
 * (c) 2013 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Services\Debug\Bar\DataFormatter;

/**
 * Formats data to be outputed as string
 */
interface DataFormatterInterface
{
    /**
     * Transforms a PHP variable to a string representation
     *
     * @param mixed $data
     *
     * @return string
     */
    public function formatVar(mixed $data): string;

    /**
     * Transforms a duration in seconds in a readable string
     *
     * @param float $seconds
     *
     * @return string
     */
    public function formatDuration(float $seconds): string;

    /**
     * Transforms a size in bytes to a human readable string
     *
     * @param string  $size
     * @param integer $precision
     *
     * @return string
     */
    public function formatBytes(string $size, int $precision = 2): string;
}

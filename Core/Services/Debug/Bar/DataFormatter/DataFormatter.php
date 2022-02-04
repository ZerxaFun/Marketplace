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


use Core\Services\DevDumper\Cloner\VarCloner;
use Core\Services\DevDumper\Dumper\CliDumper;
use ErrorException;

class DataFormatter implements DataFormatterInterface
{
    private VarCloner $cloner;
    private CliDumper $dumper;

    /**
     * DataFormatter constructor.
     */
    public function __construct()
    {
        $this->cloner = new VarCloner();
        $this->dumper = new CliDumper();
    }

    /**
     * @param mixed $data
     *
     * @return string
     * @throws ErrorException
     */
    final public function formatVar(mixed $data): string
    {
        $output = '';

        $this->dumper->dump(
            $this->cloner->cloneVar($data),
            function ($line, $depth) use (&$output) {
                // A negative depth means "end of dump"
                if ($depth >= 0) {
                    // Adds a two spaces indentation to the line
                    $output .= str_repeat('  ', $depth).$line."\n";
                }
            }
        );

        return trim($output);
    }

    /**
     * @param float $seconds
     *
     * @return string
     */
    final public function formatDuration(float $seconds): string
    {
        if ($seconds < 0.001) {
            return round($seconds * 1000000) . 'ms';
        }

        if ($seconds < 0.1) {
            return round($seconds * 1000, 2) . 'ms';
        }

        if ($seconds < 1) {
            return round($seconds * 1000) . 'ms';
        }
        return round($seconds, 2) . 's';
    }

    /**
     * @param string $size
     * @param int    $precision
     *
     * @return string
     */
    final public function formatBytes(string $size, int $precision = 2): string
    {
        $sign = $size < 0 ? '-' : '';
        $sizes = abs($size);

        $base = log($sizes) / log(1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];
        return $sign . round(1024 ** ($base - floor($base)), $precision) . $suffixes[floor($base)];
    }
}

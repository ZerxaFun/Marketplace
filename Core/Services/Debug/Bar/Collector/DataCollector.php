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

use Core\Services\Debug\Bar\DataFormatter\DataFormatter;
use Core\Services\Debug\Bar\DataFormatter\DataFormatterInterface;
use Core\Services\Debug\Bar\DataFormatter\DebugBarVarDumper;

/**
 * Abstract class for data collectors
 */
abstract class DataCollector implements DataCollectorInterface
{
    private static  $defaultDataFormatter;
    private static DebugBarVarDumper $defaultVarDumper;

    protected $dataFormater;
    protected DebugBarVarDumper $varDumper;

    /**
     * Returns the default data formater
     *
     * @return DataFormatter|DataFormatterInterface
     */
    final public static function getDefaultDataFormatter(): DataFormatter|DataFormatterInterface
    {
        if (self::$defaultDataFormatter === null) {
            self::$defaultDataFormatter = new DataFormatter();
        }
        return self::$defaultDataFormatter;
    }

    /**
     * @return DataFormatter|DataFormatterInterface
     */
    final public function getDataFormatter(): DataFormatter|DataFormatterInterface
    {
        if ($this->dataFormater === null) {
            $this->dataFormater = self::getDefaultDataFormatter();
        }
        return $this->dataFormater;
    }

    /**
     * Returns the default variable dumper
     *
     * @return DebugBarVarDumper
     */
    public static function getDefaultVarDumper(): DebugBarVarDumper
    {
        if (self::$defaultVarDumper === null) {
            self::$defaultVarDumper = new DebugBarVarDumper();
        }
        return self::$defaultVarDumper;
    }

    /**
     * Gets the variable dumper instance used by this collector; note that collectors using this
     * instance need to be sure to return the static assets provided by the variable dumper.
     *
     * @return DebugBarVarDumper
     */
    final public function getVarDumper(): DebugBarVarDumper
    {
        if ($this->varDumper === null) {
            $this->varDumper = self::getDefaultVarDumper();
        }
        return $this->varDumper;
    }
}

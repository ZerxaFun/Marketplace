<?php

declare(strict_types=1);

/**
 *=====================================================
 * Majestic Engine                                    =
 *=====================================================
 * @package Core\Bootstrap                            =
 *-----------------------------------------------------
 * @url http://majestic-studio.com/                   =
 *-----------------------------------------------------
 * @copyright 2021 Majestic Studio                    =
 *=====================================================
 * @author ZerxaFun aKa Zerxa                         =
 *=====================================================
 * @license GPL version 3                             =
 *=====================================================
 *                                                    =
 *                                                    =
 *=====================================================
 */

namespace Core;


class Define
{
    /**
     * Минимальная версия PHP для запуска системы
     */
    public const PHP_MIN = 8.0;

    public const NAME_HEAD = 'Majestic System';
    public const branch = 'alpha';
    public const version = 0.1;
    public const production = false;

    /**
     * Тип проекта, в разработке, либо публичный проект
     *
     * @var bool $_ENV['developer']
     */
    public static bool $developer;

    public static bool $htmlMinification;
    private static bool $eval = false;

    /**
     * @return bool
     */
    public static function isDeveloper(): bool
    {
        self::setDeveloper();

        return self::$developer;
    }

    /**
     * @return void
     */
    private static function setDeveloper(): void
    {

        self::$developer = $_ENV['developer'];
    }

    /**
     * @return bool
     */
    public static function isHtmlMinification(): bool
    {
        self::setHtmlMinification();

        return self::$htmlMinification;
    }

    /**
     */
    public static function setHtmlMinification(): void
    {
        self::$htmlMinification = $_ENV['htmlMinification'];
    }

    public static function setEval(bool $eval): bool
    {
        return self::$eval = $eval;
    }

    public static function getEval(): bool
    {
        return self::$eval;
    }
}

<?php


namespace Core\Services\Environment;


use Core\Services\Path\Path;

class Dotenv
{
    private const REGEX = '(?i:[A-Z][A-Z0-9_]*+)';
    private const STATE = 0;
    private const STATE_VALUE = 1;

    private static int $cursor;
    private static int $lineno;
    private static string|null $data;
    private static string $end;
    private static array $values;

    /**
     * Установка маршрута к файлу окружения
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        self::load($path);
    }

    public static function initialize(string $path = ''): Dotenv
    {
        if($path === '') {
            $path = Path::base() . '.env';
        }

        return new Dotenv($path);
    }

    /**
     * Загрузка файла окружения
     *
     * @param string $path              - путь к файлу
     * @param string ...$extraPaths     - путь к дополнительным файлам
     */
    public static function load(string $path, string ...$extraPaths): void
    {
        self::doLoad(func_get_args());
    }

    public static function populate(array $values): void
    {
        foreach ($values as $name => $value) {
            $_ENV[$name] = $value;
        }
    }

    public static function parse(string $data): array
    {
        self::$data = str_replace(["\r\n", "\r"], "\n", $data);

        self::$lineno = 1;
        self::$cursor = 0;
        self::$end = strlen(self::$data);
        $state = self::STATE;
        self::$values = [];
        $name = '';

        self::skipEmptyLines();

        while (self::$cursor < self::$end) {
            switch ($state) {
                case self::STATE:
                    $name = self::lexVarName();
                    $state = self::STATE_VALUE;
                    break;

                case self::STATE_VALUE:
                    self::$values[$name] = self::lexValue();
                    $state = self::STATE;
                    break;
            }

        }


        foreach(self::$values as $key => $value) {
            if(self::$values[$key] === 'false') {
                self::$values[$key] = false;

            } elseif(self::$values[$key] === 'true') {
                self::$values[$key] = true;
            }
        }

        if (self::STATE_VALUE === $state) {
            self::$values[$name] = '';
        }

        try {
            return self::$values;
        } finally {
            self::$values = [];
            self::$data = null;
        }
    }

    private static function lexVarName(): string
    {
        preg_match('/(export[ \t]++)?('.self::REGEX.')/A', self::$data, $matches, 0, self::$cursor);
        self::moveCursor($matches[0]);

        ++self::$cursor;
        return $matches[2];
    }

    private static function lexValue(): string
    {
        $v = '';
        do {
            if ("'" === self::$data[self::$cursor]) {
                $len = 0;


                $v .= substr(self::$data, 1 + self::$cursor, $len - 1);
                self::$cursor += 1 + $len;
            } elseif ('"' === self::$data[self::$cursor]) {
                $value = '';

                while ('"' !== self::$data[self::$cursor] || ('\\' === self::$data[self::$cursor - 1] && '\\' !== self::$data[self::$cursor - 2])) {
                    $value .= self::$data[self::$cursor];
                    ++self::$cursor;

                }
                ++self::$cursor;
                $value = str_replace(['\\"', '\r', '\n'], ['"', "\r", "\n"], $value);
                $resolvedValue = $value;
                $resolvedValue = str_replace('\\\\', '\\', $resolvedValue);
                $v .= $resolvedValue;

            } else {
                $value = '';
                $prevChr = self::$data[self::$cursor - 1];
                while (self::$cursor < self::$end && !in_array(self::$data[self::$cursor], ["\n", '"', "'"], true) && !((' ' === $prevChr || "\t" === $prevChr) && '#' === self::$data[self::$cursor])) {
                    if ('\\' === self::$data[self::$cursor] && isset(self::$data[self::$cursor + 1]) && ('"' === self::$data[self::$cursor + 1] || "'" === self::$data[self::$cursor + 1])) {
                        ++self::$cursor;
                    }

                    $value .= $prevChr = self::$data[self::$cursor];

                    ++self::$cursor;
                }
                $value = rtrim($value);

                $resolvedValue = $value;
                $resolvedValue = str_replace(['\\\\', '='], ['\\', ''], $resolvedValue);
                $resolvedValue = preg_replace('/\s+/', '', $resolvedValue);

                $v .= $resolvedValue;

                if (self::$cursor < self::$end && '#' === self::$data[self::$cursor]) {
                    break;
                }
            }
        } while (self::$cursor < self::$end && "\n" !== self::$data[self::$cursor]);

        self::skipEmptyLines();

        return $v;
    }

    private static  function skipEmptyLines(): void
    {
        if (preg_match('/(?:\s*+(?:#[^\n]*+)?+)++/A', self::$data, $match, 0, self::$cursor)) {
            self::moveCursor($match[0]);
        }
    }

    private static function moveCursor(string $text): void
    {
        self::$cursor += strlen($text);
        self::$lineno += substr_count($text, "\n");
    }

    private static function doLoad(array $paths): void
    {
        foreach ($paths as $path) {
            self::populate(self::parse(file_get_contents($path)));
        }
    }
}

<?php

/**
 * @package Flextype Components
 *
 * @author Sergey Romanenko <awilum@yandex.ru>
 * @link http://components.flextype.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Services\ErrorHandler;


use Core\Define;
use Core\Services\Path\Path;
use ErrorException;
use Exception;

class ErrorHandler
{
    /**
     * Массив ошибок, уровни
     */
    public static array $levels = [
        E_ERROR => 'Fatal Error',
        E_PARSE => 'Parse Error',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_STRICT => 'Strict Mode Error',
        E_NOTICE => 'Notice',
        E_WARNING => 'Warning',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_USER_NOTICE => 'Notice',
        E_USER_WARNING => 'Warning',
        E_USER_ERROR => 'Error',
    ];

    /**
     * Подключение обработчика ошибок
     */
    public function __construct()
    {

        set_error_handler('Core\Services\ErrorHandler\ErrorHandler::error');
        register_shutdown_function('Core\Services\ErrorHandler\ErrorHandler::fatal');
        set_exception_handler('Core\Services\ErrorHandler\ErrorHandler::exception');
    }

    public static function initialize(): ErrorHandler
    {
        return new ErrorHandler();
    }

    /**
     * Returns an array of lines from a file.
     *
     * @param string $file File in which you want to highlight a line
     * @param int $line Line numbers to highlight
     * @return array
     */
    private static function highlightCode(string $file, int $line): array
    {
        $handle = fopen($file, 'rb');
        $lines = [];
        $currentLine = 0;

        while (!feof($handle)) {
            $currentLine++;

            $temp = fgets($handle);

            if ($currentLine > $line + 15) {
                break; // Exit loop after we have found what we were looking for
            }

            if ($currentLine >= ($line - 30) && $currentLine <= ($line + 30)) {
                $lines[] = [
                    'number' => str_pad($currentLine, 4, ' ', STR_PAD_LEFT),
                    'highlighted' => ($currentLine === $line),
                    'code' => self::highlightString($temp),
                ];
            }
        }

        fclose($handle);

        return $lines;
    }

    /**
     * Converts errors to ErrorExceptions.
     *
     * @param int $code The error code
     * @param string $message The error message
     * @param string $file The filename where the error occurred
     * @param int $line The line number where the error occurred
     * @return bool
     * @throws ErrorException
     */
    public static function error(int $code, string $message, string $file, int $line): bool
    {
        // If isset error_reporting and $code then throw new error exception
        if ((error_reporting() & $code) !== 0) {

            /**
             * Don't throw NOTICE exception for PRODUCTION Environment. Just write to log.
             */
            if (Define::isDeveloper() === false && $code === 8) {

                // Get exception info
                $error['code'] = $code;
                $error['message'] = $message;
                $error['file'] = $file;
                $error['line'] = $line;
                $error['type'] = 'ErrorException: ';

                $codes = array(
                    E_USER_NOTICE => 'Notice',
                );

                $error['type'] .= array_key_exists($error['code'], $codes) ? $codes[$error['code']] : 'Unknown Error';

                // Write to log
                self::writeLogs("{$error['type']}: {$error['message']} in {$error['file']} at line {$error['line']}");

            } else {
                throw new ErrorException($message, $code, 0, $file, $line);
            }
        }

        // Don't execute PHP internal error handler
        return true;
    }

    /**
     * Highlight string
     *
     * @param string $string String
     * @return string
     */
    private static function highlightString(string $string): string
    {
        $search = array("\r\n", "\n\r", "\r", "\n",
            '<code>', '</code>',
            '#$@r4!/*',
            '<span style="color: #0000BB">&lt;?php&nbsp;',
            '<span style="color: #0000BB">',
            '<span style="color: #007700">',
        );
        $replace = array('', '', '', '',
            '', '',
            '/*',
            '<span style="color: #a626a4">',
            '<span style="color: #4078f2">',
            '<span style="color: #a626a4">',
        );

        return str_replace($search, $replace, highlight_string('<?php ' . str_replace('/*', '#$@r4!/*', $string), true));
    }

    /**
     * Modifies the backtrace array.
     *
     * @param array $backtrace Array returned by the getTrace() method of an exception object
     * @return array
     */
    private static function formatBacktrace(array $backtrace): array
    {
        if (is_array($backtrace) === false || count($backtrace) === 0) {
            return $backtrace;
        }

        /**
         * Remove unnecessary info from backtrace
         */
        if ($backtrace[0]['function'] === '{closure}') {
            unset($backtrace[0]);
        }

        /**
         * Format backtrace
         */
        $trace = [];

        foreach ($backtrace as $entry) {

            /**
             * Function
             */
            $function = '';

            if (isset($entry['class'])) {
                $function .= $entry['class'] . $entry['type'];
            }

            $function .= $entry['function'] . '()';

            /**
             * Arguments
             */
            $arguments = [];

            if (isset($entry['args']) && count($entry['args']) > 0) {
                foreach ($entry['args'] as $arg) {
                    ob_start();

                    var_dump($arg);

                    $arg = htmlspecialchars(ob_get_clean());

                    $arguments[] = $arg;
                }
            }

            /**
             * Location
             */
            $location = [];

            if (isset($entry['file'])) {
                $location['file'] = $entry['file'];
                $location['line'] = $entry['line'];
                $location['code'] = self::highlightCode($entry['file'], $entry['line']);
            }

            /**
             * Compile into array
             */
            $trace[] = array
            (
                'function' => $function,
                'arguments' => $arguments,
                'location' => $location,
            );
        }

        return $trace;
    }

    /**
     * Convert errors not caught by the error handler to ErrorExceptions.
     */
    public static function fatal(): void
    {
        $e = error_get_last();

        if ($e !== null && (error_reporting() & $e['type']) !== 0) {
            self::exception(new ErrorException($e['message'], $e['type'], 0, $e['file'], $e['line']));

            exit(1);
        }
    }

    /**
     * Writes message to log.
     *
     * @param string $message The message to write to the log
     * @return bool
     */
    public static function writeLogs(string $message): bool
    {
        return file_put_contents(Path::exceptionLog() . gmdate('Y_m_d') . '.log',
            '[' . gmdate('d-M-Y H:i:s') . '] ' . $message . PHP_EOL,
            FILE_APPEND);
    }

    /**
     * Handles uncaught exceptions and returns a pretty error screen.
     *
     * @param object $exception An exception object
     */
    public static function exception(object $exception): void
    {
        try {

            // Empty output buffers
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // Get exception info
            $error['code'] = $exception->getCode();
            $error['message'] = $exception->getMessage();
            $error['file'] = $exception->getFile();
            $error['line'] = $exception->getLine();

            // Determine error type
            if ($exception instanceof ErrorException) {
                $error['type'] = 'ErrorException: ';
                $error['type'] .= array_key_exists($error['code'], self::$levels) ? self::$levels[$error['code']] : 'Unknown Error';
            } else {
                $error['type'] = get_class($exception);
            }

            // Write to log
            self::writeLogs("{$error['type']}: {$error['message']} in {$error['file']} at line {$error['line']}");

            // Send headers and output
            @header('Content-Type: text/html; charset=UTF-8');

            if (Define::isDeveloper() === true) {

                $error['backtrace'] = $exception->getTrace();

                if ($exception instanceof ErrorException) {
                    $error['backtrace'] = array_slice($error['backtrace'], 1); //Remove call to error handler from backtrace
                }

                $error['backtrace'] = self::formatBacktrace($error['backtrace']);
                $error['highlighted'] = self::highlightCode($error['file'], $error['line']);

                @header('HTTP/1.1 500 Internal Server Error');
                include sprintf("%sErrorHandler.php", Path::module('Error' . DIRECTORY_SEPARATOR . 'View'));

            } elseif(Define::isDeveloper() === false) {

                @header('HTTP/1.1 500 Internal Server Error');
                include sprintf("%s500.php", Path::module('Error/View/'));
            }

        } catch (Exception $e) {

            // Empty output buffers
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            echo $e->getMessage() . ' in ' . $e->getFile() . ' (line ' . $e->getLine() . ').';
        }

        exit(1);
    }
}

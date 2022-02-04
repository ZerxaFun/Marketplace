<?php

namespace Core\Services\Session\Driver;

use Core\Services\Session\SessionDriver;
use Core\Services\Session\SessionInterface;


class Native extends SessionDriver implements SessionInterface
{

    /**
     * @var array - флэш-данные, чтобы сохранить для следующего запроса.
     */
    protected array $keep = [];

	/**
	 * @return bool
	 */
    final public function initialize(): bool
	{
        # Начало сеанса, если заголовки еще не были отправлены.
        if (!headers_sent()) {
            session_start();
        }

        # Инициализируйте основной массив сессии, если он не был установлен.
        if (!isset($_SESSION[$this->key])) {
            $_SESSION[$this->key] = [];
        }

        # Выполнить сеанс с ключом данных флэш.
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }

        # Успешно инициализирован.
        return true;
    }

	/**
	 * @return bool
	 */
    final public function finalize(): bool
	{
        # Удалить флэш-данные, которые не сохраняются.
        foreach (array_keys($this->kept()) as $name) {
            if (!in_array($name, $this->keep, true)) {
                unset($_SESSION['flash'][$name]);
            }

        }

        # Успешно завершено.
        return true;
    }

	/**
	 * @param string $name
	 * @param mixed  $data
	 *
	 * @return SessionDriver
	 */
    final public function put(string $name, mixed $data): SessionDriver
	{
		# Вставить данные сеанса.
        $_SESSION[$this->key][$name] = $data;

        # Возвращаем экземпляр класса.
        return $this;
    }

	/**
	 * @param string $name
	 * @return mixed
	 */
    final public function get(string $name): mixed
    {
        return $_SESSION[$this->key][$name] ?? false;
    }

	/**
	 * @param string $name
	 * @return bool
	 */
    final public function has(string $name): bool
	{
        return isset($_SESSION[$this->key][$name]);
    }

	/**
	 * @param string $name
	 * @return SessionDriver
	 */
    final public function forget(string $name): SessionDriver
	{
        if ($this->has($name)) {
            unset($_SESSION[$this->key][$name]);
        }

        return $this;
    }

	/**
	 * @return SessionDriver
	 */
    final public function flush(): SessionDriver
	{
        $_SESSION[$this->key] = [];

        return $this;
    }

	/**
	 * @return array
	 */
    final public function all(): array
	{
        return $_SESSION[$this->key] ?? [];
    }

	/**
	 * @param string $name
	 * @param array|null $data
	 * @return mixed
	 */
    final public function flash(string $name, array $data = null): mixed
    {
        # Если данные нулевые, вернуть то, что сохранено
        if ($data === null) {
            return $_SESSION['flash'][$name] ?? false;
        }

        # Сохраняем это для следующего запроса.
        $this->keep($name);

        # Хранить данные.
            return $_SESSION['flash'][$name] = $data;
    }

	/**
	 * @param string $name
	 * @return SessionDriver
	 */
    final public function keep(string $name): SessionDriver
	{
        # Сохранить в массиве keep, если его там еще нет.
        if (!in_array($name, $this->keep, true)) {
            $this->keep[] = $name;
        }

        # Возвращаем объект сеанса.
        return $this;
    }

	/**
	 * @return array
	 */
    public function kept(): array
    {
        return $this->keep;
    }

}

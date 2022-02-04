<?php


namespace Modules\Frontend\Model;


use Core\Services\Orm\Model;
use Core\Services\Orm\Query;

class UserModel extends Model
{
    # Выбор таблицы базы данных
    protected static string $table = 'accounts';

    public function getUserByParams( $login,  $password)
    {
        return Query::table(static::$table)
            ->select()
            ->where('name', '=', $login)
            ->where('password', '=', $password)
            ->first();
    }
}
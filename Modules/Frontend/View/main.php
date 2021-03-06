<?php

use Core\Define;
use Core\Services\Auth\Auth;
use Core\Services\Client\Client;
use Core\Services\Path\Path;
use Core\Services\Template\Theme\Theme;

$themes = Path::theme('Frontend/default');
$tpl = new Theme;
$tpl->dir = $themes;
$tpl->load_template('layout.mjt');

# Структура шаблона
$tpl->set('{BASE_URL}', Path::base());
$tpl->set('{THEME}', '/Content/Themes/Frontend/default/');
$tpl->set('{lang}', Client::language());
$tpl->set('{content}', Layout::content());

$tpl->set('{header}', $tpl->sub_load_template('header.mjt'));
$tpl->set('{footer}', $tpl->sub_load_template('footer.mjt'));

$tpl->set('{title}', $data['title']);
$tpl->set('{description}', $data['description']);
$tpl->set('{keywords}', $data['keywords']);



/*
 * Проверка, авторизирован ли пользователь.
 *
 * Если $auth::authorized() возвращает true, то удаляем блок [guest] *** [/guest] и его сожержимое, взамен
 * обрабатываем блок [user] *** [/user]
 */
$auth = new Auth();

if( $auth::authorized() === true ) {
    $tpl->set_block( "'\\[guest\\](.+?)\\[/guest\\]'si", "" );
    $tpl->set( '[user]', '' );
    $tpl->set( '[/user]', '' );

} elseif ($auth::authorized() === false) {
    $tpl->set_block ( "'\\[user\\](.*?)\\[/user\\]'si", "" );
    $tpl->set("[guest]", "");
    $tpl->set("[/guest]", "");
}

# Вывод результата
$tpl->result('main');

# Очистка переменных
$tpl->global_clear();

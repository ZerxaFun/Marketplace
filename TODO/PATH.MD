Переписать сервис Path, изменение на FileSystem.
Функционал.

#section 1
Вывод следующих папков в абслолютном виде:
1. Cache
2. Content
3. Content\Themes
4. Content\Themes\{activeTheme}
5. Content\Plugins
6. Modules
7. Public
8. Uploads
9. Logs
10. Logs/User
11. Logs/Exception
12. Logs/Runner

#section 2
Логика:

Отдаем данные в string

Если папки нет, создаем её с записью в лог.

Если нет папки лога, то получаем Exception и перезапускаем функцию записи в лог.


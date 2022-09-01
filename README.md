# Скрипт вычисления тяжелых HTTP-запросов
Выводит топ 10 самых тяжелых запросов по логу nginx в указанном формате.
```
log_format main
    '$remote_addr $http_host $remote_user [$time_local] '
    '"$request" $status $bytes_sent '
    '"$http_referer" "$http_user_agent" $upstream_response_time $request_time';
```

Оптимизирован для использования с большим объемом логов.

## Использование:
**Примечание**: имя файла передавать необязательно. По умолчанию используется obfuscated.log
Если указан второй параметр, выборка будет происходить по upstream_response_time.
```
php script.php <Имя файла> <upstream>
```
# Скрипт вывода 10 самых длительных запросов
Выводит 10 самых длительных запросов в среднем, по логу nginx в указанном формате.
```
log_format main
    '$remote_addr $http_host $remote_user [$time_local] '
    '"$request" $status $bytes_sent '
    '"$http_referer" "$http_user_agent" $upstream_response_time $request_time';
```

Версия скрипта appm.php считает просто максимальное значение.

## Использование:
**Примечание**: имя файла передавать необязательно. По умолчанию используется obfuscated.log
Если указан второй параметр, выборка будет происходить по upstream_response_time.
```
php app.php <Имя файла> <upstream>
```
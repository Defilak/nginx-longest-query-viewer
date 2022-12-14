<?php

// Пытался уложится в 100мб, не вышло(
ini_set('memory_limit', '-1');

$filename = $argv[1] ?? 'obfuscated.log';
$upstream = $argv[2] ?? false;

$fp = fopen($filename, 'r');
if (!$fp) {
    exit("Не могу открыть файл $filename!");
}

//я устал
const REGEX = '/(?:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\s(.+?)\s-\s(?:.+\"GET|POST|PUT|DELETE)\s(.*?(?=\ HT|HTTP\/\d\.\d|\d")).+\s(\d+(?:\.\d+)?)\s(\d+(?:\.\d+)?)/';

//нужна только для вывода ошибок.
$count = 0;
try {
    $start_time =  hrtime(true);

    $result = [];
    while (!feof($fp)) {
        $line = fgets($fp);
        if (empty($line)) continue;

        if (!preg_match(REGEX, $line, $matches)) {
            throw new Exception('Parsing error');
        }

        [, $url, $query, $upstream_response_time, $response_time] = $matches;

        $url_query = $url.$query;
        if (!isset($result[$url_query])) {
            $result[$url_query] = [];
        }
        $result[$url_query][] = $upstream ? $upstream_response_time : $response_time;
        $count++;
    }

    //Считаю среднее
    foreach ($result as $key => $entry) {
        $result[$key] = array_sum($entry) / count($entry);
    }

    arsort($result);
    $result = array_slice($result, 0, 10);

    print_result_middle($result, $start_time);
} catch (Throwable $ex) {
    print "\e[31m" . $ex->getMessage() . "\n";
    print "Error on line №$count: \n$line\n\e[0m";
} finally {
    fclose($fp);
}

function print_result_middle($result, $start_time)
{
    $delta = (hrtime(true) - $start_time) / 1_000_000_000;
    print "Время: {$delta}s\n";
    print "10 самых длительных запросов (в среднем): \n\n";
    foreach ($result as $url => $time) {
        $out = strlen($url) > 100 ? substr($url, 0, 100) . "..." : $url;
        print "{$time}c. - $out\n";
    }
}

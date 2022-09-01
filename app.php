<?php

$filename = $argv[1] ?? 'obfuscated.log';
$upstream = $argv[2] ?? false;

$fp = fopen($filename, 'r');
if (!$fp) {
    exit("Не могу открыть файл $filename!");
}

//я устал
const REGEX = '/(?:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\s(.+?)\s-\s(?:.+\"GET|POST|PUT|DELETE)\s(.*?(?=\ HT|HTTP\/\d\.\d|\d")).+\s(\d+(?:\.\d+)?)\s(\d+(?:\.\d+)?)/';

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

        update_stats($result, $url . $query, $upstream ? $upstream_response_time : $response_time);
        $count++;
    }

    $delta = (hrtime(true) - $start_time) / 1_000_000_000;
    print "Время: {$delta}s\n";
    print_result($result);
} catch (Throwable $ex) {
    print "\e[31m" . $ex->getMessage() . "\n";
    print "Error in line №$count: \n$line\n\e[0m";
} finally {
    fclose($fp);
}

function update_stats(&$result, $url, $time)
{
    if (isset($result[$url]) && $result[$url] > $time)
        return;
    $result[$url] = $time;
    arsort($result);
    if (count($result) > 10) {
        array_pop($result);
    }
}

function print_result($result)
{
    print "10 самых длительных запросов: \n\n";
    foreach ($result as $url => $time) {
        $out = strlen($url) > 100 ? substr($url, 0, 100) . "..." : $url;
        print "{$time}c. - $out\n";
    }
}

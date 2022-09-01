<?php

$filename = $argv[1] ?? 'obfuscated.log';
$upstream = $argv[2] ?? false;

$fp = fopen($filename, 'r');
if (!$fp) {
    exit("Не могу открыть файл $filename!");
}

//я устал
const REGEX = '/(?:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\s(.+?)\s-\s(?:.+\"GET|POST|PUT|DELETE)\s(.*?(?=\ HT|HTTP\/\d\.\d|\d")).+(\d+(?:\.\d+)?)\s(\d+(?:\.\d+)?)/';

$count = 0;
try {
    $result = [];
    while (!feof($fp)) {
        $line = fgets($fp);
        if (empty($line)) continue;

        if (!preg_match(REGEX, $line, $matches)) {
            throw new Exception('Parsing error');
        }

        //При желании можно и по upstream_response_time
        [, $url, $query, $upstream_response_time, $response_time] = $matches;

        //$result[] = $upstream_response_time;
        update_stats($result, $url . $query, $upstream ? $upstream_response_time : $response_time);
        $count++;
    }

    //rsort($result);
    //file_put_contents('./a.txt', var_export(array_slice($result, count($result) - 10000, 1000), true));

    fclose($fp);
    print_result($result);
} catch (Throwable $ex) {
    print "\e[31m" . $ex->getMessage() . "\n";
    print "Error in line №$count: \n$line\n\e[0m";
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
    print "Топ 10 тяжелых запросов: \n\n";
    foreach ($result as $url => $time) {
        $out = strlen($url) > 100 ? substr($url, 0, 100) . "..." : $url;
        print "{$time}c. - $out\n";
    }
}

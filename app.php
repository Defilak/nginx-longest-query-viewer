<?php

$filename = $argv[1] ?? 'obfuscated.log';
$res = fopen($filename, 'r');
if (!$res) {
    exit("Не могу открыть файл $filename!");
}

//$regex = '/^(?:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\s((.+?\s)-\s(?:.+\"GET|POST|PUT|DELETE)\s(.*?(?=\ HTTP\/\d\.\d"))).+(\d+\.\d{0,3})\s(\d+\.\d{0,3})$/';
//$regex = '/^(?:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\s((.+?\s)-\s(?:.+\"GET|POST|PUT|DELETE)\s(.*?(?=\ HTTP\/\d\.\d"))).+(\d+(?:\.\d+)?)\s(\d+(?:\.\d+)?)$/';
//$regex = '/(?:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\s((.+?\s)-\s(?:.+\"GET|POST|PUT|DELETE)\s(.*?(?=\ HTTP\/\d\.\d|\d"))).+(\d+(?:\.\d+)?)\s(\d+(?:\.\d+)?)/';
$regex =   '/(?:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\s(.+?)\s-\s(?:.+\"GET|POST|PUT|DELETE)\s(.*?(?=\ HT|HTTP\/\d\.\d|\d")).+(\d+(?:\.\d+)?)\s(\d+(?:\.\d+)?)/';
//я устал

$result = [];

try {
    $count = 0;
    while (!feof($res)) {
        $line = fgets($res);
        if (empty($line)) continue;

        if (!preg_match($regex, $line, $matches) && check_correction($matches)) {
            throw new Exception('Parsing error');
        }


        [, $url, $query, $_upstream_response_time, $response_time] = $matches;

        save_stats($result, $url . $query, $response_time);
    }

    fclose($res);
    print_result($result);
} catch (Throwable $ex) {
    print "\e[31m" . $ex->getMessage() . "\n";
    print "Line: $line\n\e[0m";
}

function check_correction($matches)
{
    return count($matches) < 4 || count(array_filter($matches, fn($val) => empty($val))) > 0;
}

function save_stats(&$result, $url, $time)
{
    if (count($result) >= 10)
        array_shift($result);
    $result[$url] = $time;
    asort($result);
}

function print_result($result)
{
    $reversed = array_reverse($result);
    print "Топ 10 тяжелых запросов: \n";
    foreach ($reversed as $url => $time) {
        $out = strlen($url) > 100 ? substr($url, 0, 100) . "..." : $url;
        print "{$time}c. - $out\n";
    }
}

exit;

//file_put_contents('./result.txt', var_export($result, true));

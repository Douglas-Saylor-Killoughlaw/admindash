<?php

require_once 'init.php';

$moment = date('[d/M/Y:H:i:s', strtotime('10 min ago'));
// trim last second to avoid case when sed cannot find single entry in log - which is much less likely for regex
// 12:45:4  than for 12:45:49
$moment = substr($moment, 0, -1); 


$cmd = "sed '1,/". preg_quote($moment, "/") ."/d' /var/www/qwintry/data/logs/qwintry.com_access.log | cut -f 1 -d ' '|sort|uniq -c|sort -nr|more | head -n 50 2>&1"; // 'sed -n "/^$'. preg_quote($moment) .'/,\$p" /var/www/qwintry/data/logs/qwintry.com_access.log 2>&1';

//echo $cmd;

$lines = [];
exec($cmd, $lines);

$out = [];

foreach ($lines as $line) {
    $out[] = explode(' ', trim($line));
}

header('Content-Type: application/json');
echo json_encode($out);
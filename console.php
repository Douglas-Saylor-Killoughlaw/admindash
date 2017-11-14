<?php

require_once 'init.php';


$start_date = $_REQUEST['start_date'] . ' ' . $_REQUEST['start_time'];
$end_date = $_REQUEST['end_date'] . ' ' . $_REQUEST['end_time'];
$ip = $_REQUEST['ip'];
$group_by = empty($_REQUEST['group_by']) ? '' : $_REQUEST['group_by'];
$exclude = empty($_REQUEST['exclude']) ? '' : $_REQUEST['exclude'];

while (@ ob_end_flush()) {
                ;
} // end all output buffers if any

echo '<pre>';

// @TODO: better security for escaping!
$ip = escapeshellcmd($ip);
//$exclude = escapeshellcmd($exclude);


if (!empty($exclude)) {
    $exclude_op = " | grep -E -v ". escapeshellarg($exclude) ." ";
} else {
    $exclude_op = '';
}







if ($group_by == 'path') {
    $group_by_op = "| cut -f 2 -d '\"' |cut -f 2 -d ' '|sort|uniq -c|sort -nr|more";
} elseif ($group_by == 'useragent') {
    $group_by_op = "| cut -f 6 -d '\"' |sort|uniq -c|sort -nr|more";
} elseif ($group_by == 'ip') {
    $group_by_op = "| awk '{print $1}' | sort -n | uniq -c | sort -nr | head -20";    
} else {
    $group_by_op = ' ';
}

$cmd = 'cd '. WEBSERVER_LOGS_PATH .' 2>&1; find -newermt '. escapeshellarg($start_date) .' -not -newermt '. escapeshellarg($end_date) .' -exec zgrep -m 5000 '. escapeshellarg($ip) .' \{\} \; '. 
$exclude_op . $group_by_op .' 2>&1';
echo '<b>Executing</b> ' . $cmd . "\n\n";

if ($group_by == 'ip') {
    $drupal_users = website_users();


    $cf_list = cflist();
    $cf_rules = [];

    foreach($cf_list as $cf_rule) {
        if ($cf_rule['configuration']['target'] == 'ip') {
            $cf_rules[$cf_rule['configuration']['value']] = [
                'mode' => $cf_rule['mode'],
                'notes' => $cf_rule['notes'],
                'created_on' => $cf_rule['created_on']
            ];
        }
    }

}


$search_ip_url = UTILITY_URL . "/?start_date=". date('Y-m-d', strtotime('-1 day')) ."&start_time=00%3A00&end_date=". date('Y-m-d') ."&end_time=23%3A59&ip={ip}&exclude=";

if (($fp = popen($cmd, "r"))) {
    while (!feof($fp)) {
        $f = fread($fp, 4096);

        if ($group_by != 'ip') {
            echo $f;
        } else {
            $lines = explode("\n", $f);
            $new_out = '';

            foreach ($lines as $n => $l) {
                $ln = explode(" ", trim($l));

                $ip = [
                    'count' => $ln[0],
                    'ip' => $ln[1],
                    'addr' => ip2city($ln[1])
                ];

                if (!empty($ln[1])) {
                    $cf_info = ' ';

                    $drupal_username = ' ';

                    if (!empty($drupal_users[$ip['ip']])) {
                        $drupal_username = '<span style="background-color:#CCC;color:#000;padding: 0 3px 0 3px">' . $drupal_users[$ip['ip']]['name'] . '</span>';
                    }

                    if (!empty($cf_rules[$ip['ip']])) {
                        $cf_rule = $cf_rules[$ip['ip']];
                        if ($cf_rule['mode'] == 'whitelist') {
                            $cf_info = '<span style="background-color:#CCC;color:#000;padding: 0 3px 0 3px">' . ($cf_rule['mode'] . ' ' . $cf_rule['notes']) . '</span>';
                        } else {
                            $cf_info = '<span style="background-color:#900;color:#FFF;padding: 0 3px 0 3px">' . ($cf_rule['mode'] . ' ' . $cf_rule['notes']) . '</span>';
                        }
                    } 


                    $link = '<a  target="_parent" href="'. strtr($search_ip_url, ['{ip}' => $ip['ip']]) .'">Search</a> ' . $cf_info . ' ' . $drupal_username;
                    $new_out .= $ip['count'] . ' ' . $ip['ip'] . ' <span style="color:gray">' . $ip['addr'] . '</span>  '. $link . "\n";
                }
            }

            echo $new_out;
        }

        



        flush(); // you have to flush buffer
    }
    fclose($fp);
}
//echo shell_exec("sudo /root/scripts/deploy_logistics_prod.sh 2>&1");
echo '</pre>';

<?php

require_once 'init.php';
require_once 'topnav.php';



while (@ ob_end_flush()) {
                ;
} // end all output buffers if any

echo '<pre>';



$cmd = "cd ". WEBSERVER_LOGS_PATH . " 2>&1; tail -n 15000 ". WEBSERVER_RECENT_LOG_FILES ." |cut -f 1 -d ' '|sort|uniq -c|sort -nr|more | head -n 50";
echo '<b>Executing</b> ' . $cmd . "\n\n";
flush();

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




$search_ip_url = UTILITY_URL . "/?start_date=". date('Y-m-d', strtotime('-1 day')) ."&start_time=00%3A00&end_date=". date('Y-m-d') ."&end_time=23%3A59&ip={ip}&exclude=";

if (($fp = popen($cmd, "r"))) {
    while (!feof($fp)) {
        $f = fread($fp, 4096); 
        $lines = explode("\n", $f);

        $new_out = '';
        foreach ($lines as $n => $l) {

            if ($n < 20) {
                // get ip location for top results
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


                    $link = '<a href="'. strtr($search_ip_url, ['{ip}' => $ip['ip']]) .'">Search</a> ' . $cf_info . ' ' . $drupal_username;
                    $new_out .= $ip['count'] . ' ' . $ip['ip'] . ' <span style="color:gray">' . $ip['addr'] . '</span>  '. $link . "\n";
                }
            } else {
                $new_out .= $l . "\n";
            }
            
        }
        echo $new_out;
        flush(); // you have to flush buffer
    }
    fclose($fp);
}

echo '</pre>';



?>
<?php require_once 'footer.php' ?>
<?php

require_once 'init.php';
require_once 'topnav.php';




while (@ ob_end_flush()) {
                ;
} // end all output buffers if any

echo '<pre>';



$cmd = "netstat -n|grep :80|cut -c 45-|cut -f 1 -d ':'|sort|uniq -c|sort -nr|more";
echo '<b>Executing</b> ' . $cmd . "\n\n";
flush();


$search_ip_url = UTILITY_URL . "/?start_date=". date('Y-m-d', strtotime('-1 day')) ."&start_time=00%3A00&end_date=". date('Y-m-d') ."&end_time=23%3A59&ip={ip}&exclude=";

if (($fp = popen($cmd, "r"))) {
    while (!feof($fp)) {
        $f = fread($fp, 4096); 
        $lines = explode("\n", $f);

        $new_out = '';
        foreach ($lines as $n => $l) {

            if ($n < 7) {
                // get ip location for top results
                $ln = explode(" ", trim($l));
                if (!empty($ln[1])) {
                    $link = '<a href="'. strtr($search_ip_url, ['{ip}' => $ln[1]]) .'">Search</a>';
                    $new_out .= $l . ' <span style="color:gray">' . ip2city($ln[1]) . '</span>  '. $link . "\n";
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
//echo shell_exec("sudo /root/scripts/deploy_logistics_prod.sh 2>&1");
echo '</pre>';


?>
<?php require_once 'footer.php' ?>
<?php

require_once 'init.php';


class Colors {
    private $foreground_colors = array();
    private $background_colors = array();

    public function __construct() {
        // Set up shell colors
        $this->foreground_colors['black'] = '0;30';
        $this->foreground_colors['dark_gray'] = '1;30';
        $this->foreground_colors['blue'] = '0;34';
        $this->foreground_colors['light_blue'] = '1;34';
        $this->foreground_colors['green'] = '0;32';
        $this->foreground_colors['light_green'] = '1;32';
        $this->foreground_colors['cyan'] = '0;36';
        $this->foreground_colors['light_cyan'] = '1;36';
        $this->foreground_colors['red'] = '0;31';
        $this->foreground_colors['light_red'] = '1;31';
        $this->foreground_colors['purple'] = '0;35';
        $this->foreground_colors['light_purple'] = '1;35';
        $this->foreground_colors['brown'] = '0;33';
        $this->foreground_colors['yellow'] = '1;33';
        $this->foreground_colors['light_gray'] = '0;37';
        $this->foreground_colors['white'] = '1;37';

        $this->background_colors['black'] = '40';
        $this->background_colors['red'] = '41';
        $this->background_colors['green'] = '42';
        $this->background_colors['yellow'] = '43';
        $this->background_colors['blue'] = '44';
        $this->background_colors['magenta'] = '45';
        $this->background_colors['cyan'] = '46';
        $this->background_colors['light_gray'] = '47';
    }

    // Returns colored string
    public function getColoredString($string, $foreground_color = null, $background_color = null) {
        $colored_string = "";

        // Check if given foreground color found
        if (isset($this->foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
        }
        // Check if given background color found
        if (isset($this->background_colors[$background_color])) {
            $colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
        }

        // Add string and end coloring
        $colored_string .=  $string . "\033[0m";

        return $colored_string;
    }

    public function getDangerString($string) {
        return $this->getColoredString($string, 'red');
    }

    public function getSuccessString($string) {
        return $this->getColoredString($string, 'green');
    }

    // Returns all foreground color names
    public function getForegroundColors() {
        return array_keys($this->foreground_colors);
    }

    // Returns all background color names
    public function getBackgroundColors() {
        return array_keys($this->background_colors);
    }
}

$colored = new Colors();

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


$cmd = "cd ". WEBSERVER_LOGS_PATH ." 2>&1; tail -n 10000 ". WEBSERVER_RECENT_LOG_FILES . "|cut -f 1 -d ' '|sort|uniq -c|sort -nr|more | head -n 50";
echo '<b>Executing</b> ' . $cmd . "\n\n";
//flush();



$ips = [];

if (($fp = popen($cmd, "r"))) {
    while (!feof($fp)) {
        $f = fread($fp, 4096); 
        $lines = explode("\n", $f);

        $new_out = '';
        foreach ($lines as $n => $l) {

            if ($n < 20) {
                // get ip location for top results
                $ln = explode(" ", trim($l));
                if (!empty($ln[1])) {

                    $ip = [
                        'count' => $ln[0],
                        'ip' => $ln[1],
                        'addr' => ip2city($ln[1])
                    ];

                    $cf_info = '';
                    if (!empty($cf_rules[$ip['ip']])) {
                        $cf_rule = $cf_rules[$ip['ip']];
                        if ($cf_rule['mode'] == 'whitelist') {
                            $cf_info = $colored->getColoredString($cf_rule['mode'] . ' ' . $cf_rule['notes'], 'black', 'light_gray');
                        } else {
                            $cf_info = $colored->getColoredString($cf_rule['mode'] . ' ' . $cf_rule['notes'], 'white', 'red');
                        }
                    } 
                    
                    $new_out .= $l . ' ' . $ip['addr'] . ' '. $cf_info . "\n";
                    $ips[] = $ip;
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


$handle = fopen ("php://stdin","r");

foreach ($ips as $ip) {
    $ip_human = $ip['ip'] . ' ('. $ip['addr'] . ', ' . $ip['count'] .')';

    if (!empty($cf_rules[$ip['addr']])) {
        $cf_rule = $cf_rules[$ip['addr']];
        echo "Skipping " . $ip_human . ' since it is already in CF ('. $cf_rule['mode'] .')';
        continue;
    }

    $notes = 'Ban '. date('Y-m-d H:i:s') .' by CLI ';

    echo "Ban " . $ip_human . '? ';
    
    $line = fgets($handle);
    if(trim($line) != 'y'){
        echo "skipping $ip_human \n";
    } else {
        echo "banning $ip_human ... ";
        $result = cfban($ip['ip'], $notes, 'challenge');
        echo ($result ? $colored->getSuccessString('SUCCESS') : $colored->getDangerString('FAIL')) . "\n";
    }
    
}

fclose($handle);
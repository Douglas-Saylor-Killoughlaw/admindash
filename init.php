<?php

require_once 'config.php';

$web_access_allowed = !empty($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], $allowed_ips);
$cli_access_allowed = php_sapi_name() == 'cli';

if (!$web_access_allowed && !$cli_access_allowed) {
    exit('access denied');
}

require_once 'cache.class.php';

$cache = new Cache();
$cache->eraseExpired();

function ip2city($ip) {
    require_once 'geoip/SxGeo.php';
    $SxGeo = new SxGeo(__DIR__ . '/geoip/cities.dat');
    $r = $SxGeo->get($ip);
    return $r['country']['iso'] . ',' . $r['city']['name_en'];
}

function l($text, $href) {
    return '<a href="'. $href .'">' . $text . '</a>';
}







function cfban($ipaddr, $notes = '', $type = 'block'){
    $cfheaders = array(
        'Content-Type: application/json',
        'X-Auth-Email: ' . CF_ACCOUNT_EMAIL,
        'X-Auth-Key: ' . CF_AUTH_KEY
    );
    $data = array(
        'mode' => $type,
        'configuration' => array('target' => 'ip', 'value' => $ipaddr),
        'notes' => $notes
    );
    $json = json_encode($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $cfheaders);
    curl_setopt($ch, CURLOPT_URL, 'https://api.cloudflare.com/v4/zones/'. CF_ZONE_HASH .'/firewall/access_rules/rules');
    //curl_setopt($ch, CURLOPT_URL, 'https://api.cloudflare.com/client/v4/user/firewall/access_rules/rules');
    $return = curl_exec($ch);
    curl_close($ch);

    
    if ($return === false){
        throw new \Exception("Ban failed due to unknown reasons!");
        return false;
    }else{
        $return = json_decode($return,true);
        if(isset($return['success']) && $return['success'] == true){
            $cache = new Cache();
            $cache->erase(CACHE_CF_LIST);
            return $return['result']['id'];
        }else{
            throw new \Exception(print_r($return['errors'], 1));
            return false;
        }
    }
}

function cfunban($block_rule_id){
    $cfheaders = array(
        'Content-Type: application/json',
        'X-Auth-Email: ' . CF_ACCOUNT_EMAIL,
        'X-Auth-Key: ' . CF_AUTH_KEY
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $cfheaders);
    curl_setopt($ch, CURLOPT_URL, 'https://api.cloudflare.com/client/v4/zones/'. CF_ZONE_HASH .'/firewall/access_rules/rules/'. $block_rule_id);
    $return = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);


    curl_close($ch);

    if ($httpcode > 250 && empty($return)) {
        
        throw new \Exception("Cloudflare returned http code " . $httpcode, 1);
    }

    if ($return === false){
        return false;
    }else{
        $return = json_decode($return,true);
        if(isset($return['success']) && $return['success'] == true){
            $cache = new Cache();
            $cache->erase(CACHE_CF_LIST);
            return $return['result']['id'];
        }else{
            throw new \Exception(print_r($return, 1));
            return false;
        }
    }
}


function cflist(){

    $cache = new Cache();

    if ($cflist = $cache->retrieve(CACHE_CF_LIST)) {
        return $cflist;
    }

    $cfheaders = array(
        'Content-Type: application/json',
        'X-Auth-Email: ' . CF_ACCOUNT_EMAIL,
        'X-Auth-Key: ' . CF_AUTH_KEY
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $cfheaders);
    curl_setopt($ch, CURLOPT_URL, 'https://api.cloudflare.com/v4/zones/'. CF_ZONE_HASH .'/firewall/access_rules/rules/?page=1&per_page=200&order=mode');
    //curl_setopt($ch, CURLOPT_URL, 'https://api.cloudflare.com/client/v4/user/firewall/access_rules/rules/');
    $return = curl_exec($ch);
    curl_close($ch);
    if ($return === false){
        return false;
    }else{
        $return = json_decode($return,true);
        if(isset($return['success']) && $return['success'] == true){
            //var_dump(count($return['result']));die();
            $cache->store(CACHE_CF_LIST, $return['result'], time() + 60*60);
            return $return['result'];
        }else{
            return false;
        }
    }
}



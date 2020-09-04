<?php

//fixer.io
// http://data.fixer.io/api/latest?access_key=bd86b13c4f91a685545925857ea5eeb6&format=1

$endpoint = 'latest';
$access_key = 'bd86b13c4f91a685545925857ea5eeb6';

//1 day cache life span
$cacheTimer = 86400;

//File name for the cached conversion rates
$cacheFileName = 'cache/cachedConverter.txt';

//Check if the file name still exists

//If the file does not exists or it has been expired, we create a new one
if (!file_exists($cacheFileName) or (time() - filemtime($cacheFileName) > $cacheTimer)) {
    // Initialize CURL:
    $ch = curl_init('http://data.fixer.io/api/' . $endpoint . '?access_key=' . $access_key . '&symbols=USD,MXN,EUR,ARS,COP,CAD,CLP');
    //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Gets the data. Increases the amount that the url has been called.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $json = curl_exec($ch);
    curl_close($ch);

    //Decode JSON response:
    $exchangeRates = json_decode($json, true);
    //var_dump($json);

    //If the curl cannot get the API, we output for the console log
    if(!$exchangeRates){
        echo 'https://fixer.io/ API is unavailable. Please check the API.';
    }
    //If there is no request error in the curl or the status of the fixer.io is a OK
    else if($exchangeRates['success'] == true){
        $fileopen = fopen($cacheFileName, 'w');
        fwrite($fileopen, $json);
        fclose($fileopen);
        $cachedFile = file_get_contents($cacheFileName);
        include($cacheFileName);
    }
    //Console log the false state of the Fixer.io
    else if($exchangeRates['success'] == false){
        echo $json;
    }

    //$cachedFile = file_get_contents($cacheFileName);
}
//If it still exists or it's not expired, we get the file
else {
    $cachedFile = file_get_contents($cacheFileName);
    include($cacheFileName);
}
//include($cacheFileName);

<?php

//fixer.io
// http://data.fixer.io/api/latest?access_key=bd86b13c4f91a685545925857ea5eeb6&format=1

$endpoint = 'latest';
$access_key = 'bd86b13c4f91a685545925857ea5eeb6';

//1 day cache life span
$cacheTimer = 30;

//File name for the cached conversion rates
$cacheFileName = 'cache/cachedConverter.txt';

//icboxed
$cacheErrorFile = 'cache/errorLog.txt';

//iceboxed
function sendEmail($errorMsg)
{

    //$to = 'hipos60438@rmomail.com';
    //$subject = 'PETA error testing email';
    //$message = $errorMsg;
    //$headers = 'From:' . $to;
    //if(mail($to, $subject, $message, $headers)){
    //    echo 'Message has been sent';
    //}
    //else {
    //    echo 'Message has not been sent';
    //}

    $sendgrid_key = 'SG.FvT_Hvk6SIKUa65Kv1uPGw.rl0BUy_4H5OIgFMwgneywov_eH4MvP05l_T6n3wodIw';

    $headers = array("Authorization: Bearer $sendgrid_key", 'User-Agent: PHP',
    'Content-Type: application/json');

    $subject = 'APP-01 - Currency Conversion API Issue';

    $email ='gemini@peta.org';
    $name ="PETA bot";
    $body = $errorMsg;

    $data = array(
        'personalizations' => array (
            array (
                'to' => array (
                    array(
                        'email' => $email,
                        'name' => $name
                    )
                )
            )
                ),
        'from' => array(
            'email' => $email
        ),
        'subject' => $subject,
        'content' => array(
            array(
                'type' => 'text/html',
                'value' => $body
            )
        )
    );

    // Create the new GitHub issue
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.sendgrid.com/v3/mail/send');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_close($ch);

}
//iceboxed
function createIssues($errorMsg)
{
    //personal auth token from your github.com account.  doing this will eliminate having to use oauth everytime
    $issueTitle = 'API issue error';
    $github_personal_access_token = '';

    $headers = array("Authorization: token $github_personal_access_token", 'User-Agent: PHP');

    $json = array();
    $json['title'] = 'IGNORE: testing issue automation';
    $json['body'] = 'please ignore the issue';

    // Create the new GitHub issue
    $ch = curl_init("https://api.github.com/jdelacr/PETA/issues");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
    curl_exec($ch);
    echo curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo 'An issue has been sent in the Github';
}

//Check if the file name still exists

function errorCache($cacheErrorFile, $cacheTimer, $errorMsg){

    //Create the error log if the it does not exist and sends the issue and email
    if (!file_exists($cacheErrorFile) or (time() - filemtime($cacheErrorFile) > $cacheTimer)){
        $fileopen = fopen($cacheErrorFile, 'w');
        fwrite($fileopen, $errorMsg);
        fclose($fileopen);
        $cachedFile = file_get_contents($cacheErrorFile);

        //createIssues($errorMsg);
        sendEmail($errorMsg);

    }
}

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

    //If the curl cannot get the API, use the old file
    if (!$exchangeRates) {

        //If the file exists and the last modified has been 1 day, send an error to uptime bot
        if (file_exists($cacheFileName)){
            $cachedFile = file_get_contents($cacheFileName);
            include($cacheFileName);

            if((time() - filemtime($cacheFileName) > $cacheTimer)){
                //sending error
                http_response_code(500);
            }
            else {
                http_response_code(200);
            }
        }

        //$errorMsg = 'https://fixer.io/ API is unavailable. Please check the API. Using the last gathered currency.';
        //errorCache($cacheErrorFile,$cacheTimer,$errorMsg);
        //echo $errorMsg;


    }
    //If there is no request error in the curl or the status of the fixer.io is a OK
    else if ($exchangeRates['success'] == true) {
        $fileopen = fopen($cacheFileName, 'w');
        fwrite($fileopen, $json);
        fclose($fileopen);
        $cachedFile = file_get_contents($cacheFileName);
        include($cacheFileName);
        //unlink('cache/errorLog.txt');
    }
    //Console log the false state of the Fixer.io
    else if ($exchangeRates['success'] == false) {
        //errorCache($cacheErrorFile,$cacheTimer,$json);
        //echo $json;

        //If the file exists and the last modified has been 1 day, send an error to uptime bot
        if (file_exists($cacheFileName)){
            $cachedFile = file_get_contents($cacheFileName);
            include($cacheFileName);

            if((time() - filemtime($cacheFileName) > $cacheTimer)){
                //sending error
                http_response_code(500);
            }
            else {
                http_response_code(200);
            }
        }
    }

    //$cachedFile = file_get_contents($cacheFileName);
}
//If it still exists or it's not expired, we get the file
else {
    $cachedFile = file_get_contents($cacheFileName);
    include($cacheFileName);
}
//include($cacheFileName);

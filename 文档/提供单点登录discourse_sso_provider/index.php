<?php
require('mysql.php'); // see https://gist.github.com/paxmanchris/f5d4b94f67a8acd8cefc
$me = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
$sso_secret = 'YOUR_SSO_PROVIDER_KEY_HERE';
$discourse_url = 'http://example.com';

if(!empty($_GET) and isset($_GET['sso'])){
	
    $login = get_key('login');
    if($login){
        header("location: $me");
        die();
    }
    $sso = $_GET['sso'];
    $sig = $_GET['sig'];
    

    // validate sso
    if(hash_hmac('sha256', urldecode($sso), $sso_secret) !== $sig){
        header("HTTP/1.1 404 Not Found");
        die();
    }
    
    
    $sso = urldecode($sso);
    $query = array();
    parse_str(base64_decode($sso), $query);
    
    // verify nonce with generated nonce
    $nonce = get_key('nonce'); // pretend that get_key is a function that get a value from a database by key
    if($query['nonce'] != $nonce){
        header("HTTP/1.1 404 Not Found");
        die();
    }
    // login user
    set_key('login', $query);

    header("Access-Control-Allow-Origin: *");
    die();
}

$info = '';
// user is logged on
$login = get_key('login');
if($login){
    print "<pre>";
    print_r($login);
    $info ="if you click this a second time, you will be redirected here<br>";
}

$nonce = hash('sha512', mt_rand());
set_key('nonce', $nonce); // pretend that set_key is a function that saves key value data in a database

$payload =  base64_encode( http_build_query( array (
    'nonce' => $nonce,
    'return_sso_url' => $me
    )
) );

$request = array(
    'sso' => $payload,
    'sig' => hash_hmac('sha256', $payload, $sso_secret )
    );

$query = http_build_query($request);


print "$info 
<a href='$discourse_url/session/sso_provider?$query'>sign in with discourse</a><pre>
";


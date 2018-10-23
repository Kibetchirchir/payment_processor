<?php
/**
 * Created by PhpStorm.
 * User: langa
 * Date: 6/15/2018
 * Time: 8:37 AM
 */


file_put_contents("fb.txt",file_get_contents("php://input"));


$fb=file_get_contents("fb.txt");
$fb=json_decode($fb);

$rid=$fb->entry[0]->messaging[0]->sender->id;

print_r($rid);
$data= array(
    'recipient' => array('id' => "rid"),
    'message' => array('text' => 'Nice to meet you!'),
);

$option=array(
    'http' =>  array(

        'method' => 'POST',
        'content' => json_encode($data),
        'header' =>  'Content-Type: application/json\n',

    ),

);

$context=stream_context_create('$option');

$token="EAAI2mBNgHKsBAG4j4giqjsK6jVRGytuCLxGGcr7VC4wuLh67zodNxKITlYgjLneVNBcGXZCvRR9ZCoo442qWqhUYEYxcSkzkRcf1KKlVgRL1Oq0ScZAkg1mstZBAEQCUa3vTcAMZCAf7MTEZAlfrMQZBu399V0gSzxLgyhWwVwrO5HTRoKRuHZABQvA9GwIMtpEZD";

file_get_contents("https://graph.facebook.com/v2.6/me/messages?access_token=$token, false, $context");





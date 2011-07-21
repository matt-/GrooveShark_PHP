<?php
include("grooveshark.class.php");

$gs = new GrooveShark(array(
/* an example of the options you can pass in.
	'proxy' => "tcp://127.0.0.1:8080",
	'configDefaults' => array(
		'client'         => 'htmlshark',
		'clientRevision' => '20110606',
		'revToken'       => 'backToTheScienceLab',
		'tokenKey'       => 'bewareOfBearsharktopus'
	),*/
));

// if they change the clientRevision or revToken this will try to find it from app.js.
#$gs->getAppData();

$url = 'http://grooveshark.com/#/s/Cookies+With+A+Smile/3EXEk7?src=5';
$song = $gs->getSongByUrl($url);
#print_r($gs->getSongFromToken("3EXEk7"));
$data = $gs->getSongById($song['SongID']);

print_r($data);
<?php

	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	
	include("../grooveshark.class.php");

	$music_path = "/Users/matt/Music/gs";

	$gs = new GrooveShark();
	$url = 'http://grooveshark.com/s/1980/2JS6Dg?src=5';

	# look up the son URL then get the download URL. 
	$song = $gs->getSongByUrl($url);
	$data = $gs->getSongById($song['SongID']);

	# push the file name in the header
	$filename = "{$song['ArtistName']} - {$song['Name']}.mp3";

	if (!is_dir($music_path)) {
		mkdir($music_path, 0700, true);
	}
	$filename = str_replace(' ','\ ', $filename);
	print $filename;

#	header("Content-Disposition: attachment; filename={$filename}");
	passthru("wget -O $music_path/{$filename} {$data['url']}");


	osascript -e 'tell application "iTunes" to add POSIX file "/Users/xil/Psycho_Chicken.mp3"'

?>
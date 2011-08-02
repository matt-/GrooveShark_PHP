<?php
	include("../grooveshark.class.php");

	// remove memry limits 
	ini_set('memory_limit', -1);

	$gs = new GrooveShark();
	$playlist_id = '52412981';

	$songs = $gs->playlistGetSongs($playlist_id);

	$filename = $playlist_id . '.zip';
	$zip = new ZipArchive();
	# open or create a zip file by the playlist id number



	foreach ($songs as $song) {
		if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
		    exit("cannot create <$filename>\n");
		}
	
		$songs_info = $gs->getSongById($song['SongID']);
		$file_name = $song['Name'].".mp3";
		echo "Downloading file: {$file_name}\n";
	
		$zip->addFromString($file_name , file_get_contents($songs_info['url']));		
		get_mem();
	
		$zip->close();
	}



	function get_mem(){
		$size = memory_get_usage(true);
		$unit=array('b','kb','mb','gb','tb','pb');
		$size = @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
		echo "Mem: $size\n"; // 123 kb
	}



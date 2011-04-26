<?php
	
	include("grooveshark.class.php");
	if ($argc != 2) {
		$playlist_name = 'download';
	}
	else{ 
		$playlist_name = $argv[1];
	}
	
	$gs = new GrooveShark();
	$me = $gs->login('username', 'password');
	if($me){
		foreach($me['Playlists'] as $playlist) {
			if($playlist['Name'] == $playlist_name){
				$songs = $gs->playlistGetSongs($playlist['PlaylistID']);
				foreach($songs as $song) {
					$filename = "{$song['ArtistName']}-{$song['Name']}.mp3";	
					if(!file_exists($filename)){
						$song_data = $gs->getSongById($song['SongID']);
						$url = "http://{$song_data['ip']}/stream.php?streamKey={$song_data['streamKey']}";
						print "Downloading: $filename $url\n";
						file_put_contents($filename,file_get_contents($url));
						print "Saved File: $filename\n";
					}
					
				}
			}
		}
	}
	else{
		echo "Bad username or password.\n";
	}

?>
<?php
/**
 * class Grooveshark
 *
 * GrooveShark PHP Class for the undocumented GrooveShark API. 
 * @package GrooveShark
 * @author Matt Austin
 * @version 1.0
 **/

class GrooveShark 
{
	public $session = null;
	public $profile = Array();
	
	private $token = null;	
	private $lastRandomizer = null;
	private $communication_token = null;
	
	private $clientRevision = '20101222'; // should be a costant?
	private $uuid = '6D3BFDDD-B6D6-4E64-BA91-B63C9213BAD1';
	private $action_url = 'listen.grooveshark.com/more.php?';
	
	
	//public $client = 'widget'; // this may be needed to get the stream 
	//public $client = 'jsqueue';
	private $http_headers = array(
		'User-Agent'       => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0) Gecko/20100101 Firefox/4.0',
		'Accept'           => 'application/json, text/javascript, */*',
		'Connection'       => 'keep-alive',
		'Content-Type'     => 'application/json; charset=UTF-8',
		'X-Requested-With' => 'XMLHttpRequest',
		'Referer'          => 'http://listen.grooveshark.com/',
#		'Content-Length'   => null,
		'Cookie'           => null,
		'Host'             => 'listen.grooveshark.com',
		'Pragma'           => 'no-cache',
		'Cache-Control'    => 'no-cache'
	);
	
	// this should be replaced with the "getCountry" call
	private $country = array (
		'ID' => '65',
		'CC1' => '0',
		'CC2' => '1',
		'CC3' => '0',
		'CC4' => '0',
		'IPR' => '7854'
	);
	
	public function __construct($session_id = null) 
	{
		if(!$session_id){
			$this->getSession();
		}
	}


/** Usuer Functions **/

	/** Login (to get curent user id)
     *
     * @param string $username
     * @param string $password
     * @return bool true on success 
     */
	public function login($username, $password, $get_playlist = true) {
		$params = array(
			'username' => $username,
			'password' => $password,
			'savePassword' => 0,
		);

		$data = $this->send('authenticateUser', $params, 'htmlshark', true);
		
		if($data['authToken'] == null){
			return false;
		}
		
		if($get_playlist){
			$data['Playlists'] = $this->userGetPlaylists($data['userID']);
		}
				
		$this->profile = $data;
		return $data;
	}
	
	/** Logout
     */	
	public function logout() {
		$params = array();
		$data = $this->send('logoutUser', $params);
		return true;
	}

	/** getUserByID
     *
     * @param int $user_id
     * @return mixed  
     */
	public function getUserByID($user_id) {
		$params = array(
			'userID' => $user_id,
		);
		$data = $this->send('getUserByID', $params);
		return $data['User'];
	}

	/** userGetPlaylists
     *
     * @param int $user_id
     * @return mixed Playlist data
     */
	public function userGetPlaylists($user_id) {
		$params = array(
			'userID' => $user_id,
		);
		$data = $this->send('userGetPlaylists', $params);
		return $data['Playlists'];
	}
	
	
		/** getFavorites
	     *
	     * @param int $user_id
	     * @param string ofWhat (Albums)
	     * @return mixed Song data
	     */
		public function getFavorites($user_id, $ofWhat) {
			$params = array(
				'userID' => $user_id,
				'ofWhat' => $ofWhat,
			);
			$data = $this->send('userGetPlaylists', $params);
			return $data['Playlists'];
		}	
	
/** Search Functions **/

    /** Search 
     *
     * @param string $search
     * @param string $type (Songs, Albums
     * @return array Array of songs matching search
     */

	public function search($search, $type = 'Songs') {
		$params = array(
			'query' => $search,
			'type' => $type
		);
		$data = $this->send('getSearchResultsEx', $params);
		return $data['result'];
	}

    /** List songs in playlist
     *
     * @param int $playlist_id Playlist Id
     * @return array Array of songs 
     */
	public function playlistGetSongs($playlist_id) {
		$params = array(
			'playlistID' => $playlist_id,
		);
		$data = $this->send('playlistGetSongs', $params);
		return $data['Songs'];
	}

    /** List all songs by an artist
     *
     * @param int $artist_id Artist Id
     * @return array Array of songs 
     */	
	public function getSongsByArtist($artist_id) {
		$params = array(
			"artistID" => $artist_id,
			"isVerified" => true,
			"offset" => 0		
		);
		$data = $this->send('artistGetSongs', $params);
		if(isset($data['songs'])){
			return $data['songs'];
		}
		return array();
	}

    /** List all albums by an artist
     *
     * @param int $artist_id Artist Id
     * @return array Array of Albums 
     */
	public function getAlbumsByArtist($artist_id) {
		$songs = $this->getSongsByArtist($artist_id);
		$data = $albums = array();
		if(count($songs)){
			foreach($songs as $song) {
				$albums[$song['AlbumID']] = array(
					'AlbumName' => $song['AlbumName'],
					'AlbumID' => $song['AlbumID'],
					'CoverArtFilename' => $song['CoverArtFilename'],
					'Year' => $song['Year'],
					'ArtistName' => $song['ArtistName'],					
				);
			}
			foreach($albums as $album) {
				array_push($data, $album);
			}			
			return $data;
		}
		return array();
	}

/** Song Functions **/

	/** List all songs in an album
     *
     * @param int $album_id Album Id
     * @return array Array of Albums 
     */
	public function getSongsByAlbum($album_id) {
		$params = array(
			'albumID' => $album_id,
			'isVerified' => false,
			'offset' => 0,
		);
		$data = $this->send('albumGetSongs', $params);
		if(isset($data['songs'])){
			return $data['songs'];
		}
		return array();
	}

	/** Get Song (stream) data by id
     *
     * @param int $artist_id Artist Id
     * @return mixed Song Data
     */
	public function getSongById($song_id) {
		$params = array(
			'songID' => $song_id,
			'mobile' => 'false',
			'prefetch' => 'false',
			'country' => $this->country,		
		);
		return $this->send('getStreamKeyFromSongIDEx', $params, 'jsqueue');
	}

	/** Get song by a url 
     *
     * @param string $url
     * @return mixed Song Data
     */	
	public function getSongByUrl($url){
		// parse the "token" form the url
		//$url = 'http://listen.grooveshark.com/#/s/Read+My+Mind+album+Version+/2zLI60?src=5';
		if(preg_match('/\w+(?=\?)/', $url, $matches)){
			$token = $matches[0];
			$params = array(
				'token' => $token,
				'country' => $this->country,		
			);
			return $this->send('getSongFromToken', $params);				
		}
		else{
			return false;
		}		
	}

/** internal Functions **/

	private function getSession(){
		$this->session = $this->send('initiateSession'); 		
	
		if($this->session !== null){
			$params = array(
				'secretKey' => md5($this->session)
			);
			$this->communication_token = $this->send('getCommunicationToken', $params); 
		}
		else{
			return false;
		}
	
		//return ($this->session !== null);
	}

	private function makeNewRandomizer(){
		// make sure we never send two random tokens in a row
		$rand = sprintf("%06x",mt_rand(0,0xffffff));
		if($rand !== $this->lastRandomizer){
			$this->lastRandomizer = $rand;
			return $rand;
		}else{
			return $this->makeNewRandomizer();
		}
	}

	public function send($method, $params = null, $client = 'htmlshark', $secure = false){
		$query = array(
			'header' => array(
				'client' => $client,
				'clientRevision' => $this->clientRevision,
				'privacy' => 0,
				'country' => $this->country,
				'uuid' => $this->uuid
			),
			'method' => $method
		);

		// add parameters if they are passed
		if( $params !== null ) {
			$query['parameters'] = $params;
		}
				
		// set the session id and token if we have it
		if($this->session !== null){
			$http_headers['Cookie'] = 'PHPSESSID=' . $this->session;
			$query['header']['session'] = $this->session;

			// unless we dont have a token make a new random hash with the token
			// and add it to the header.
			if(($this->communication_token !== null) && !array_key_exists('token', $query['header'])){
				$lastRandomizer = $this->makeNewRandomizer();
				$query['header']['token'] = $lastRandomizer . sha1(
					sprintf("%s:%s:quitStealinMahShit:%s", $method, $this->communication_token, $lastRandomizer)
				);
			}
		}

		// build the rest of the http headers
		$protocol = ($secure) ? 'https://':'http://';
		$url = $protocol . $this->action_url . $method;
		$content = json_encode($query);
		$data = $this->http_send($url, $content);
		
		$result = json_decode($data, true);
		
		if(isset($result['fault'])){
			throw new Exception($result['fault']['message']);
		}
		
		return $result['result'];
	}
	
	private function http_send($url, $content = null){
		// TODO: clean this up and move it to a class varible 
		// Prepare the HTTP options
		$options = array(
			'http' => array(
				'method' => 'POST',
				'header' => array('Content-Length' => strlen($content)),
				'content' => $content,
			)
		);

		foreach($this->http_headers as $key => $value) {
			$options['http']['header'] .= $key . ": " . $value . "\r\n";
		}
		
		$context = stream_context_create($options);
		$data = file_get_contents($url, false, $context); 	
		return $data; 
	}

}

?>
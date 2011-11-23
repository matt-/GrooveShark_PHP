<?php
#echo exec("osascript input.scpt");

echo applePrompt("this is a test");
#	display dialog "'.$message.'" default answer "'.$default.'"

#run_osascript('display dialog "Download a" buttons {"Song", "Playlist"} default button 2');

function applePrompt($message, $default = ""){
	run_osascript("display dialog \"{$message}\" default answer \"{$default}\"");
}

function run_osascript($code){
	// use this wrapper to solve "execution error: No user interaction allowed. (-1713)"
	$output = exec("osascript -e 'tell application \"AppleScript Runner\"\n{$code}\nend tell'");
	echo $output;	
}

function x_run_osascript($code){
	// use this wrapper to solve "execution error: No user interaction allowed. (-1713)"
	$output = exec('osascript <<-EOF
		tell application "AppleScript Runner"
			'.$code.'		
		end tell
	EOF');
	echo $output;	
}

?>
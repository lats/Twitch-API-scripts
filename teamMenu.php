<?php
//everyone loves curl. This is more of a time saver than anything else.
function curl($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}
//Make the API calls to Twitch, and decode them appropriately.
$api = 'http://api.twitch.tv/api/team/juicegaming/all_channels.json';
$kraken = 'https://api.twitch.tv/kraken/teams/juicegaming';
$team_api = curl($api);
$team_kraken = curl($kraken);
$json_api = json_decode($team_api, true);
$json_kraken = json_decode($team_kraken, true);
//your typical error checks
if ($json_api == NULL) {
	echo "<div class='error'>Could not retrieve team information, please try again in a minute</div>";
	die();
	}
if ($json_kraken == NULL) {
	echo "<div class='error'>Could not retrieve team information, please try again in a minute</div>";
	die();
	}
if ($json_api['error'] !=NULL) {
	echo "<div class='error'>" . $json_api['error'] . "</div>";
	die();
}
if ($json_kraken['error'] !=NULL) {
	echo "<div class='error'>" . $json_kraken['error'] . "</div>";
	die();	
}
//Start Building content, first up the Team name. This can be ommitted if not being displayed.
/*echo "<div class='team_name'>" . $json_kraken['display_name'] . "</div><br>";*/
//This is where we build the roster, for the purposes of the website all we need is displayname and status.
$jsn_p = $json_api['channels']; //this converts the channel listings to an array called jsn_p that we can parse later
$online = array(); //This will be needed later for the array_push below. It store the "online" folks
foreach ($jsn_p as $jsn) {
	$name = $jsn['channel']['display_name']; //Pulls the "Display name"
	$active = $jsn['channel']['status']; //Pulls the Live/Offline status
	//We only care about "Live" users, as such everyone else is omitted.
	if ($active == 'live') { //This is where the names are added to the array.
			array_push($online, $name);
		}
}
//We only need 1 div, it will pull a user from the "Online" array at random and populate the needed information
///If a user goes offline currently it will NOT automatically refresh. Logic needs to be added to rebuild this periodically.
//buttons get built
echo '<div class="buttons">';
foreach ($jsn_p as $jsn) {
	$name = $jsn['channel']['display_name']; //gets the streamer name
	$active = $jsn['channel']['status']; //gets the stats (live offline)
	//$playing = $jsn['channel']['meta_game']; //gets the name of the game they are playing, not currently used in the buttons. But left as an example
	//Live users get one designation
	if ($active == 'live') {
		$chan = 'https://api.twitch.tv/kraken/streams/'.$name;
		$jchan = curl($chan);
		$json_chan = json_decode($jchan, true);
		$viewers = $json_chan['stream']['viewers']; //gets the current number of viewers
		//This is where the names are added to the array.
		//array_push($online, $name, $viewers);
		echo '<img src="./images/online.png"/><a href="http://www.twitch.tv/' . $name . '"><button class="live" type="button">'.$name.' Online '.$viewers.'</button></a></br>';
		}
	//Offline users get a different set of contstraints. We need much less information here since we already know they are offline.
	else {
		echo '<img src="./images/offline.png"><a href="http://www.twitch.tv/' . $name . '"><button class="offline" type="button">' . $name . ' Offline</button></a></br>';
	}
}
echo '</div>';
?>

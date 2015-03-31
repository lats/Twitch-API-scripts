<?php
//everyone loves curl
function curl($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}
//Make the API calls to Twitch, and decode them appropriately.
///Don't want API keys plain text
include_once("./client.php");
$api = 'http://api.twitch.tv/api/team/juicegaming/all_channels.json?clientId='.$clientId;
$kraken = 'https://api.twitch.tv/kraken/teams/juicegaming?clientId=' .$clientId;
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
///echo "<div class='team_name'>" . $json_kraken['display_name'] . "</div><br>";
//This is where we build the roster, for the purposes of the website all we need is displayname and status.
$jsn_p = $json_api['channels']; //this converts the channel listings to an array called jsn_p that we can parse later
foreach ($jsn_p as $jsn) {
	$name = $jsn['channel']['display_name']; //Pulls the "Display name"
	$active = $jsn['channel']['status']; //Pulls the Live/Offline status
	//We only care about "Live" users, as such everyone else is omitted.
	if ($active == 'live') {
		//This is where the names are added to the array.
		$online = array();
		array_push($online, $name);
		//print_r($online); //bug checking, to ensure names are properly pushed to the array.
		}
}
//We only need 1 div, it will pull a user from the "Online" array at random and populate the needed information
///If a user goes offline it will NOT refresh and pull a new one randomly. May want to make it rotate on a timer instead of random.
echo '<div class="stream">';
$tag = array_rand($online, 1);
$winner = $online[$tag];
echo '<div id="' . $winner . '" style="display:block">
	<table border="0">
		<tbody>
			<tr>
				<td>        
				<object type="application/x-shockwave-flash" height="500" width="750" id="live_embed_player_flash" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=' . $winner . '" bgcolor="#000000">
					<param name="allowFullScreen" value="true" />
					<param name="allowScriptAccess" value="always" />
					<param name="allowNetworking" value="all" />
					<param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf" />
					<param name="flashvars" value="hostname=www.twitch.tv&amp;channel=' . $winner . '&amp;auto_play=true&amp;start_volume=25" /></object>
				</td>
			</tr>
		</tbody>
	</table>
</div>';
echo '</div>'
?>

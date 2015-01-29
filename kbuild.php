<html>
<head>
<LINK href="kbuild.css" rel="stylesheet" type="text/css">
<script type='text/javascript' src='//code.jquery.com/jquery-1.9.1.js'></script>
<!--toggle streams when the button is clicked !-->
<script type='text/javascript'>//<![CDATA[ 
$(window).load(function(){
$('div.buttons').on('click', 'button', function () {
    var divs = $('div.stream').children();
    divs.eq($(this).index()).show().siblings().hide();
});
});//]]>  

</script>
</head>
<body>
<?php
//check if "team" is set, and if not error
if (htmlspecialchars($_GET["team"]) !=NULL){
	$team = htmlspecialchars($_GET["team"], ENT_QUOTES);
	}
else {
	echo "No team specified. Please specify the team you wish to view:";
	echo '<form method="GET" action="kbuild.php">
		  Team Name <input type="text" name="team" maxchars="200"><br>';
	echo "<input class='submit' type='submit' value='Lookup Team'></form>";
	die();
}
//everyone loves curl
function curl($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}
//We need $s to be 0 first
$s = 0;
//Make the API calls to Twitch, and decode them appropriately.
///Don't want API keys plain text
include_once("./client.php");
$api = 'http://api.twitch.tv/api/team/' . $team . '/all_channels.json?clientId='.$clientId;
$kraken = 'https://api.twitch.tv/kraken/teams/' . $team . '?clientId=' .$clientId;
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
//Start Building content, first up the Team name.
echo "<div class='team_name'>" . $json_kraken['display_name'] . "</div><br>";
$jsn_p = $json_api['channels'];
//Next up we have the members of the team getting buttons to be used later.
echo '<div class="buttons">';
foreach ($jsn_p as $jsn) {
	$name = $jsn['channel']['display_name'];
	$active = $jsn['channel']['status'];
	$playing = $jsn['channel']['meta_game'];
	//Live users get a Green button
	if ($active == 'live') {
		if ($s == "0") {
			$url = "?s" . $s . "=" . $name;
		}
		else {
			$url = "&s" . $s . "=" . $name;
			}
		echo '<button class="live" id="' . $name . '" value="' . $name . '">' . $name .  '</button>';
		$built .= $url;
		$s++;
		}
	//Offline users get a Red button
	else {
		echo '<button class="offline" id="' . $name . '" value="' . $name . '">' . $name .  '</button>';
	}
}
//Watch all currently online users via KBMod.
$layout = $s*3-1;
echo '<input class="kbmod" type="button" value="Watch Active streams on KBmod" onclick="window.open(\'http://kbmod.com/multistream/view/' . $built . '&layout=' . $layout . '\')"/>';
echo '</div>';
//Creates the divs to be controlled by the previously created buttons.
echo '<div class="stream">';
foreach ($jsn_p as $jsn) {
	$name = $jsn['channel']['display_name'];
	$active = $jsn['channel']['status'];
	if ($active == 'live') {
	echo '<div id="' . $name . '" style="display:none">
		<table border="0">
			<tbody>
				<tr>
					<td>        
						<object type="application/x-shockwave-flash" height="500" width="750" id="live_embed_player_flash" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=' . $name . '" bgcolor="#000000">
						<param name="allowFullScreen" value="true" />
						<param name="allowScriptAccess" value="always" />
						<param name="allowNetworking" value="all" />
						<param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf" />
						<param name="flashvars" value="hostname=www.twitch.tv&amp;channel=' . $name . '&amp;auto_play=false&amp;start_volume=25" /></object>
					</td>
				</tr>
				<tr>
					<td>
						<iframe frameborder="0" scrolling="no" src="http://twitch.tv/chat/embed?channel=' . $name . '&amp;popout_chat=true" height="250" width="100%"></iframe>
					</td>
				</tr>
			</tbody>
		</table>
	</div>';
	}
}
echo '</div>'
?>
</body>
</html>

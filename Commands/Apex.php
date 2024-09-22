<?php

	function Apex($message) {
		
		$get = file_get_contents("https://apexlegendsstatus.com/current-map/battle_royale/pubs");
		preg_match('/<h3 .*>(.+)<\/h3>.+ ends in (.+)<\/p>/U', $get, $data);
		preg_match_all('/<h3 .*>(.+)<\/h3>/U', $get, $next);
	
		$message->channel->sendMessage($data[1]." ends in ".$data[2]." | Next Map: ".$next[1][1]);
		
	}
	
?>
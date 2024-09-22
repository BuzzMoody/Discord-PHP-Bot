<?php

	function urbanDic($message, $args) {
		
		$getUD = (empty($args)) ? @file_get_contents("https://www.urbandictionary.com/random.php") : @file_get_contents("https://www.urbandictionary.com/define.php?term=".urlencode($args));
		if (empty($getUD)) { return $message->channel->sendMessage("Word not found"); }
		preg_match_all("/(:?href=\"\/define\.php\?term=(.+)\" id=\"\d+\">(.+)<\/a><\/h1>|<div class=\"break-words meaning mb-4\">(.+)<\/div>)/mU", $getUD, $matches);
		
		$message->channel->sendMessage("**".str_replace('+',' ', $matches[2][0])."**: ".html_entity_decode(strip_tags($matches[4][1])));
		
	}
	
?>
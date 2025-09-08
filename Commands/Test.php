<?php

	function Test($message) {
		
		global $discord;
	
		if (isAdmin($message->author->id) && getenv('BETA') === 'true') {
			
			$guild = $discord->guilds->get('id', '232691831090053120');
			$channel = $guild->channels->get('id', '274828566909157377');
			$message2 = $channel->messages->fetch('1414434587677032499')->then(function ($message2) {
				return simpleEmbed("Chat Reminders", "attachment://bot.webp", "This is your reminder or some shit I dunno", $message2, true, null); 
			});				

		}
		
	}
	
?>
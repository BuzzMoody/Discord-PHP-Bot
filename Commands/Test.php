<?php

	function Test($message) {
		
		global $discord;
	
		if (isAdmin($message->author->id) && getenv('BETA') === 'true') {
			
			// put test code here
			
			return $message->channel->sendFile("/Media/Images/sydney/test.webp", "sydney.webp");
		
		}
		
	}
	
?>
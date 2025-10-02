<?php

	function BetaReload($message) { 
	
		if (isAdmin($message->author->id) && getenv('BETA') === 'true') {
			die();
		}
		
	}
	
?>
<?php

	function BetaReload($message) { 
	
		global $keys;
	
		if (isAdmin($message->author->id) && getenv('BETA') === true) {
			exec("git stash");
			exec("git pull origin discord");
			die();
		}
		
	}
	
?>
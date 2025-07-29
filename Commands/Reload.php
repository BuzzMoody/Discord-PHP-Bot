<?php

	function Reload($message) { 
	
		if (isAdmin($message->author->id) && getenv('BETA') !== 'true') {
			exec("git stash");
			exec("git pull origin docker");
			die();
		}
		
	}
	
?>
<?php

	function BetaReload($message) { 
	
		global $keys;
	
		if (isAdmin($message->author->id) && $keys['beta']) {
			exec("git stash");
			exec("git pull origin beta");
			die();
		}
		
	}
	
?>
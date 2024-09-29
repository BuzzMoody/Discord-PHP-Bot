<?php

	function Reload($message) { 
	
		global $keys;
	
		if (isAdmin($message->author->id) && !$keys['beta']) {
			exec("git stash");
			exec("git pull origin main");
			die();
		}
		
	}
	
?>
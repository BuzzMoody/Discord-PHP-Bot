<?php

	function Reload($message) { 
	
		global $keys;
	
		if (isAdmin($message->author->id)) {
			exec("git stash");
			exec("git pull https://buzz:{$keys['gh']}@github.com/BuzzMoody/Discord-PHP-Bot.git");
			die();
		}
		
	}
	
?>
<?php

	function Test($message) {
	
		if (isAdmin($message->author->id) && getenv('BETA') === 'true') {
			
			print_r($this->discord);
			
			print_r($this->pdo);

			return;			

		}
		
	}
	
?>
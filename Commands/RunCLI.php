<?php

	function RunCLI($message, $args) {
		
		if ($message->author->id == 232691181396426752 && !empty($args)) {		
			$message->channel->sendMessage("```swift\n".shell_exec($args)."\n```");		
		}
		
	}
	
?>
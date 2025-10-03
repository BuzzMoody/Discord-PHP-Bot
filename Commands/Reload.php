<?php

	class Reload extends AbstractCommand {
		
		public function getName(): string {
			return 'Reload';
		}
		
		public function getDesc(): string {
			return 'Reloads the bot with updated code.';
		}
		
		public function getPattern(): string {
			return '/^reload$/';
		}
		
		public function execute($message, $args, $matches) {
		
			if ($this->utils->isAdmin($message->author->id) && getenv('BETA') !== 'true') {
				die();
			}
		
		}
		
	}

?>
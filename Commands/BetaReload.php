<?php

	class BetaReload extends AbstractCommand {
		
		public function getName(): string {
			return 'BetaReload';
		}
		
		public function getDesc(): string {
			return 'Reloads the bot with updated code.';
		}
		
		public function getPattern(): string {
			return '/^betarl$/';
		}
		
		public function execute($message, $args, $matches) {
		
			if ($this->utils->isAdmin($message->author->id) && getenv('BETA') === 'true') {
				die();
			}
		
		}
		
	}

?>
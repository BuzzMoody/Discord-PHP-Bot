<?php

	class Reload extends AbstractCommand {
		
		public function getName(): string {
			return 'Reload';
		}
		
		public function getDesc(): string {
			return 'Reloads the bot with updated code.';
		}
		
		public function getPattern(): string {
			return '/^(?:(reload)|(betarl))$/';
		}
		
		public function execute(Message $message, string $args, array $matches) {
			
			if ($this->utils->isAdmin($message->author->id)) {
				
				if (!empty($matches[1]) && !$this->utils->betaCheck()) {
					
					die();
					
				} 
				elseif (!empty($matches[2]) && $this->utils->betaCheck()) {
					
					die();
					
				}
				
			}
		
		}
		
	}

?>
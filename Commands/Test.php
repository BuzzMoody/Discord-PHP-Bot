<?php

	class Test extends AbstractCommand {
		
		public function getName(): string {
			return 'Test';
		}
		
		public function getDesc(): string {
			return 'Test command lol.';
		}
		
		public function getPattern(): string {
			return '/^test$/';
		}
		
		public function execute($message, $args, $matches) {
		
			if ($this->utils->isAdmin($message->author->id) && $this->utils->betaCheck()) {

				$message->reply(":df:");

			}
		
		}
		
	}
	
?>
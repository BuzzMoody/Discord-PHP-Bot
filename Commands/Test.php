<?php

	use Discord\Parts\Channel\Message;
	
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
		
		public function execute(Message $message, string $args, array $matches): void {
		
			if ($this->utils->isAdmin($message->author->id) && $this->utils->betaCheck()) {

				$testFunction = function (string $mustBeString) {
					return $mustBeString;
				};

				$testFunction(null);

			}
		
		}
		
	}
	
?>
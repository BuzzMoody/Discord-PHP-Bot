<?php

	namespace app\Commands;

	use Discord\Parts\Channel\Message;
	
	class RunCLI extends AbstractCommand {
		
		public function getName(): string {
			return 'RunCLI';
		}
		
		public function getDesc(): string {
			return 'Runs bash commands on the host.';
		}
		
		public function getPattern(): string {
			return '/^(shell|bash|cli|cmd)/';
		}
		
		public function execute(Message $message, string $args, array $matches): void {
		
			if ($message->author->id == 232691181396426752 && !empty($args)) {		
				$message->channel->sendMessage("```swift\n".shell_exec($args)."\n```");		
			}
		
		}
		
	}
	
?>
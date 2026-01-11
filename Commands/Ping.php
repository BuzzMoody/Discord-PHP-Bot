<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Builders\MessageBuilder;

	class Ping extends AbstractCommand {
		
		private const RESPONSES = [
			"Pong! Right back at ya.",
			"Ping received. Pong!",
			"Got it!",
			"Ping received, initiating pong sequence... Pong!",
			"Did someone say ping? Pong!",
			"You rang? Pong!",
			"Copy that. Pong!",
			"The answer is always... pong."
		];
		
		public function getName(): string {
			return 'Ping';
		}
		
		public function getDesc(): string {
			return 'Returns a pong to your ping. A marco to your polo.';
		}
		
		public function getPattern(): string {
			return '/^ping$/';
		}
		
		public function execute(Message $message, string $args) {
			
			$embed = $this->discord->factory(Embed::class);
			$embed->setColor(getenv('COLOUR'))
				->setDescription(self::RESPONSES[array_rand(self::RESPONSES)]);
			
			$builder = MessageBuilder::new()
				->addEmbed($embed);

			return $message->reply($builder);
			
		}
		
	}

?>
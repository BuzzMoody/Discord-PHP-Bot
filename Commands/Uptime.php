<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Builders\MessageBuilder;

	class Uptime extends AbstractCommand {
		
		public function getName(): string {
			return 'Uptime';
		}
		
		public function getPattern(): string {
			return '/^uptime$/';
		}
		
		public function execute($message) {
		
			$diff = ((int)(microtime(true) * 1000) - $this->uptime) / 1000;
			$days = floor($diff / 86400);
			$diff -= $days * 86400;
			$hours = floor($diff / 3600) % 24;
			$diff -= $hours * 3600;
			$minutes = floor($diff / 60) % 60;
			$diff -= $minutes * 60;
			$seconds = floor((int)$diff % 60);
		
			$embed = $this->discord->factory(Embed::class);
			$embed->setColor(getenv('COLOUR'))
				->setDescription("{$days} days, {$hours} hrs, {$minutes} mins, {$seconds} secs");

			$builder = MessageBuilder::new()
				->addEmbed($embed);

			return $message->reply($builder);
		
		}
		
	}

?>
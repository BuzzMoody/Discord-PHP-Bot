<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;

	function Uptime($message) {
		
		global $uptime, $discord;
		
		$diff = ((int)(microtime(true) * 1000) - $uptime) / 1000;
		$days = floor($diff / 86400);
		$diff -= $days * 86400;
		$hours = floor($diff / 3600) % 24;
		$diff -= $hours * 3600;
		$minutes = floor($diff / 60) % 60;
		$diff -= $minutes * 60;
		$seconds = floor((int)$diff % 60);
		
		$embed = $discord->factory(Embed::class);
		$embed->setAuthor('Uptime', 'attachment://bot.webp')
			->setColor(getenv('COLOUR'))
			->setDescription("{$days} days, {$hours} hrs, {$minutes} mins, {$seconds} secs");
		
		$builder = MessageBuilder::new()
			->addEmbed($embed)
			->addFile("/Media/bot.webp", "bot.webp");

		return $message->reply($builder);
		
	}

?>
<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;

	function Ping($message) {
		
		global $discord;
		
		$responses = [
			"Pong! Right back at ya.",
			"Ping received. Pong!",
			"Got it!",
			"Ping received, initiating pong sequence... Pong!",
			"Did someone say ping? Pong!",
			"You rang? Pong!",
			"Copy that. Pong!",
			"The answer is always... pong."
		];
		
		$pingKey = array_rand($responses);
		
		$embed = $discord->factory(Embed::class);
		$embed->setColor(getenv('COLOUR'))
			->setDescription($responses[$pingKey]);
		
		$builder = MessageBuilder::new()
			->addEmbed($embed);

		return $message->reply($builder);
		
	}

?>
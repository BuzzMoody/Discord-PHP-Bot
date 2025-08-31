<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;

	function Radar($message) {
		
		global $discord;
		
		$time = microtime(true);
		$embed = $discord->factory(Embed::class);
		$embed->setAuthor("Melbourne Weather Radar", "attachment://BOM.png", "http://www.bom.gov.au/products/IDR023.loop.shtml")
			->setImage("attachment://radar-{$time}.gif")
			->setColor(getenv('COLOUR'));
		$builder = MessageBuilder::new()
			->addEmbed($embed)
			->addFile("/Media/Maps/BOM.png", "BOM.png")
			->addFile("/Media/Radar/animated.gif", "radar-{$time}.gif");		
		$message->channel->sendMessage($builder);
		
	}
	
?>
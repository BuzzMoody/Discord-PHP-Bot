<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;
	
	class Radar extends AbstractCommand {
		
		public function getName(): string {
			return 'Radar';
		}
		
		public function getDesc(): string {
			return 'Current weather radar imagery for Melbourne.';
		}
		
		public function getPattern(): string {
			return '/^radar$/';
		}
		
		public function execute(Message $message, string $args) {
		
			$time = microtime(true);
			$embed = $this->discord->factory(Embed::class);
			$embed->setAuthor("Melbourne Weather Radar", "https://beta.bom.gov.au/themes/custom/bom_theme/images/icons/favicon-32.png", "http://www.bom.gov.au/products/IDR023.loop.shtml")
				->setImage("attachment://radar-{$time}.gif")
				->setColor(getenv('COLOUR'));
			$builder = MessageBuilder::new()
				->addEmbed($embed)
				->addFile("/Media/Radar/animated.gif", "radar-{$time}.gif");		
			$message->channel->sendMessage($builder);
		
		}
		
	}
	
?>
<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;

	function UrbanDic($message, $args) {
		
		global $discord;
		
		$http = new Browser();
		$http->get("https://unofficialurbandictionaryapi.com/api/search?term={$args}&limit=1")->then(
			function (ResponseInterface $response) use ($message, $discord) {
				
				if ($response->getStatusCode() === 404) {
					
					$embed = $discord->factory(Embed::class);
					$embed->setAuthor('Urban Dictionary', 'https://www.urbandictionary.com/favicon-32x32.png')
						->setColor(getenv('COLOUR'))
						->setDescription("No entries found for __{$args}__");
						
					$builder = MessageBuilder::new()
						->addEmbed($embed);
						
					return $message->reply($builder);		
					
				}
				
				$output = json_decode($response->getBody());
				
				$embed = $discord->factory(Embed::class);
				$embed->setAuthor("{$output->data[0]->word} - Urban Dictionary", 'https://www.urbandictionary.com/favicon-32x32.png')
					->setColor(getenv('COLOUR'))
					->setDescription($output->data[0]->meaning)
					->addFieldValues("Example", $output->data[0]->example, true);
					
				return $message->channel->sendEmbed($embed);
			},
			function (Exception $e) use ($message) {
				$message->channel->send("Error: {$e->getMessage()}");
			}
		);
		
	}
	
?>
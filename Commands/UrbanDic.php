<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Builders\MessageBuilder;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	
	class UrbanDic extends AbstractCommand {
		
		public function getName(): string {
			return 'UrbanDic';
		}
		
		public function getDesc(): string {
			return 'Get word and phrase definitions from the Urban Dictionary.';
		}
		
		public function getPattern(): string {
			return '/^u(?:rban)?d(?:ictionary)?/';
		}
		
		public function execute(Message $message, string $args, array $matches): void {
		
			$url = (empty($args)) ? "https://unofficialurbandictionaryapi.com/api/random?limit=1" : "https://unofficialurbandictionaryapi.com/api/search?term={$args}&limit=1";
			
			$http = new Browser();
			$http->get($url)->then(
				function (ResponseInterface $response) use ($message) {
					$output = json_decode($response->getBody());
					$embed = $this->discord->factory(Embed::class);
					$embed->setAuthor("{$output->data[0]->word} - Urban Dictionary", 'https://www.urbandictionary.com/favicon-32x32.png')
						->setColor(getenv('COLOUR'))
						->setDescription($output->data[0]->meaning)
						->addFieldValues("Example", $output->data[0]->example, true);
						
					$message->channel->sendEmbed($embed);
				},
				function (Exception $e) use ($message, $args) {
					$embed = $this->discord->factory(Embed::class);
					$embed->setAuthor('Urban Dictionary', 'https://www.urbandictionary.com/favicon-32x32.png')
						->setColor(getenv('COLOUR'))
						->setDescription("No entries found for *{$args}*");
						
					$builder = MessageBuilder::new()
						->addEmbed($embed);
						
					$message->reply($builder);	
				}
			);
		
		}
		
	}
	
?>
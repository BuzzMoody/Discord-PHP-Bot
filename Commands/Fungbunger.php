<?php

	use Discord\Parts\Channel\Message;
	use Discord\Parts\Embed\Embed;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;

	class Fungbunger extends AbstractCommand {
		
		public function getName(): string {
			return 'Fungbunger';
		}
		
		public function getDesc(): string {
			return 'Tweets from the great man';
		}
		
		public function getPattern(): string {
			return '/^fung(?:bunger)?|parsfarce$/';
		}
		
		public function execute(Message $message, string $args, array $matches): void {
			
			$url = (empty($args) || !ctype_digit($args)) ? "https://fungbunger.au/api.php" : "https://fungbunger.au/api.php?id=".$args;
		
			$http = new Browser();

			$http->get($url)->then(
				function (ResponseInterface $response) use ($message) {
					$output = json_decode($response->getBody());
					$date = new DateTime($output->tweets[0]->timestamp);
					$formattedDate = $date->format('g:i A \• M j, Y');
					$embed = $this->discord->factory(Embed::class);
					$embed->setAuthor('Fungbunger (@parsfarce)', 'https://fungbunger.au/images/fung_profile.jpg', "https://fungbunger.au/api.php?id=".$output->id)
						->setColor(getenv('COLOUR'))
						->setDescription($output->tweets[0]->content)
						->setThumbnail("https://fungbunger.au/images/fung_profile.jpg")
						->setFooter($formattedDate);
					$message->channel->sendEmbed($embed);
				},
				function (Exception $e) use ($message) {
					$message->channel->sendMessage("Error: {$e->getMessage()}");
				}
			);
		
		}
		
	}

?>
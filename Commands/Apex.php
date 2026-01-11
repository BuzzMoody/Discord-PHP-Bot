<?php

	use Discord\Parts\Embed\Embed;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;

	class Apex extends AbstractCommand {
		
		public function getName(): string {
			return 'Apex';
		}
		
		public function getDesc(): string {
			return 'Gives the current Apex Legends map.';
		}
		
		public function getPattern(): string {
			return '/^apex$/';
		}
		
		public function execute(Message $message, string $args, array $matches): void {
			
			$http = new Browser();
			$http->get('https://apexlegendsstatus.com/current-map/battle_royale/pubs')->then(
				function (ResponseInterface $response) use ($message) {
					$output = $response->getBody();
					preg_match('/<h3 .*>(.+)<\/h3>.+ ends in (.+)<\/p>/U', $output, $data);
					preg_match_all('/<h3 .*>(.+)<\/h3>/U', $output, $next);
					$embed = $this->discord->factory(Embed::class);
					$embed->setAuthor('Apex Legends Status', 'https://apexlegendsstatus.com/assets/layout/favicon-dark-scheme-32x32.png', 'https://apexlegendsstatus.com/')
						->setColor(getenv('COLOUR'))
						->setDescription("The current map is **{$data[1]}**.\n\nThe next map is **{$next[1][1]}** and starts in **{$data[2]}**")
						->setImage("https://apexlegendsstatus.com/assets/maps/".str_replace(' ', '_', $data[1]).".png");
					$message->channel->sendEmbed($embed);
				},
				function (Exception $e) use ($message) {
					$message->channel->sendMessage("Error: {$e->getMessage()}");
				}
			);
			
		}
		
	}
	
?>


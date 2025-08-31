<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;

	function Apex($message) {
		
		global $discord;
		
		$http = new Browser();
		$http->get('https://apexlegendsstatus.com/current-map/battle_royale/pubs')->then(
			function (ResponseInterface $response) use ($message, $discord) {
				$output = $response->getBody();
				preg_match('/<h3 .*>(.+)<\/h3>.+ ends in (.+)<\/p>/U', $output, $data);
				preg_match_all('/<h3 .*>(.+)<\/h3>/U', $output, $next);
				$embed = $discord->factory(Embed::class);
				$embed->setAuthor('Apex Legends Status', 'https://apexlegendsstatus.com/assets/layout/favicon-dark-scheme-32x32.png', 'https://apexlegendsstatus.com/')
					->setColor(getenv('COLOUR'))
					->setDescription("The current map is **{$data[1]}**.\n\nThe next map is **{$next[1][1]}** and starts in **{$data[2]}** minutes")
					->setImage("https://apexlegendsstatus.com/assets/maps/".str_replace(' ', '_', $data[1]).".png");
				$message->channel->sendEmbed($embed);
			},
			function (Exception $e) use ($message) {
				$message->channel->send("Error: {$e->getMessage()}");
			}
		);
		
	}
	
?>
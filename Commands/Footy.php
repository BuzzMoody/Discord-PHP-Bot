<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	
	function Footy($message) {
		
		global $discord, $keys;
	
		$client = new Browser();
		$embed = $discord->factory(Embed::class);
		
		$embed->setTitle("AFL Round Summary");

		$client->get('https://aflapi.afl.com.au/afl/v2/matches?competitionId=1&compSeasonId=73&pageSize=10&roundNumber=10')->then(
			function (ResponseInterface $response) use ($message, $embed, $keys) {
				
				$responseBody = $response->getBody();
				$responseData = json_decode($responseBody);
				$day = "Thursday";
				
				foreach ($responseData->matches as $game) {
					$time = new DateTime($game->utcStartTime);
					$time->setTimezone(new DateTimeZone('Australia/Melbourne'));
					$dayName = $time->format('l');
					$gameDay = $time->format('l jS F');
					$gameTime = $time->format('g:ia');
					$gamesList[$gameDay][] = array(
						"ID" => $game->providerId,
						"teams" => $game->home->team->name." vs ".$game->away->team->name,
						"time" => $gameTime,
						"venue" => $game->venue->name.", ".$game->venue->location
					);
				}
				
				foreach ($gamesList as $key => $value) {
					$content = "";
					foreach ($value as $session) {
						$content .= "**{$session['teams']}** {$session['time']} (*{$session['venue']}*)\n\n";
					}
					$embed->addFieldValues($key, $content, false);
				}
				
				$embed->setColor($keys['colour']);
				$builder = MessageBuilder::new()
					->addEmbed($embed);
					
				$message->channel->sendMessage($builder);
				
			},
			function (Exception $e) use ($message) {
				$message->channel->sendMessage('Error: ' . $e->getMessage());
			}
		);
		
	}
	
?>
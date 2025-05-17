<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	
	function Footy($message) {
		
		//if ($message->channel->id != 1352902587837583370) { return; }
		
		global $discord, $keys;
	
		$client = new Browser();
		$embed = $discord->factory(Embed::class);

		$client->get('https://www.afl.com.au')->then(
			function (ResponseInterface $response) use ($client, $message, $embed, $keys) {
			
				$responseBody = $response->getBody();
				preg_match("/data-round-number=\"(\d+)\"/", $responseBody, $round);
				
				$embed->setTitle("AFL Round {$round[1]} Summary")
					->setFooter("Australian Football League", "https://www.afl.com.au/resources/v5.32.21/afl/apple-touch-icon.png");
				
				$client->get('https://aflapi.afl.com.au/afl/v2/matches?competitionId=1&compSeasonId=73&pageSize=10&roundNumber='.$round[1])->then(
					function (ResponseInterface $response) use ($message, $embed, $keys) {
						
						$responseBody = $response->getBody();
						$responseData = json_decode($responseBody);
						
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
								$content .= " {$session['time']}: {$session['teams']} (*{$session['venue']}*)\n";
							}
							$embed->addFieldValues("**{$key}**", $content, false);
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
		);
		
	}
	
?>
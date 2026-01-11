<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Builders\MessageBuilder;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	
	class Footy extends AbstractCommand {
		
		public function getName(): string {
			return 'Footy';
		}
		
		public function getDesc(): string {
			return 'Information on the current round of AFL games';
		}
		
		public function getPattern(): string {
			return '/^(afl|footy)$/';
		}
		
		public function execute(Message $message, string $args) {
		
			if ($message->channel->id != 1352902587837583370) { return; }
	
			$client = new Browser();
			$embed = $this->discord->factory(Embed::class);
			
			$embed->setAuthor("AFL Round Summary", "https://www.afl.com.au/resources/v5.37.23/afl/favicon-32x32.png");

			$client->get('https://www.afl.com.au')->then(
				function (ResponseInterface $response) use ($client, $message, $embed) {
				
					$responseBody = $response->getBody();
					preg_match("/data-round-number=\"(\d+)\"/", $responseBody, $round);
					
					$client->get('https://aflapi.afl.com.au/afl/v2/matches?competitionId=1&compSeasonId=73&pageSize=10&roundNumber='.$round[1])->then(
						function (ResponseInterface $response) use ($message, $embed) {
							
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
									$content .= " {$session['time']}: {$session['teams']} (*{$session['venue']}*)\n";
								}
								$embed->addFieldValues("**{$key}**", $content, false);
							}
							
							$embed->setColor(getenv('COLOUR'));
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
		
	}
	
?>
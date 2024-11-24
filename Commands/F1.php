<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;

	function F1($message) {
		
		global $discord, $keys;
		
		$http = new Browser();

		$headers = array(
		  'apikey' => 'BQ1SiSmLUOsp460VzXBlLrh689kGgYEZ',
		  'locale' => 'en',
		);

		$http->get('https://api.formula1.com/v1/event-tracker', $headers)->then(
			function (ResponseInterface $response) use ($message, $discord, $keys) {
				$output = json_decode($response->getBody());
				$embed = $discord->factory(Embed::class);
				$embed->setAuthor('Formula 1 - Race Weekend', 'https://media.formula1.com/etc/designs/fom-website/icon192x192.png')
					->setTitle($output->race->meetingOfficialName)
					->setURL("https://www.formula1.com{$output->race->url}")
					->setColor($keys['colour'])
					->setDescription("The current location is **{$output->race->meetingLocation}, {$output->race->meetingCountryName}**.");
				foreach ($output->seasonContext->timetables as $event) {
					$fieldval = ($event->state == 'completed') ? "~~".toAusTime($event->startTime, 'G:i', null, $event->gmtOffset)." - ".toAusTime($event->endTime, 'G:i', null, $event->gmtOffset)."~~ - [Results](https://www.formula1.com/en/results/{$output->seasonContext->seasonYear}/races/{$output->fomRaceId}/F1/".str_replace(' ', '/', strtolower($event->description)).")" : toAusTime($event->startTime, 'G:i', null, $event->gmtOffset)." - ".toAusTime($event->endTime, 'G:i', null, $event->gmtOffset)." (Starts <t:".strtotime(toAusTime($event->startTime, 'Y-m-d\TH:i:s', null, $event->gmtOffset, true)).":R>)";
					$embed->addFieldValues($event->description, $fieldval, false);
				}
				$message->channel->sendEmbed($embed);
			},
			function (Exception $e) use ($message) {
				$message->channel->send("Error: {$e->getMessage()}");
			}
		);
	
	}

?>
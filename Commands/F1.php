<?php

	use Discord\Parts\Embed\Embed;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;

	class F1 extends AbstractCommand {
		
		public function getName(): string {
			return 'F1';
		}
		
		public function getDesc(): string {
			return 'Details for the next Formula 1 race event';
		}
		
		public function getPattern(): string {
			return '/^f(?:ormula)?1$/';
		}
		
		public function execute(Message $message, string $args, array $matches): void {
		
			$http = new Browser();

			$headers = array(
			  'apikey' => 'BQ1SiSmLUOsp460VzXBlLrh689kGgYEZ',
			  'locale' => 'en',
			);

			$http->get('https://api.formula1.com/v1/event-tracker', $headers)->then(
				function (ResponseInterface $response) use ($message) {
					$output = json_decode($response->getBody());
					$embed = $this->discord->factory(Embed::class);
					$embed->setAuthor('Formula 1 - Race Weekend', 'https://media.formula1.com/etc/designs/fom-website/icon192x192.png', "https://www.formula1.com{$output->race->url}")
						->setTitle($output->race->meetingOfficialName)
						->setColor(getenv('COLOUR'))
						->setDescription("The current location is **{$output->race->meetingLocation}, {$output->race->meetingCountryName}**.");
					foreach ($output->seasonContext->timetables as $event) {
						$fieldval = ($event->state == 'completed') ? "~~".$this->utils->toAusTime($event->startTime, 'G:i', null, $event->gmtOffset)." - ".$this->utils->toAusTime($event->endTime, 'G:i', null, $event->gmtOffset)."~~ - [Results](https://www.formula1.com/en/results/{$output->seasonContext->seasonYear}/races/{$output->fomRaceId}/F1/".str_replace(' ', '/', strtolower($event->description)).")" : $this->utils->toAusTime($event->startTime, 'G:i', null, $event->gmtOffset)." - ".$this->utils->toAusTime($event->endTime, 'G:i', null, $event->gmtOffset)." (Starts <t:".strtotime($this->utils->toAusTime($event->startTime, 'Y-m-d\TH:i:s', null, $event->gmtOffset, true)).":R>)";
						$embed->addFieldValues($event->description, $fieldval, false);
					}
					$message->channel->sendEmbed($embed);
				},
				function (Exception $e) use ($message) {
					$message->channel->sendMessage("Error: {$e->getMessage()}");
				}
			);
		
		}
		
	}

?>
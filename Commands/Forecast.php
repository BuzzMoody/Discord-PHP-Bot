<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;
	
	class Forecast extends AbstractCommand {
		
		public function getName(): string {
			return 'Forecast';
		}
		
		public function getDesc(): string {
			return 'The weeks weather forecast for a given location.';
		}
		
		public function getPattern(): string {
			return '/^forecast$/';
		}
		
		public function execute($message, $args, $matches) {
		
			$place = $this->utils->getLocale($args);
			if (!$place) { return $this->utils->simpleEmbed("BOM Weather", "https://beta.bom.gov.au/themes/custom/bom_theme/images/icons/favicon-32.png", "Location not found. Try using a larger town/city located nearby.", $message, true, "https://bom.gov.au"); }
			
			$embed = $this->discord->factory(Embed::class);

			$forecast = json_decode(@file_get_contents("https://api.beta.bom.gov.au/apikey/v1/forecasts/daily/{$place['forecast']}?timezone=Australia%2FMelbourne"));
			array_shift($forecast->fcst->daily);
			$i=0;
			foreach ($forecast->fcst->daily as $daily) {
				$uv = (!empty($daily->atm->surf_air->radiation->uv_clear_sky_max_code) && $i < 3) ? ", uv ".round(@$daily->atm->surf_air->radiation->uv_clear_sky_max_code, 1) : "";
				$icon = preg_replace(array('/^1$/', '/^2$/', '/^3$/', '/^4$/', '/^5$/', '/^6$/', '/^7$/', '/^8$/', '/^9$/', '/^10$/', '/^11$/'), array('☀️', '2', '🌤', ':cloud:', '5', '6', '7', '8', '9', '🌫️', '🌦️'), $daily->atm->surf_air->weather->icon_code);
				$embed->addFieldValues($this->utils->toAusTime($daily->date_utc, 'l jS')." {$icon}", round($daily->atm->surf_air->temp_max_cel, 1)."° / ".round($daily->atm->surf_air->temp_min_cel, 1)."° \n_☔ {$daily->atm->surf_air->precip->any_probability_percent}% {$uv}_", true);
				$i++;
			}
			
			$this->utils->getMapImg($place);
			
			$embed->setColor(getenv('COLOUR'))
				->setAuthor("{$place['name']}, {$place['state']} ({$place['postcode']}) - Weather Forecast", "https://beta.bom.gov.au/themes/custom/bom_theme/images/icons/favicon-32.png")
				->setImage("attachment://map-of-{$place['filename']}.png");
				
			$builder = MessageBuilder::new()
				->addEmbed($embed)
				->addFile("/Media/Maps/{$place['filename']}.png", "map-of-{$place['filename']}.png");
			
			return $message->channel->sendMessage($builder);
		
		}
		
	}

?>
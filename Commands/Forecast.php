<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;

	function Forecast($message, $args) {
		
		global $discord;
		
		$place = getLocale($args);
		if (!$place) { return $message->channel->sendMessage("No location found"); }
		$embed = $discord->factory(Embed::class);
		$embed->setTitle("{$place['name']}, {$place['state']} ({$place['postcode']})");
		$forecast = json_decode(@file_get_contents("https://api.beta.bom.gov.au/apikey/v1/forecasts/daily/{$place['forecast']}?timezone=Australia%2FMelbourne"));
		array_shift($forecast->fcst->daily);
		$i=0;
		foreach ($forecast->fcst->daily as $daily) {
			$uv = (!empty($daily->atm->surf_air->radiation->uv_clear_sky_max_code) && $i < 3) ? ", uv ".round(@$daily->atm->surf_air->radiation->uv_clear_sky_max_code, 1) : "";
			$icon = preg_replace(array('/^1$/', '/^2$/', '/^3$/', '/^4$/', '/^5$/', '/^6$/', '/^7$/', '/^8$/', '/^9$/', '/^10$/', '/^11$/'), array('☀️', '2', '🌤', ':cloud:', '5', '6', '7', '8', '9', '🌫️', '🌦️'), $daily->atm->surf_air->weather->icon_code);
			$embed->addFieldValues(toAusTime($daily->date_utc, 'l jS')." {$icon}", round($daily->atm->surf_air->temp_max_cel, 1)."° / ".round($daily->atm->surf_air->temp_min_cel, 1)."° \n_☔ {$daily->atm->surf_air->precip->any_probability_percent}% {$uv}_", true);
			$i++;
		}
		getMapImg($place);
		$embed->setColor("0x00A9FF")
			->setTimestamp()
			->setImage("attachment://map-of-{$place['filename']}.png")
			->setFooter("Bureau of Meteorology", "attachment://BOM.png");
			
		$builder = MessageBuilder::new()
			->addEmbed($embed)
			->addFile("../Media/Maps/{$place['filename']}.png", "map-of-{$place['filename']}.png")
			->addFile("../Media/Maps/BOM.png", "BOM.png");
		
		return $message->channel->sendMessage($builder);
		
	}
	
?>
<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;

	function Weather($message, $args) {
		
		global $discord;
		
		$place = getLocale($args);
		if (!$place) { return $message->channel->sendMessage("No location found"); }
		$location = json_decode(file_get_contents("https://api.beta.bom.gov.au/apikey/v1/locations/places/details/{$place['type']}/{$place['id']}?filter=nearby_type:bom_stn"));	
		if (empty($location->place->location_hierarchy->nearest->id)) { return $message->channel->sendMessage("No weather for location"); }	
		$place += array (
			"district" 	=> $location->place->location_hierarchy->public_district->description,
			"state" 	=> $location->place->location_hierarchy->region[1]->description,
			"obsid" 	=> $location->place->location_hierarchy->nearest->id,
		);
		if (preg_match("/(NTC AWS|PYLON|JETTY|RMYS)/", $location->place->location_hierarchy->nearest->name)) {
			foreach ($location->place->location_hierarchy->nearby as $stations) {
				if (!preg_match("/(NTC AWS|PYLON|JETTY|RMYS)/", $stations->name)) {
					$place['obsid'] = $stations->id;
					break;
				}
			}
		}
		$uv = json_decode(file_get_contents("https://api.beta.bom.gov.au/apikey/v1/forecasts/3hourly/{$place['forecast']}?timezone=Australia%2FMelbourne"));
		$temp = array (
			"uv"		=> round($uv->fcst[0]->{'3hourly'}[0]->atm->surf_air->radiation->uv_clear_sky_code, 2),
			"cloudper"	=> $uv->fcst[0]->{'3hourly'}[0]->atm->surf_air->cloud_amt_avg_percent,
			"rainper"	=> $uv->fcst[0]->{'3hourly'}[0]->atm->surf_air->precip->precip_any_probability_percent
		);
		$obs = json_decode(file_get_contents("https://api.beta.bom.gov.au/apikey/v1/observations/latest/{$place['obsid']}/atm/surf_air?include_qc_results=false"));
		$temp += array(
			"stn"		=> $obs->stn->identity->bom_stn_name,
			"temp" 		=> $obs->obs->temp->dry_bulb_1min_cel,
			"feels" 	=> $obs->obs->temp->apparent_1min_cel,
			"max" 		=> $obs->obs->temp->dry_bulb_max_cel,
			"min" 		=> $obs->obs->temp->dry_bulb_min_cel,
			"humidity" 	=> $obs->obs->temp->rel_hum_percent,
			"wind" 		=> round(($obs->obs->wind->speed_10m_mps*3.6), 1),
			"gusts" 	=> round(($obs->obs->wind->gust_speed_10m_mps*3.6), 1),
			"direction"	=> $obs->obs->wind->dirn_10m_ord,
			"rain"		=> $obs->obs->precip->since_0900lct_total_mm,
			"vis" 		=> round(($obs->obs->visibility->horiz_m/1000), 1),
		);
		getMapImg($place);
		$embed = $discord->factory(Embed::class);
		$embed->setTitle("{$place['name']}, {$place['state']}")
			->setDescription("{$place['district']} - {$place['postcode']} - {$temp['stn']}")
			->addFieldValues("Temp", "{$temp['temp']}°", true)
			->addFieldValues("Feels", "{$temp['feels']}°", true)
			->addFieldValues("Max / Min", "{$temp['max']}° / {$temp['min']}°", true)
			->addFieldValues("Wind", "{$temp['wind']}kph ".preg_replace(array('/^N$/', '/^S$/', '/^E$/', '/^W$/', '/^.?NE$/', '/^.?SE$/', '/^.?SW$/', '/^.?NW$/', '/^CALM$/'), array('↓', '↑', '←', '→', '↙', '↖', '↗', '↘', ''), $temp['direction']), true)
			->addFieldValues("Gusts", "{$temp['gusts']}kph", true)
			->addFieldValues("Humidity", "{$temp['humidity']}%", true)
			->addFieldValues("Rain", "{$temp['rain']}mm ({$temp['rainper']}%)", true)
			->addFieldValues("UV", $temp['uv'], true)
			->addFieldValues("Visibility", "{$temp['vis']}km", true)
			->setImage("attachment://map-of-{$place['filename']}.png")
			->setColor("F1C40F")
			->setTimestamp()
			->setFooter("Bureau of Meteorology", "attachment://BOM.png");
			
		$builder = MessageBuilder::new()
			->addEmbed($embed)
			->addFile("../Media/Maps/{$place['filename']}.png", "map-of-{$place['filename']}.png")
			->addFile("../Media/Maps/BOM.png", "BOM.png");
		
		return $message->channel->sendMessage($builder);
		
	}

?>
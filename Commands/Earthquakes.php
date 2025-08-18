<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;
	
	function Earthquakes() {
		
		if (getenv('BETA') === 'true') { return; }
		
		global $discord;
		
		$guild = $discord->guilds->get('id', '232691831090053120');
		$channel = $guild->channels->get('id', '232691831090053120');
		
		$currentTime = new DateTime('now', new DateTimeZone('UTC'));
		$priorTime = clone $currentTime;
		$priorTime->sub(new DateInterval('PT2M'));
		$currentFormatted = $currentTime->format('Y-m-d\TH:i:s\Z');
		$priorFormatted = $priorTime->format('Y-m-d\TH:i:s\Z');
		
		$url = "https://ui.earthquakes.ga.gov.au/geoserver/earthquakes/wfs?service=WFS&request=getfeature&typeNames=earthquakes:earthquakes&outputFormat=application/json&CQL_FILTER=display_flag=%27Y%27%20AND%20origin_time%20BETWEEN%20{$priorFormatted}%20AND%20{$currentFormatted}%20AND%20located_in_australia=%27Y%27&sortBy=origin_time%20D";
		
		$headers = [
			'accept: */*',
			'accept-language: en-AU,en;q=0.9',
			'dnt: 1',
			'origin: https://earthquakes.ga.gov.au',
			'priority: u=1, i',
			'referer: https://earthquakes.ga.gov.au/',
			'sec-ch-ua: "Not;A=Brand";v="99", "Brave";v="139", "Chromium";v="139"',
			'sec-ch-ua-mobile: ?0',
			'sec-ch-ua-platform: "Windows"',
			'sec-fetch-dest: empty',
			'sec-fetch-mode: cors',
			'sec-fetch-site: same-site',
			'sec-gpc: 1',
			'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36',
		];
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		$responseData = json_decode($result);

		if ($responseData->totalFeatures >= 1) {
			
			foreach ($responseData->features as $quakes) {
				
				getMapImg($quakes->geometry->coordinates[1].",".$quakes->geometry->coordinates[0], true, $quakes->properties->event_id);
				
				$embed = $discord->factory(Embed::class);
				$embed->setTitle("⚠️ Earthquake Alert ⚠️")
					->setDescription("Magnitude **".round($quakes->properties->preferred_magnitude, 1)."** earthquake detected at a depth of **{$quakes->properties->depth} km**\n\nLocation: **{$quakes->properties->description}**")
					->setImage("attachment://map-of-{$quakes->properties->event_id}.png")
					->setColor(getenv('COLOUR'))
					->setURL("https://earthquakes.ga.gov.au/event/{$quakes->properties->event_id}")
					->setFooter("Geoscience Australia");
					
				$builder = MessageBuilder::new()
					->addEmbed($embed)
					->addFile("/Media/Maps/{$quakes->properties->event_id}.png", "map-of-{$quakes->properties->event_id}.png");
				
				$channel->sendMessage($builder);
				
			}
			
		}
	
	}

?>
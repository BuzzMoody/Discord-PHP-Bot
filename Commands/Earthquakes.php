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
		$priorTime->sub(new DateInterval('P1D'));
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
				
				$quakeID = $quakes->properties->event_id;
				$magnitude = round($quakes->properties->preferred_magnitude, 1);
				$depth = round($quakes->properties->depth, 1);
				$location = $quakes->properties->description;
				
				if (checkEQ($quakeID) || ($magnitude < 3.5 && strpos($location, 'VIC') === false)) { return; }
				
				getMapImg($quakes->geometry->coordinates[1].",".$quakes->geometry->coordinates[0], true, $quakeID);
				
				$epiTimeZ = $quakes->properties->origin_time;
				$epiTime = new DateTime($epiTimeZ, new DateTimeZone('UTC'));
				$epiTime->setTimezone(new DateTimeZone('Australia/Melbourne'));
				
				$embed = $discord->factory(Embed::class);
				$embed->setAuthor("Earthquake Alert 🫨", "https://www.ga.gov.au/__data/assets/image/0005/123368/GA_logo_180x180.png", "https://earthquakes.ga.gov.au/event/{$quakeID}")
					->setDescription("Magnitude **{$magnitude}** earthquake detected at a depth of **{$depth} km**\n\nLocation: **{$location}**\nTime: **{$epiTime->format('g:i:s A')}**")
					->setImage("attachment://map-of-{$quakeID}.png")
					->setColor(getenv('COLOUR'));
					
				$builder = MessageBuilder::new()
					->addEmbed($embed)
					->addFile("/Media/Maps/{$quakeID}.png", "map-of-{$quakeID}.png");
				
				$channel->sendMessage($builder);
				
				writeEQ($quakeID);
				
			}
			
		}
	
	}
	
	function checkEQ($id) {
		
		$mysqli = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_KEY'), getenv('DB_NAME'));
		$checkEQDB = $mysqli->query("SELECT * FROM `earthquakes` WHERE quakeid='{$id}'");
		$mysqli->close();
		if ($checkEQDB->num_rows > 0) {
			return true;
		}
		else { return false; }
		
	}
	
	function writeEQ($id) {
		
		$mysqli = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_KEY'), getenv('DB_NAME'));
		$result = $mysqli->query("INSERT INTO `earthquakes` (quakeid) VALUES ('{$id}');");
		$mysqli->close();
		
	}

?>
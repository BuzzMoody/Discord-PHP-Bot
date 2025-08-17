<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	
	function Earthquakes() {
		
		global $discord;
		
		$guild = $discord->guilds->get('id', '232691831090053120');
		$channel = $guild->channels->get('id', '232691831090053120');
		
		$currentTime = new DateTime('now', new DateTimeZone('UTC'));
		$yesterdayTime = clone $currentTime;
		$yesterdayTime->sub(new DateInterval('P1D'));
		$currentFormatted = $currentTime->format('Y-m-d\TH:i:s\Z');
		$yesterdayFormatted = $yesterdayTime->format('Y-m-d\TH:i:s\Z');
		
		$embed = $discord->factory(Embed::class);
		$embed->setTitle("⚠️ Earthquake Alert ⚠️");
		
		$url = "https://ui.earthquakes.ga.gov.au/geoserver/earthquakes/wfs?service=WFS&request=getfeature&typeNames=earthquakes:earthquakes&outputFormat=application/json&CQL_FILTER=display_flag=%27Y%27%20AND%20origin_time%20BETWEEN%20{$yesterdayFormatted}%20AND%20{$currentFormatted}%20AND%20located_in_australia=%27Y%27&sortBy=origin_time%20D";
		
		$headers = [
			'accept' => '*/*',
			'accept-language' => 'en-AU,en;q=0.9',
			'dnt' => '1',
			'origin' => 'https://earthquakes.ga.gov.au',
			'priority' => 'u=1, i',
			'referer' => 'https://earthquakes.ga.gov.au/',
			'sec-ch-ua' => '"Not;A=Brand";v="99", "Brave";v="139", "Chromium";v="139"',
			'sec-ch-ua-mobile' => '?0',
			'sec-ch-ua-platform' => '"Windows"',
			'sec-fetch-dest' => 'empty',
			'sec-fetch-mode' => 'cors',
			'sec-fetch-site' => 'same-site',
			'sec-gpc' => '1',
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36',
		];
		
		$client->get($url, $headers)->then(
			function (ResponseInterface $response) use ($embed) {
				
				$responseBody = $response->getBody();
				$responseData = json_decode($responseBody);
				
				print_r($responseData);
				
			},
			function (Exception $e) use ($message) {
				$channel->sendMessage('Error: ' . $e->getMessage());
			}
		);
	
	}

?>
<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Builders\MessageBuilder;
	use Discord\Parts\Channel\Attachment;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	
	class BotUtils {
	
		private $discord;
		private $pdo;
		
		public function __construct($discord, PDO $pdo) {
			
			$this->discord = $discord;
			$this->pdo = $pdo;
			
		}
		
		public function isAdmin(string $userID): bool {
			
			if ($userID == '232691181396426752') { return true; }
			$testGuild = $this->discord->guilds->get('id', '232691831090053120');
			$testMember = $testGuild->members->get('id', $userID);
			return $testMember->roles->has('232692759557832704');
			
		}
		
		public function simpleEmbed($authName, $authIMG, $text, $message, $reply = false, $authURL = null) {
			
			$embed = $this->discord->factory(Embed::class);
			$embed->setAuthor($authName, $authIMG, $authURL)
				->setColor(getenv('COLOUR'))
				->setDescription($text);

			if (!$reply) { return $message->channel->sendEmbed($embed); }
			
			$builder = MessageBuilder::new()
				->addEmbed($embed)
				->setReplyTo($message);
				
			if (str_starts_with($authIMG, "attachment://")) {
				$fileIMG = substr($authIMG, strlen('attachment://'));
				$builder->addFile("/Media/{$fileIMG}", $fileIMG);
			}
			
			return $message->channel->sendMessage($builder);
		
		}
		
		public function Earthquakes() {
		
			if (getenv('BETA') === 'true') { return; }
			
			$guild = $this->discord->guilds->get('id', '232691831090053120');
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

			if (@$responseData->totalFeatures >= 1) {
				
				foreach ($responseData->features as $quakes) {
					
					$quakeID = $quakes->properties->event_id;
					$magnitude = round($quakes->properties->preferred_magnitude, 1);
					$depth = round($quakes->properties->depth, 1);
					$location = $quakes->properties->description;
					
					if ($this->checkEQ($quakeID) || ($magnitude < 3.5 && strpos($location, 'VIC') === false)) { return; }
					
					$this->getMapImg($quakes->geometry->coordinates[1].",".$quakes->geometry->coordinates[0], true, $quakeID);
					
					$epiTimeZ = $quakes->properties->origin_time;
					$epiTime = new DateTime($epiTimeZ, new DateTimeZone('UTC'));
					$epiTime->setTimezone(new DateTimeZone('Australia/Melbourne'));
					
					$embed = $this->discord->factory(Embed::class);
					$embed->setAuthor("Earthquake Alert 🫨", "https://www.ga.gov.au/__data/assets/image/0005/123368/GA_logo_180x180.png", "https://earthquakes.ga.gov.au/event/{$quakeID}")
						->setDescription("Magnitude **{$magnitude}** earthquake detected at a depth of **{$depth} km**\n\nLocation: **{$location}**\nTime: **{$epiTime->format('g:i:s A')}**")
						->setImage("attachment://map-of-{$quakeID}.png")
						->setColor(getenv('COLOUR'));
						
					$builder = MessageBuilder::new()
						->addEmbed($embed)
						->addFile("/Media/Maps/{$quakeID}.png", "map-of-{$quakeID}.png");
					
					$channel->sendMessage($builder);
					
					$this->writeEQ($quakeID);
					
				}
				
			}
		
		}
	
		private function checkEQ(string $id): bool {
			
			$stmt = $this->pdo->prepare("SELECT EXISTS(SELECT 1 FROM earthquakes WHERE quakeid = :id)");
			$stmt->execute([':id' => $id]);
			return (bool) $stmt->fetchColumn();
			
		}
		
		private function writeEQ(string $id) {

			$stmt = $this->pdo->prepare("INSERT INTO earthquakes (quakeid) VALUES (:id)");
			$stmt->execute([':id' => $id]);

		}
		
		public function getMapImg($place, $eq = false, $name = "") {
		
			if ($eq) {
				if (!file_exists("/Media/Maps/{$name}.png")) { 
					file_put_contents("/Media/Maps/{$name}.png", file_get_contents("https://maps.googleapis.com/maps/api/staticmap?key=".getenv('MAPS_API_KEY')."&center=-27.918284,133.995323&zoom=4&size=600x450&scale=1&markers=size:mid|color:red|{$place}"));
				}
			}
			else {
				if (!file_exists("/Media/Maps/{$place['filename']}.png")) { 
					file_put_contents("/Media/Maps/{$place['filename']}.png", file_get_contents("https://maps.googleapis.com/maps/api/staticmap?key=".getenv('MAPS_API_KEY')."&center=".str_replace(' ', '%20', $place['name']).",%20".str_replace(' ', '%20', $place['state'])."&zoom=9&size=640x300&scale=2&markers=size:mid%7Ccolor:red%7C".str_replace(' ', '%20', $place['name']))); 
				}
			}
			
		}
		
		public function toAusTime($time, $format = 'jS F: G:i', $countdown = false, $offset = 'UTC', $relative = false) {
		
			if ($relative) {
				$dateTimeWithOffset = $time . $offset;
				$dateTime = new DateTime($dateTimeWithOffset);
				$dateTime->setTimezone(new DateTimeZone('Australia/Melbourne'));
				return $dateTime->format($format);	
			}
			else if ($countdown) {
				$currTime = new DateTime();
				$diffTime = $currTime->diff($dateTime);
				$countTime = "";
				if ($diffTime->days > 0) { $countTime .= "{$diffTime->days} days, "; }
				if ($diffTime->h > 0) { $countTime .= "{$diffTime->h} hrs, "; }
				if ($diffTime->i > 0) { $countTime .= "{$diffTime->i} mins"; }
				return $dateTime->format($format)." ({$countTime})";
			}
			else {
				$dateTime = new DateTime($time, new DateTimeZone($offset));
				$dateTime->setTimezone(new DateTimeZone('Australia/Melbourne'));
				return $dateTime->format($format);	
			}
			
		}
		
		public function getLocale($locale) {
		
			$locale = (empty($locale)) ? "Highett" : str_replace(' ', '+', trim($locale));
			$results = json_decode(@file_get_contents("https://api.beta.bom.gov.au/apikey/v1/locations/places/autocomplete?name={$locale}&limit=1&website-sort=true&website-filter=true"));
			if (empty($results)) { return false; }
			$place = array(
				"name" 		=> $results->candidates[0]->name,
				"state" 	=> $results->candidates[0]->state,
				"filename"	=> str_replace(array(' ', '(', ')'), array('-', '', ''), $results->candidates[0]->name),
				"type"		=> $results->candidates[0]->type,
				"postcode" 	=> $results->candidates[0]->postcode->name,
				"forecast"	=> $results->candidates[0]->gridcells->forecast->x."/".$results->candidates[0]->gridcells->forecast->y,
				"id"		=> $results->candidates[0]->id
			);
			return $place;
		
		}
		
		public function SearchFunc($type, $message, $args) {
	
			if (empty($args)) { return $this->simpleEmbed("Google Search", "https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/24px-Google_%22G%22_logo.svg.png", "Invalid syntax used. Please provide search terms.", $message, true, null); }
			
			$api_url = ($type == 'google') ? "https://customsearch.googleapis.com/customsearch/v1?key=".getenv('SEARCH_API_KEY')."&cx=017877399714631144452:hlos9qn_wvc&googlehost=google.com.au&num=1&q=".str_replace(' ', '%20', $args) : "https://customsearch.googleapis.com/customsearch/v1?key=".getenv('SEARCH_API_KEY')."&cx=017877399714631144452:0j02gfgipjq&googlehost=google.com.au&searchType=image&excludeTerms=youtube&imgSize=xxlarge&safe=off&num=1&fileType=jpg,png,gif&q=".str_replace(' ', '%20', $args)."%20-site:facebook.com%20-site:tiktok.com%20-site:instagram.com";
			
			try {
				$search = file_get_contents($api_url);
				if ($search === false) { return null; }
				$return = json_decode($search);		
				if ($return === null) { return null; }	
			} catch (Exception $e) {
				return null;
			}
			
			if ($return->searchInformation->totalResults == 0) { return $this->simpleEmbed("Google Search", "https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/24px-Google_%22G%22_logo.svg.png", "No results found for *{$args}*.", $message, true, null); }
			
			return ($type == 'google') ? $message->channel->sendMessage("{$return->items[0]->title}: {$return->items[0]->link}") : $message->channel->sendMessage($return->items[0]->link);
		
		}
	
	}
	
?>
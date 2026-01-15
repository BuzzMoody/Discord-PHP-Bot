<?php

	use Discord\Parts\Channel\Message;
	use Discord\Parts\Embed\Embed;
	use Discord\Builders\MessageBuilder;
	use Discord\Parts\Channel\Attachment;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	
	class BotUtils {
		
		public function __construct(private Discord\Discord $discord, private PDO $pdo) { }
		
		public function betaCheck(): bool {
			
			if (getenv('BETA') !== 'true') return false;
			
			$betaGuild = $this->discord->guilds->get('id', '232691831090053120');
			$betaMember = $betaGuild->members->get('id', '274805663614369793');

			return $betaMember->status !== null && $betaMember->status !== 'offline';

		}
		
		public function isAdmin(string $userID): bool {
			
			if ($userID == '232691181396426752') { return true; }
			$testGuild = $this->discord->guilds->get('id', '232691831090053120');
			$testMember = $testGuild->members->get('id', $userID);
			return $testMember->roles->has('232692759557832704');
			
		}
		
		public function simpleEmbed(string $authName, string $authIMG, string $text, Message $message, bool $reply = false, string $authURL = ''): void {
			
			$authURL = ($authURL == '') ? null : $authURL;
			
			$embed = $this->discord->factory(Embed::class);
			$embed->setAuthor($authName, $authIMG, $authURL)
				->setColor(getenv('COLOUR'))
				->setDescription($text);

			if (!$reply) { 
				$message->channel->sendEmbed($embed); 
				return;
			}
			
			$builder = MessageBuilder::new()
				->addEmbed($embed)
				->setReplyTo($message);
				
			if (str_starts_with($authIMG, "attachment://")) {
				$fileIMG = substr($authIMG, strlen('attachment://'));
				$builder->addFile("/Media/{$fileIMG}", $fileIMG);
			}
			
			$message->channel->sendMessage($builder);
		
		}
		
		public function checkNews(): void {
			
			if ($this->betaCheck()) { return; }

			$xml = simplexml_load_file('https://www.theverge.com/rss/ces/index.xml');
			$existingNews = file_exists('/Media/news.txt') ? file('/Media/news.txt', FILE_IGNORE_NEW_LINES) : [];
			
			$guild = $this->discord->guilds->get('id', '232691831090053120');
			$channel = $guild->channels->get('id', '1457664461358764131');

			foreach ($xml->entry as $item) {
				$title = trim((string)$item->title);
				$link = (string)$item->link['href'];
				if (!in_array($title, $existingNews)) {
					$channel->sendMessage($link);
					file_put_contents('/Media/news.txt', $title . PHP_EOL, FILE_APPEND);
					$existingNews[] = $title;
				}
			}
			
		}
		
		public function checkEarthquakes(): void {
		
			if ($this->betaCheck()) { return; }
			
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
					
					$epiTime = (new DateTimeImmutable($quakes->properties->origin_time, new DateTimeZone('UTC')))
        ->setTimezone(new DateTimeZone('Australia/Melbourne'));
					
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
			
			$stmt = $this->pdo->prepare("SELECT COUNT(*) FROM earthquakes WHERE quakeid = :id");
			$stmt->execute([':id' => (string)$id]);
			return (bool) $stmt->fetchColumn();
			
		}
		
		private function writeEQ(string $id): void {

			$stmt = $this->pdo->prepare("INSERT INTO earthquakes (quakeid) VALUES (:id)");
			$stmt->execute([':id' => (string)$id]);

		}
		
		public function getMapImg(array|string $place, bool $eq = false, string $name = ''): void {
		
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
		
		public function toAusTime($time, $format = 'jS F: G:i', $countdown = false, $offset = 'UTC', $relative = false): string {
		
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
		
		public function getLocale(string $locale): array {
		
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
		
		public function SearchFunc(string $type, Message $message, string $args): void {
	
			if (empty($args)) { 
				$this->simpleEmbed("Google Search", "https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/24px-Google_%22G%22_logo.svg.png", "Invalid syntax used. Please provide search terms.", $message, true);
				return;
			}
			
			$api_url = ($type == 'google') ? "https://customsearch.googleapis.com/customsearch/v1?key=".getenv('SEARCH_API_KEY')."&cx=017877399714631144452:hlos9qn_wvc&googlehost=google.com.au&num=1&q=".str_replace(' ', '%20', $args) : "https://customsearch.googleapis.com/customsearch/v1?key=".getenv('SEARCH_API_KEY')."&cx=017877399714631144452:0j02gfgipjq&googlehost=google.com.au&searchType=image&excludeTerms=youtube&imgSize=xxlarge&safe=off&num=1&fileType=jpg,png,gif&q=".str_replace(' ', '%20', $args)."%20-site:facebook.com%20-site:tiktok.com%20-site:instagram.com";
			
			try {
				$search = file_get_contents($api_url);
				if ($search === false) { return; }
				$return = json_decode($search);		
				if ($return === null) { return; }	
			} catch (Exception $e) {
				return;
			}
			
			if ($return->searchInformation->totalResults == 0) { 
				$this->simpleEmbed("Google Search", "https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/24px-Google_%22G%22_logo.svg.png", "No results found for *{$args}*.", $message, true);
				return;
			}
			
			$content = ($type == 'google') ? "{$return->items[0]->title}: {$return->items[0]->link}" : $return->items[0]->link;

			$message->channel->sendMessage($content);
		
		}
		
		public function checkReminders(): void {
	
			$time = time();
			$stmt = $this->pdo->prepare("SELECT userid, channelid, messageid, time FROM reminders WHERE time < :time");
			$stmt->execute([':time' => $time]);
			$reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
			if (count($reminders) > 0) {
				
				$this->pdo->beginTransaction();
				$deleteStmt = $this->pdo->prepare("DELETE FROM reminders WHERE time = :time");
				
				foreach ($reminders as $row) {

					$guild = $this->discord->guilds->get('id', '232691831090053120');
					$channel = $guild->channels->get('id', $row['channelid']);
					$channel->messages->fetch($row['messageid'])->then(function ($message) use ($row) {
						$this->simpleEmbed("Chat Reminders", "attachment://bot.webp", "<@{$row['userid']}> Here is your reminder: https://discord.com/channels/232691831090053120/{$row['channelid']}/{$row['messageid']}", $message, true); 
					});
					$deleteStmt->execute([':time' => $row['time']]);
				
				}
				
				$this->pdo->commit(); 
				
			}
		
		}
		
		public function filterUsers(Message $message): string {

			$content = $message->content;
			
			if ($message->mentions->count() > 0) {	
				foreach ($message->mentions as $mention) {
					$member = $message->guild->members->get('id', $mention->id);
					if ($member) {
						$displayName = (empty($member->nick)) ? $member->user->username : $member->nick;
						$content = str_replace("<@{$mention->id}>", "@{$displayName}", $content);
					}
				}
			}

			return $content;
			
		}
		
		public function checkTrades(): void {
			
			if ($this->betaCheck()) { return; }
			
			$ids = file_exists('/Media/trades.txt') ? file('/Media/trades.txt',  FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
			$guild = $this->discord->guilds->get('id', '232691831090053120');
			$channel = $guild->channels->get('id', '1352902587837583370');
			$http = new Browser();
			
			$http->get("https://aflapi.afl.com.au/liveblog/afl/164/EN?maxResults=3")->then(
				function (ResponseInterface $response) use ($ids, $channel) {
					$output = json_decode($response->getBody());
					
					foreach ($output->entries as $article) {
						if (in_array($article->id, $ids) || is_null($article->comment)) {
							continue;
						}
						
						file_put_contents('/Media/trades.txt', $article->id . PHP_EOL, FILE_APPEND);
						
						preg_match("/<p class=\"live-blog-post-trade__heading-section__label\">(.+)<p>/m", $article->comment, $trade_type);
						preg_match_all("/<h2 class=\"live-blog-post-trade__title\">\s*(.*?)\s*<span.+?> (receive|give)s?:<\/span>/ms", $article->comment, $teams);
						preg_match_all("/<p class=\"live-blog-post-trade__text\">\s*(.*?)\s*<\/p>/ms", $article->comment, $items);
						preg_match("/<h2 class=\"live-blog-post-article__title\">(.+?)<\/h2>.+<p class=\"live-blog-post-article__text\">(.+?)<\/p>/ms", $article->comment, $article_text);
						preg_match("/, (https:\/\/resources\.afl\.com\.au\/photo-resources\/.+\.(jpg|png)\?width=2128&height=1200)/", $article->comment, $image);
						preg_match("/href=\"(\/news\/(.+?))\".*target=\"_blank\"/s", $article->comment, $url);
						
						$embed = $this->discord->factory(Embed::class);
						$embed->setTitle($article->title)
							->setAuthor("AFL Trade Radio", "https://www.afl.com.au/resources/v5.37.23/afl/favicon-32x32.png")
							->setDescription($article_text[1].". ".$article_text[2])
							->setURL("https://www.afl.com.au{$url[1]}")
							->setColor(getenv('COLOUR'))
							->setImage($image[1])
							->setFooter($trade_type[1])
							->setTimestamp();
						
						foreach ($teams[1] as $i => $team) {
							$embed->addFieldValues("{$team} {$teams[2][$i]}:", $items[1][$i]);
						}
						
						$channel->sendEmbed($embed);
					}
					
				},
				function (Exception $e) {
					echo $e->getMessage()."\n";
				}
			);
			
		}
	
	}
	
?>
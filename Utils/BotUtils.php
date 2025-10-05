<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Builders\MessageBuilder;
	use Discord\Parts\Channel\Attachment;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	
	class BotUtils {
	
		private $discord;
		private $pdo;
		
		private const DOTA_LEVELS = [
			0, 0, 240, 640, 1160, 1760, 2440, 3200, 4000, 4900, 5900, 7000, 8200, 9500, 10900, 12400, 14000, 15700, 17500, 19400, 21400, 23600, 26000, 28600, 31400, 34400, 38400, 43400, 49400, 56400, 63900
		];
		
		private const DOTA_HEROES = [
			1 => "Anti-Mage", 2 => "Axe", 3 => "Bane", 4 => "Bloodseeker", 5 => "Crystal Maiden", 6 => "Drow Ranger", 7 => "Earthshaker", 8 => "Juggernaut", 9 => "Mirana", 11 => "Shadow Fiend", 10 => "Morphling", 12 => "Phantom Lancer", 13 => "Puck", 14 => "Pudge", 15 => "Razor", 16 => "Sand King", 17 => "Storm Spirit", 18 => "Sven", 19 => "Tiny", 20 => "Vengeful Spirit", 21 => "Windranger", 22 => "Zeus", 23 => "Kunkka", 25 => "Lina", 31 => "Lich", 26 => "Lion", 27 => "Shadow Shaman", 28 => "Slardar", 29 => "Tidehunter", 30 => "Witch Doctor", 32 => "Riki", 33 => "Enigma", 34 => "Tinker", 35 => "Sniper", 36 => "Necrophos", 37 => "Warlock", 38 => "Beastmaster", 39 => "Queen of Pain", 40 => "Venomancer", 41 => "Faceless Void", 42 => "Skeleton King", 43 => "Death Prophet", 44 => "Phantom Assassin", 45 => "Pugna", 46 => "Templar Assassin", 47 => "Viper", 48 => "Luna", 49 => "Dragon Knight", 50 => "Dazzle", 51 => "Clockwerk", 52 => "Leshrac", 53 => "Nature's Prophet", 54 => "Lifestealer", 55 => "Dark Seer", 56 => "Clinkz", 57 => "Omniknight", 58 => "Enchantress", 59 => "Huskar", 60 => "Night Stalker", 61 => "Broodmother", 62 => "Bounty Hunter", 63 => "Weaver", 64 => "Jakiro", 65 => "Batrider", 66 => "Chen", 67 => "Spectre", 69 => "Doom", 68 => "Ancient Apparition", 70 => "Ursa", 71 => "Spirit Breaker", 72 => "Gyrocopter", 73 => "Alchemist", 74 => "Invoker", 75 => "Silencer", 76 => "Outworld Devourer", 77 => "Lycanthrope", 78 => "Brewmaster", 79 => "Shadow Demon", 80 => "Lone Druid", 81 => "Chaos Knight", 82 => "Meepo", 83 => "Treant Protector", 84 => "Ogre Magi", 85 => "Undying", 86 => "Rubick", 87 => "Disruptor", 88 => "Nyx Assassin", 89 => "Naga Siren", 90 => "Keeper of the Light", 91 => "IO", 92 => "Visage", 93 => "Slark", 94 => "Medusa", 95 => "Troll Warlord", 96 => "Centaur Warrunner", 97 => "Magnus", 98 => "Timbersaw", 99 => "Bristleback", 100 => "Tusk", 101 => "Skywrath Mage", 102 => "Abaddon", 103 => "Elder Titan", 104 => "Legion Commander", 106 => "Ember Spirit", 107 => "Earth Spirit", 108 => "Abyssal Underlord", 109 => "Terrorblade", 110 => "Phoenix", 105 => "Techies", 111 => "Oracle", 112 => "Winter Wyvern", 113 => "Arc Warden", 114 => "Monkey King", 119 => "Dark Willow", 120 => "Pangolier", 121 => "Grimstroke", 123 => "Hoodwink", 126 => "Void Spirit", 128 => "Snapfire", 129 => "Mars", 131 => "Ringmaster", 135 => "Dawnbreaker", 136 => "Marci", 137 => "Primal Beast", 138 => "Muerta", 145 => "Kez"
		];
		
		private const DOTA_GAMEMODES = [
			0 => "Unknown", 1 => "All Pick", 2 => "Captains Mode", 3 => "Random Draft", 4 => "Single Draft", 5 => "All Random", 6 => "Intro", 7 => "Diretide", 8 => "Reverse Captains Mode", 9 => "Greeviling", 10 => "Tutorial", 11 => "Mid Only", 12 => "Least Played", 13 => "Limited Heroes", 14 => "Compendium Matchmaking", 15 => "Custom", 16 => "Captains Draft", 17 => "Balanced Draft", 18 => "Ability Draft", 19 => "Event", 20 => "All Random Death Match", 21 => "1v1 Mid", 22 => "All Draft", 23 => "Turbo", 24 => "Mutation", 25 => "Coaches Challenge"
		];
		
		public function __construct(Discord $discord, PDO $pdo) {
			
			$this->discord = $discord;
			$this->pdo = $pdo;
			
		}
		
		public function isAdmin(string $userID): bool {
			
			if ($userID == '232691181396426752') { return true; }
			$testGuild = $this->discord->guilds->get('id', '232691831090053120');
			$testMember = $testGuild->members->get('id', $userID);
			return $testMember->roles->has('232692759557832704');
			
		}
		
		public function simpleEmbed($authName, $authIMG, $text, $message, $reply = false, $authURL = null): Message|MessageBuilder {
			
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
			
			$stmt = $this->pdo->prepare("SELECT COUNT(*) FROM earthquakes WHERE quakeid = :id)");
			$stmt->execute([':id' => (string)$id]);
			return (bool) $stmt->fetchColumn();
			
		}
		
		private function writeEQ(string $id): void {

			$stmt = $this->pdo->prepare("INSERT INTO earthquakes (quakeid) VALUES (:id)");
			$stmt->execute([':id' => (string)$id]);

		}
		
		public function getMapImg($place, $eq = false, $name = ""): void {
		
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
		
		public function getLocale($locale): string {
		
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
		
		public function checkReminders() {
	
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
						$this->simpleEmbed("Chat Reminders", "attachment://bot.webp", "<@{$row['userid']}> Here is your reminder: https://discord.com/channels/232691831090053120/{$row['channelid']}/{$row['messageid']}", $message, true, null); 
					});
					$deleteStmt->execute([':time' => $row['time']]);
				
				}
				
				$this->pdo->commit(); 
				
			}
		
		}
		
		public function filterUsers($message): string {

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
		
		public function checkDota() {
			
			if (getenv('BETA') === 'true') { return; }
			
			$date = new DateTime('now');
			$current_hour = (int)$date->format('G');

			if ($current_hour >= 10 || $current_hour <= 2) {

				$ids = array(
					array("232691181396426752", "54716121", "Buzz"), 
					array("381596223435702282", "33939542", "Dan"), 
					array("276222661515018241", "77113202", "Hassler"), 
				);
				
				$games = 0;

				for ($i = 0; $i < count($ids); $i++) {

					$url = "https://api.opendota.com/api/players/{$ids[$i][1]}/recentMatches";
					
					$content = @file_get_contents($url);
					
					if ($content === FALSE) { return; }

					$response = json_decode($content);

					$details[$i]['user'] = $ids[$i][1];
					$details[$i]['matchid'] = '';

					if ($this->checkNew($details[$i]['user'], $response[0]->match_id)) {
						
						echo "User: {$ids[$i][2]} has a new game\n";

						$keyz = array_keys(array_combine(array_keys($details), array_column($details, 'matchid')), $response[0]->match_id);	
						$details[$i]['matchid'] = $response[0]->match_id;
						
						if (
							$i == 0 || 
							$i > 0 && @$keyz[0] == 1 && $response[0]->match_id == $details[($i-1)]['matchid'] && count($details[($i-1)]) > 2 || 
							$i > 0 && @!$keyz[0] && $response[0]->match_id == $details[($i-1)]['matchid'] ||
							$i > 0 && @!$keyz[0] && $details[($i-1)]['matchid'] == null
						) {
						
							$details[$i]['matchid'] = $response[0]->match_id;
							$details[$i]['new'] = true;
							$details[$i]['discord'] = $ids[$i][0];
							$details[$i]['name'] = $ids[$i][2];
							$details[$i]['team'] = ($response[0]->player_slot <= 127) ? "Radiant" : "Dire";
							$details[$i]['win'] = ($response[0]->radiant_win == true && $details[$i]['team'] == "Radiant" || $response[0]->radiant_win == false && $details[$i]['team'] == "Dire") ? "Won" : "Lost";
							$details[$i]['hero'] = self::DOTA_HEROES[$response[0]->hero_id];
							$details[$i]['stats'] = array("Kills" => $response[0]->kills, "Deaths" => $response[0]->deaths, "Assists" => $response[0]->assists,"HeroDMG" => number_format($response[0]->hero_damage), "TowerDMG" => number_format($response[0]->tower_damage), "XPM" => $response[0]->xp_per_min, "GPM" => number_format($response[0]->gold_per_min), "Heal" => number_format($response[0]->hero_healing));
							$start = $response[0]->start_time;
							$duration = $response[0]->duration;
							$hours = floor($duration / 3600);
							$format = ($hours > 0) ? 'g \h\o\u\r\s i \m\i\n\s' : 'i \m\i\n\s';
							$length = gmdate($format, $duration);
							$mode = self::DOTA_GAMEMODES[$response[0]->game_mode];
							@$matchid = ($response[0]->match_id == null) ? @$matchid : $response[0]->match_id;
							$ranked = ($response[0]->lobby_type == 5 || $response[0]->lobby_type == 6 || $response[0]->lobby_type == 7) ? "Ranked" : "Unranked";
							$games++;
							$this->updateMatch($details[$i]['user'], $response[0]->match_id);
							
						}
						
					}
					
				}

				if ($games > 0) {
					
					$tz = new DateTime("now", new DateTimeZone('Australia/Melbourne'));
					$tz->setTimestamp($start);
					
					$embed = $this->discord->factory(Embed::class);
					$embed->setAuthor("Dota 2 Match Information", "attachment://dota.png", "https://www.opendota.com/matches/{$matchid}")
						->setImage("https://media.licdn.com/dms/image/C5612AQGLKrCEqkHZMw/article-cover_image-shrink_600_2000/0/1636444501645?e=2147483647&v=beta&t=Fd2nbDk9TUmsSm9c5Kt2wq9hP_bH1MxZITTa4pEx1wg")
						->setColor(getenv('COLOUR'));
					
					$embed->addFieldValues("Start Time", $tz->format('g:i A'), true);
					$embed->addFieldValues("Length", $length, true);
					$embed->addFieldValues("Game Mode", "{$ranked} {$mode}", true);
					
					$desc = "";
					
					for ($x = 0; $x < count($details); $x++) {
						if (@$details[$x]['new']) {
							$id = $x;
							$desc .= "<@{$details[$x]['discord']}> **{$details[$x]['win']}** playing as **{$details[$x]['hero']}**";
						$embed->addFieldValues($details[$x]['name'], "{$details[$x]['hero']}\n{$details[$x]['stats']['Kills']} / {$details[$x]['stats']['Deaths']} / {$details[$x]['stats']['Assists']}\n{$details[$x]['team']}", true);
							$embed->addFieldValues("Damage / Heal", "{$details[$x]['stats']['HeroDMG']} dmg\n{$details[$x]['stats']['TowerDMG']} tower\n{$details[$x]['stats']['Heal']} heal\n", true);
							$embed->addFieldValues("Stats", "Lvl ".$this->getLevel(($details[$x]['stats']['XPM'] * ($duration / 60)))."\n".number_format($details[$x]['stats']['XPM'])." xpm\n{$details[$x]['stats']['GPM']} gpm", true);
						}
					}

					$embed->setDescription($desc."\n");
					
					$builder = MessageBuilder::new()
						->addEmbed($embed)
						->addFile("/Media/dota.png", "dota.png");
					
					$guild = $this->discord->guilds->get('id', '232691831090053120');
					$channel = $guild->channels->get('id', '232691831090053120');

					return $channel->sendMessage($builder);
				
				}
				
			}
		
		}
	
		private function getLevel($exp): int {
			
			for ($level = count(self::DOTA_LEVELS) - 1; $level >= 1; $level--) {
				if ($exp >= self::DOTA_LEVELS[$level]) {
					return $level;
				}
			}
			
			return 1;
			
		}
		
		private function updateMatch($id, $matchID): void {
			
			$stmt = $this->pdo->prepare("UPDATE dota2 SET matchid = :matchid WHERE id = :id");
			$stmt->execute([
				'matchid' => (string)$matchID, 
				'id' => (string)$id
			]);
			
		}
		
		private function checkNew($id, $matchID) {
			
			$stmt1 = $this->pdo->prepare("SELECT matchid FROM dota2 WHERE id = :id");
			$stmt1->execute(['id' => (string)$id]);
			$row = $stmt1->fetch(PDO::FETCH_ASSOC);

			if ($row['matchid'] == 1) {
				$this->updateMatch($id, $matchID);
				return false; 
			}
			elseif ($row['matchid'] != $matchID) {
				return true;
			}
			
		}
		
		private function allMatchIDsMatch($details) {
			
			$first = $details[0]['matchid'];
			
			foreach ($details as $item) {
				
				if (!isset($item['matchid']) || $item['matchid'] !== $first) {
					return false;
				}
				
			}
			
			return true;
			
		}
		
		public function checkTrades() {
			
			if (getenv('BETA') === 'true') { return; }
			
			$ids = file_exists('/Media/trades.txt') ? file('/Media/trades.txt',  FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
			$guild = $this->discord->guilds->get('id', '232691831090053120');
			$channel = $guild->channels->get('id', '1352902587837583370');
			$http = new Browser();
			
			$http->get("https://aflapi.afl.com.au/liveblog/afl/164/EN?maxResults=3")->then(
				function (ResponseInterface $response) use ($ids, $channel) {
					$output = json_decode($response->getBody());
					foreach ($output->entries as $article) {
						if (!in_array($article->id, $ids)) {
							file_put_contents('/Media/trades.txt', $article->id . PHP_EOL, FILE_APPEND);
							preg_match("/<p class=\"live-blog-post-trade__heading-section__label\">(.+)<p>/m", $article->comment, $trade_type);
							preg_match_all("/<h2 class=\"live-blog-post-trade__title\">\s*(.*?)\s*<span.+?> (receive|give)s?:<\/span>/ms", $article->comment, $receives_team);
							preg_match_all("/<p class=\"live-blog-post-trade__text\">\s*(.*?)\s*<\/p>/ms", $article->comment, $receives_item);
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
							
							for ($x=0;$x<count($receives_team[1]);$x++) {
							
								$embed->addFieldValues("{$receives_team[1][$x]} {$receives_team[2][$x]}:", $receives_item[1][$x]);
								
							}
							
							$channel->sendEmbed($embed);
						}
					}
					
				},
				function (Exception $e) use ($channel) {
					$channel->sendMessage("Error: {$e->getMessage()}");
				}
			);
			
		}
	
	}
	
?>
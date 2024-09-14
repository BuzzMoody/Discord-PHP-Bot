<?php

use Discord\Parts\Embed\Embed;
use Discord\Parts\Channel\Attachment;
use Discord\Builders\MessageBuilder;
use Carbon\Carbon;
use React\Http\Browser;
use Psr\Http\Message\ResponseInterface;

class Commands {
	
	public $keys;
	public $uptime;
	public $heroes;
	public $gamemode;
	public $commands;
	public $patterns;
	
	function __construct($keys, $uptime) {
		
		$this->keys = $keys;
		$this->uptime = $uptime;
		$this->heroes = array(1 => "Anti-Mage", 2 => "Axe", 3 => "Bane", 4 => "Bloodseeker", 5 => "Crystal Maiden", 6 => "Drow Ranger", 7 => "Earthshaker", 8 => "Juggernaut", 9 => "Mirana", 11 => "Shadow Fiend", 10 => "Morphling", 12 => "Phantom Lancer", 13 => "Puck", 14 => "Pudge", 15 => "Razor", 16 => "Sand King", 17 => "Storm Spirit", 18 => "Sven", 19 => "Tiny", 20 => "Vengeful Spirit", 21 => "Windranger", 22 => "Zeus", 23 => "Kunkka", 25 => "Lina", 31 => "Lich", 26 => "Lion", 27 => "Shadow Shaman", 28 => "Slardar", 29 => "Tidehunter", 30 => "Witch Doctor", 32 => "Riki", 33 => "Enigma", 34 => "Tinker", 35 => "Sniper", 36 => "Necrophos", 37 => "Warlock", 38 => "Beastmaster", 39 => "Queen of Pain", 40 => "Venomancer", 41 => "Faceless Void", 42 => "Skeleton King", 43 => "Death Prophet", 44 => "Phantom Assassin", 45 => "Pugna", 46 => "Templar Assassin", 47 => "Viper", 48 => "Luna", 49 => "Dragon Knight", 50 => "Dazzle", 51 => "Clockwerk", 52 => "Leshrac", 53 => "Nature's Prophet", 54 => "Lifestealer", 55 => "Dark Seer", 56 => "Clinkz", 57 => "Omniknight", 58 => "Enchantress", 59 => "Huskar", 60 => "Night Stalker", 61 => "Broodmother", 62 => "Bounty Hunter", 63 => "Weaver", 64 => "Jakiro", 65 => "Batrider", 66 => "Chen", 67 => "Spectre", 69 => "Doom", 68 => "Ancient Apparition", 70 => "Ursa", 71 => "Spirit Breaker", 72 => "Gyrocopter", 73 => "Alchemist", 74 => "Invoker", 75 => "Silencer", 76 => "Outworld Devourer", 77 => "Lycanthrope", 78 => "Brewmaster", 79 => "Shadow Demon", 80 => "Lone Druid", 81 => "Chaos Knight", 82 => "Meepo", 83 => "Treant Protector", 84 => "Ogre Magi", 85 => "Undying", 86 => "Rubick", 87 => "Disruptor", 88 => "Nyx Assassin", 89 => "Naga Siren", 90 => "Keeper of the Light", 91 => "IO", 92 => "Visage", 93 => "Slark", 94 => "Medusa", 95 => "Troll Warlord", 96 => "Centaur Warrunner", 97 => "Magnus", 98 => "Timbersaw", 99 => "Bristleback", 100 => "Tusk", 101 => "Skywrath Mage", 102 => "Abaddon", 103 => "Elder Titan", 104 => "Legion Commander", 106 => "Ember Spirit", 107 => "Earth Spirit", 108 => "Abyssal Underlord", 109 => "Terrorblade", 110 => "Phoenix", 105 => "Techies", 111 => "Oracle", 112 => "Winter Wyvern", 113 => "Arc Warden", 114 => "Monkey King", 119 => "Dark Willow", 120 => "Pangolier", 121 => "Grimstroke", 123 => "Hoodwink", 126 => "Void Spirit", 128 => "Snapfire", 129 => "Mars", 131 => "Ring Master", 135 => "Dawnbreaker", 136 => "Marci", 137 => "Primal Beast", 138 => "Muerta");
		$this->gamemode = array(0 => "Unknown", 1 => "All Pick", 2 => "Captains Mode", 3 => "Random Draft", 4 => "Single Draft", 5 => "All Random", 6 => "Intro", 7 => "Diretide", 8 => "Reverse Captains Mode", 9 => "Greeviling", 10 => "Tutorial", 11 => "Mid Only", 12 => "Least Played", 13 => "Limited Heroes", 14 => "Compendium Matchmaking", 15 => "Custom", 16 => "Captains Draft", 17 => "Balanced Draft", 18 => "Ability Draft", 19 => "Event", 20 => "All Random Death Match", 21 => "1v1 Mid", 22 => "All Draft", 23 => "Turbo", 24 => "Mutation", 25 => "Coaches Challenge");
		$this->commands = [
			'ping' => 'ping',
			'radar' => 'radar',
			'apex' => 'apex',
			'uptime' => 'uptime',
			'reload' => 'reload'
		];
		$this->patterns = [
			'/^(kate|t(?:ay(lor)?|swizzle)|emma|e?liz(abeth)?|olympia|olivia|kim|mckayla|zach|hilary|ronan|sydney)$/' => 'sendBabe',
			'/^(search|google|bing|find|siri)/' => 'searchGoogle',
			'/^(image|img|photo|pic)/' => 'searchImage',
			'/^(ban|kick|sb|sinbin)/' => 'sinbin',
			'/^(bard|gemini|(open)?ai)/' => 'gemini',
			'/^(asx|share(s)?|stock(s)?|etf)/' => 'ASX',
			'/^(weather|temp(erature)?)/' => 'weather',
			'/^(forecast)$/' => 'forecast',
			'/^(shell|bash|cli|cmd)/' => 'runcli',
			'/^(remind(?:me|er))/' => 'createReminder',
			'/^(4k|games|afl|round)/' => 'afl',
			'/^(f(ormula)?1)$/' => 'f1',
			'/^(roll|dice)/' => 'dice',
			'/^(s(?:table)?d(?:iffusion)?)/' => 'stableDiffuse',
			'/^(u(rban)?d(ictionary)?)/' => 'urbanDic'
		];
		
	}
	
	function execute($message, $discord) {
		
		$inputs = explode(" ", trim($message->content));
		$command = substr($inputs[0], 1);
		$command = strtolower($command);
		array_shift($inputs);
		$args = implode(" ", $inputs);
		
		if (isset($this->commands[$command])) {
            $this->{$this->commands[$command]}($message, $discord);
        } else {
            foreach ($this->patterns as $pattern => $method) {
                if (preg_match($pattern, $command, $matches)) {
                    array_shift($matches);
                    $this->$method($message, $discord, $args, $matches);
                    break;
                }
            }
        }
		
	}
	
 	function ping($message) {
		$message->reply("Pong!");
	}
	
	function searchGoogle($message, $discord, $args) { 
		$this->search('google', $args, $message);
	}
	
	function searchImage($message, $discord, $args) { 
		$this->search('image', $args, $message);
	}
	
	function stableDiffuse($message, $discord, $args) { 
		$client = new Browser();
		$client->get("{$this->keys['sd']}/?img={$args}")->then(function (ResponseInterface $response) use ($message) {
			$rand = rand(1,100000);
			file_put_contents("../Media/AI/{$rand}.png", $response->getBody());
			$builder = MessageBuilder::new()
				->addFile("../Media/AI/{$rand}.png", "{$rand}.png");
			return $message->channel->sendMessage($builder);
		}, function (Exception $e) {
			echo "Error: {$e->getMessage()}\n";
		});
	}
	
	function urbanDic($message, $discord, $args) {
		$getUD = (empty($args)) ? @file_get_contents("https://www.urbandictionary.com/random.php") : @file_get_contents("https://www.urbandictionary.com/define.php?term=".urlencode($args));
		if (empty($getUD)) { return $message->channel->sendMessage("Word not found"); }
		preg_match_all("/(:?href=\"\/define\.php\?term=(.+)\" id=\"\d+\">(.+)<\/a><\/h1>|<div class=\"break-words meaning mb-4\">(.+)<\/div>)/mU", $getUD, $matches);
		$message->channel->sendMessage("**".str_replace('+',' ', $matches[2][0])."**: ".html_entity_decode(strip_tags($matches[4][1])));
	}
	
	function dice($message, $discord, $args) {
		if (preg_match('/(\d{1,2})(d(\d{1,2}))?$/', $args, $die)) {
			$dice = ($die[1] < 11 && $die[1] > 0) ? $die[1] : rand(1,10);
			$sides = ($die[3] < 21 && $die[3] > 0) ? $die[3] : rand(1,20);
			$op = "```\nRolling {$dice} {$sides}-sided Dice:\n\n";
			$ttl = 0;
			for ($x=1;$x<=$dice;$x++) {
				$val = rand(1,$sides);
				$op .= "🎲 {$x}:	{$val}\n";
				$ttl += $val;
			}
			$message->channel->sendMessage($op."\nTotal:	{$ttl}```");
		}
	}
	
	function f1($message, $discord) {
		
		$http = new Browser();

		$headers = array(
		  'apikey' => 'BQ1SiSmLUOsp460VzXBlLrh689kGgYEZ',
		  'locale' => 'en',
		);

		$http->get('https://api.formula1.com/v1/event-tracker', $headers)->then(
			function (ResponseInterface $response) use ($message, $discord) {
				$output = json_decode($response->getBody());
				$embed = $discord->factory(Embed::class);
				$embed->setAuthor('Formula 1 - Race Weekend', 'https://media.formula1.com/etc/designs/fom-website/icon192x192.png')
					->setTitle($output->race->meetingOfficialName)
					->setURL("https://www.formula1.com{$output->race->url}")
					->setColor('0x00A9FF')
					->setDescription("The current location is **{$output->race->meetingLocation}, {$output->race->meetingCountryName}**.");
				foreach ($output->seasonContext->timetables as $event) {
					$fieldval = ($event->state == 'completed') ? "~~{$this->toAusTime($event->startTime, 'G:i')} - {$this->toAusTime($event->endTime, 'G:i')}~~ - [Results](https://www.formula1.com/en/results/{$output->seasonContext->seasonYear}/races/{$output->fomRaceId}/{$output->race->meetingCountryName}/".str_replace(' ', '/', strtolower($event->description)).")" : "{$this->toAusTime($event->startTime, 'G:i')} - {$this->toAusTime($event->endTime, 'G:i')} (Starts <t:".strtotime($event->endTime).":R>)";	
					$embed->addFieldValues($event->description, $fieldval, false);
				}
				$message->channel->sendEmbed($embed);
			},
			function (Exception $e) use ($message) {
				$message->channel->send("Error: {$e->getMessage()}");
			}
		);
		
	}
	
	function toAusTime($time, $format = 'jS F: G:i', $countdown = false) {
		$datetime = new DateTime($time, new DateTimeZone('UTC'));
		$datetime->setTimezone(new DateTimeZone('Australia/Melbourne'));
		if ($countdown) {
			$starttime = new DateTime($countdown, new DateTimeZone('UTC'));
			$starttime->setTimezone(new DateTimeZone('Australia/Melbourne'));
			$currTime = new DateTime();
			$diffTime = $currTime->diff($starttime);
			$countTime = "";
			if ($diffTime->days > 0) { $countTime .= "{$diffTime->days} days, "; }
			if ($diffTime->h > 0) { $countTime .= "{$diffTime->h} hrs, "; }
			if ($diffTime->i > 0) { $countTime .= "{$diffTime->i} mins"; }
			return $datetime->format($format)." ({$countTime})";
		}
		return $datetime->format($format);	
	}
	
	function afl($message, $discord, $round) {
		
		$round = (empty($round)) ? intval(date("W")) - 10 : intval($round);
		$fixture = json_decode(file_get_contents("https://aflapi.afl.com.au/broadcasting/match-events?competition=1&compseason=62&round={$round}&pageSize=9"));
		if (empty($fixture->content)) { return $message->channel->sendMessage("Round not found."); }
		$embed = $discord->factory(Embed::class);
		foreach ($fixture->content as $game) {
			foreach ($game->channels as $channel) { 
				if ($channel->info->name == "Channel 7") { 
					if (empty($channel->restrictedRegions)) { 
						$seven = true;
						break;
					}
					foreach ($channel->restrictedRegions as $region) { 
						if ($region->id == 2) { $seven = true; }
					}
				}
			}
			if (!$seven) { $embed->addFieldValues("{$game->name}", "{$this->toAusTime($game->startDateTime, "l d F - H:i")}", true); }
			$seven = false;
		}
		
		$embed->setTitle("Round {$round} - 4K Games")
			->setColor("0x00A9FF")
			->setTimestamp()
			->setFooter("AFL", "https://www.afl.com.au/resources/v5.19.20/afl/apple-touch-icon.png");
		$message->channel->sendEmbed($embed);

	}
	
	function sendBabe($message, $discord, $args, $babe) {
	
		$imgDir = "../Media/Images/".preg_replace(array('/e?liz(abeth)?\b/', '/t(ay)?(lor)?(swizzle)?\b/'), array('elizabeth', 'taylor'), $babe[0]);
		$files = (is_dir($imgDir)) ? scandir($imgDir) : null;
		if ($files) { 
			$message->channel->sendFile("{$imgDir}/{$files[rand(2,(count($files) - 1))]}", $babe[0].".jpg");
		}
		
	}
	
	function search($type, $args, $message) {
	
		if (empty($args)) { return $message->reply("Maybe give me something to search for??"); }
		
		$search = ($type == "google") ? @file_get_contents("https://www.googleapis.com/customsearch/v1?key={$this->keys['google']}&cx=017877399714631144452:hlos9qn_wvc&googlehost=google.com.au&num=1&q=".str_replace(' ', '%20', $args)) : @file_get_contents("https://www.googleapis.com/customsearch/v1?key={$this->keys['google']}&cx=017877399714631144452:0j02gfgipjq&googlehost=google.com.au&searchType=image&excludeTerms=youtube&imgSize=xxlarge&safe=off&num=1&fileType=jpg,png,gif&q=".str_replace(' ', '%20', $args)."%20-site:facebook.com%20-site:tiktok.com%20-site:instagram.com");
		
		$return = json_decode($search);
		
		if ($return->searchInformation->totalResults == 0) { return $message->reply("No results."); }
		
		return ($type == "google") ? $message->channel->sendMessage("{$return->items[0]->title}: {$return->items[0]->link}") : $message->channel->sendMessage($return->items[0]->link);
	
	}
	
	function gemini($message, $discord, $args) {
		
		if (empty($args)) { return $message->reply("Maybe give the AI something to do??"); }
		
		$tokens = ($this->isAdmin($message->author->id, $discord)) ? 400 : 200;
		
		$post_fields = array(
			"contents" => array(
				"parts" => array(
					"text" => $args
				)
			),
			"safetySettings" => array(
				array(
					"category" => "HARM_CATEGORY_HATE_SPEECH",
					"threshold" => "BLOCK_NONE"
				),
				array(
					"category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT",
					"threshold" => "BLOCK_NONE"
				),
				array(
					"category" => "HARM_CATEGORY_HARASSMENT",
					"threshold" => "BLOCK_NONE"
				),
				array(
					"category" => "HARM_CATEGORY_DANGEROUS_CONTENT",
					"threshold" => "BLOCK_NONE"
				)
			),
			"generationConfig" => array(
				"temperature" => 0.5,
				"maxOutputTokens" => $tokens,
			),
			"systemInstruction" => array(
				"role" => "system",
				"parts" => array(
					"text" => "You are a Discord chatbot. Provide accurate and relevant answers to the questions and prompts given. Do not ask questions back. Answers must be in the form of full sentences. You may elaborate on your answer."
				),
			),
		);

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key='.$this->keys["gemini"],
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => json_encode($post_fields),
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json'
			),
		));

		$response = json_decode(curl_exec($curl));
		
		curl_close($curl);

		if (@$response->error->message) { return $message->channel->sendMessage($response->error->message); }

		else if (@$response->blockReason) { 
			
			return $message->channel->sendMessage("Error Reason: ".$response->blockReason); 
			
		}

		$string = $response->candidates[0]->content->parts[0]->text;

		$output = (strlen($string) > 1995) ? substr($string,0,1995).'…' : $string;
		
		$message->channel->sendMessage($output);
		
	}
	
	function getLocale($locale) {
		$locale = (empty($locale)) ? "Highett" : str_replace(' ', '+', trim($locale));
		$results = json_decode(@file_get_contents("https://api.beta.bom.gov.au/apikey/v1/locations/places/autocomplete?name={$locale}&limit=1&website-sort=true&website-filter=true"));
		if (empty($results)) { return false; }
		$place = array(
			"name" 		=> $results->candidates[0]->name,
			"state" 	=> $results->candidates[0]->state,
			"filename"	=> str_replace(' ', '-', $results->candidates[0]->name),
			"type"		=> $results->candidates[0]->type,
			"postcode" 	=> $results->candidates[0]->postcode->name,
			"forecast"	=> $results->candidates[0]->gridcells->forecast->x."/".$results->candidates[0]->gridcells->forecast->y,
			"id"		=> $results->candidates[0]->id
		);
		return $place;
	}
	
	function getMapImg($place) { 
		if (!file_exists("../Media/Maps/{$place['filename']}.png")) { file_put_contents("../Media/Maps/{$place['filename']}.png", file_get_contents("https://maps.googleapis.com/maps/api/staticmap?key={$this->keys['maps']}&center=".str_replace(' ', '%20', $place['name']).",%20".str_replace(' ', '%20', $place['state'])."&zoom=9&size=640x300&scale=2&markers=size:mid%7Ccolor:red%7C".str_replace(' ', '%20', $place['name']))); }
	}
	
	function forecast($message, $discord, $args) {
		$place = $this->getLocale($args);
		if (!$place) { return $message->channel->sendMessage("No location found"); }
		$embed = $discord->factory(Embed::class);
		$embed->setTitle("{$place['name']}, {$place['state']} ({$place['postcode']})");
		$forecast = json_decode(@file_get_contents("https://api.beta.bom.gov.au/apikey/v1/forecasts/daily/{$place['forecast']}?timezone=Australia%2FMelbourne"));
		array_shift($forecast->fcst->daily);
		$i=0;
		foreach ($forecast->fcst->daily as $daily) {
			$uv = (!empty($daily->atm->surf_air->radiation->uv_clear_sky_max_code) && $i < 3) ? ", uv ".round(@$daily->atm->surf_air->radiation->uv_clear_sky_max_code, 1) : "";
			$icon = preg_replace(array('/^1$/', '/^2$/', '/^3$/', '/^4$/', '/^5$/', '/^6$/', '/^7$/', '/^8$/', '/^9$/', '/^10$/', '/^11$/', '/^16$/'), array('☀️', '2', '🌤', ':cloud:', '5', '6', '7', '8', '🌬️', '🌫️', '🌦️', '⛈️'), $daily->atm->surf_air->weather->icon_code);
			$embed->addFieldValues("{$this->toAusTime($daily->date_utc, 'l jS')} {$icon}", "".round($daily->atm->surf_air->temp_max_cel, 1)."° / ".round($daily->atm->surf_air->temp_min_cel, 1)."° \n_☔ {$daily->atm->surf_air->precip->any_probability_percent}% {$uv}_", true);
			$i++;
		}
		$this->getMapImg($place);
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
	
	function weather($message, $discord, $args) {
		$place = $this->getLocale($args);
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
		$this->getMapImg($place);
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
			->setColor("0x00A9FF")
			->setTimestamp()
			->setFooter("Bureau of Meteorology", "attachment://BOM.png");
			
		$builder = MessageBuilder::new()
			->addEmbed($embed)
			->addFile("../Media/Maps/{$place['filename']}.png", "map-of-{$place['filename']}.png")
			->addFile("../Media/Maps/BOM.png", "BOM.png");
		
		return $message->channel->sendMessage($builder);
		
	}
	
	function uptime($message) {
		
		$diff = (floor(microtime(true) * 1000) - $this->uptime) / 1000;
		$days = floor($diff / 86400);
		$diff -= $days * 86400;
		$hours = floor($diff / 3600) % 24;
		$diff -= $hours * 3600;
		$minutes = floor($diff / 60) % 60;
		$diff -= $minutes * 60;
		$seconds = floor($diff % 60);
		$message->reply("{$days} days, {$hours} hrs, {$minutes} mins, {$seconds} secs");
		
	}
	
	function ASX($message, $discord, $args) {
		
		if (empty($args) || strlen($args) > 4) { return $message->reply("Try !asx DMP"); }
		
		if (false === ($header = @file_get_contents("https://asx.api.markitdigital.com/asx-research/1.0/companies/{$args}/header"))) {
			return $message->reply("Invalid search. Try !asx DMP"); 
		}
		
		$asxInit = json_decode($header);
		$asx["Current Price"] = "$".number_format($asxInit->data->priceLast, 2);
		$asx["Change"] = number_format($asxInit->data->priceChangePercent, 2)."%";
		$asx["Name"] = $asxInit->data->displayName;
		$asx["URL"] = "https://www2.asx.com.au/markets/company/{$args}";
		$asx["Market Cap"] = ($asxInit->data->securityType == 7) ? "ETF" : "$".number_format($asxInit->data->marketCap);
		$key = json_decode(file_get_contents("https://asx.api.markitdigital.com/asx-research/1.0/companies/{$args}/key-statistics"));
		$asx["52W ↑ / ↓"] = "$".$key->data->priceFiftyTwoWeekHigh." / $".$key->data->priceFiftyTwoWeekLow;
		$asx["Earnings Per Share"] = (!$key->data->earningsPerShare) ? "ETF" : "$".$key->data->earningsPerShare;
		$asx["Annual Yield"] = (!$key->data->yieldAnnual) ? "ETF" : number_format($key->data->yieldAnnual, 2)."%";
		
		if ($asx["Market Cap"] == "ETF") {
			$keyETF = json_decode(file_get_contents("https://asx.api.markitdigital.com/asx-research/1.0/etfs/{$args}/key-statistics"));
			$asx["NAV"] = "$".$keyETF->data->shareInformation->nav;
			$asx["YTD Return"] = $keyETF->data->fundamentals->returnYearToDate."%";
			$asx["Mgmt Fee"] = $keyETF->data->fundamentals->managementFeePercent."%";
			$asx["URL"] = "https://www2.asx.com.au/markets/etp/{$args}";
		}
		
		$embed = $discord->factory(Embed::class);
		$embed->setTitle($asx["Name"])
			->setURL($asx["URL"])
			->setDescription("ASX : ".strtoupper($args))
			->setColor("0x00A9FF")
			->setTimestamp()
			->setFooter("ASX", "https://www2.asx.com.au/content/dam/asx/asx-logos/asx-brandmark.png");
		
		foreach ($asx as $key => $value) {		
			if ($key == "Name" || $key == "URL" || $value == "ETF" ) { }
			else {	
				$embed->addFieldValues("{$key}", "{$value}", true);
			}
		}
		
		$message->channel->sendEmbed($embed);
	}
	
	function sinbin($message, $discord, $args, $filter = false) {
		
		if ($this->isAdmin($message->author->id, $discord) || $filter == true) {
			
			if (empty($args)) { return $message->reply("Try !sinbin @username"); }
		
			$argz = explode(" ", $args);
			$sbID = str_replace(array('<','@','!','>', '&'),'', $argz[0]);
		 	$sbGuild = $discord->guilds->get('id', '232691831090053120');
			$sbMember = $sbGuild->members->get('id', strval($sbID));
			$time = (count($argz) <= 1) ? 1 : $argz[1];
			$reason = ($argz[2] != null) ? implode(' ', array_slice($argz, 2)) : "";
			$sbMember->timeoutMember(new Carbon("{$time} minutes"))->done(function () {});
			$message->channel->sendMessage("{$argz[0]} has been given a {$time} minute timeout. {$reason}");
			
		}
		
	}
	
	function runcli($message, $discord, $args) {
		
		if ($message->author->id == 232691181396426752 && !empty($args)) {		
			$message->channel->sendMessage("```swift\n".shell_exec($args)."\n```");		
		}
		
	}
	
	function isAdmin($userID, $discord) {
		
		if ($userID == 232691181396426752) { return true; }
		$testGuild = $discord->guilds->get('id', '232691831090053120');
		$testMember = $testGuild->members->get('id', $userID);
		return $testMember->roles->has('232692759557832704');
		
	}
	
	function apex($message, $discord) {
		
		$get = file_get_contents("https://apexlegendsstatus.com/current-map/battle_royale/pubs");
		preg_match('/<h3 .*>(.+)<\/h3>.+ ends in (.+)<\/p>/U', $get, $data);
		preg_match_all('/<h3 .*>(.+)<\/h3>/U', $get, $next);
	
		$message->channel->sendMessage($data[1]." ends in ".$data[2]." | Next Map: ".$next[1][1]);
	}
	
	function checkReminders($discord) {
		
		$time = time();
		$mysqli = mysqli_connect('localhost', 'buzz', $this->keys['mysql'], 'discord');
		$result = $mysqli->query("SELECT * FROM reminders WHERE time < {$time}");
		
		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				$guild = $discord->guilds->get('id', '232691831090053120');
				$channel = $guild->channels->get('id', $row['channelid']);
				$channel->sendMessage("<@{$row['userid']}> Here is your reminder: https://discord.com/channels/232691831090053120/{$row['channelid']}/{$row['messageid']}");
				$mysqli->query("DELETE FROM reminders WHERE time = '{$row['time']}'");
			}
		}
		
		$mysqli->close();
		
	}
	
	function createReminder($message, $discord, $args) {	
		
		if (empty($args)) { return $message->reply("no args"); }
		
		$args2 = explode(" ", $args);	
		if (!is_numeric(intval($args2[0])) || intval($args2[0]) < 1) { return $message->reply("Must be valid positive number"); }
		if (!preg_match('/(min(?:ute)?|hour|day|week|month)s?/',$args2[1])) { return $message->reply("Syntax: !remindme 5 mins/hours/days [message]"); }

		$time = time() + (intval($args2[0]) * intval(preg_replace(array('/min(?:ute)?s?/', '/hours?/', '/days?/', '/weeks?/', '/months?/'), array('60', '3600', '86400', '604800', '2592000'), $args2[1])));
		
		if ($time > (time() + 2592000*12)) { return $message->reply("Too far into the future lol."); }
		
		$mysqli = mysqli_connect('localhost', 'buzz', $this->keys['mysql'], 'discord');
		$result = $mysqli->query("SELECT * FROM reminders WHERE userid = '{$message->author->id}'");
		
		if ($result->num_rows > 4) {
			 return $message->reply("You have the maximum amount of reminders set already.");
		}
		else {
		
			if ($mysqli->query("INSERT INTO reminders (userid, time, messageid, channelid) VALUES ({$message->author->id}, {$time}, {$message->id}, {$message->channel->id})")) {
				$message->react('⏲️');
			}
			else {
				$message->reply("I threw more errors than I know what to do with");
			}
		
		}
		
		$mysqli->close();
	
	}
	
	function radar($message, $discord) {
		
		$time = microtime(true);
		$embed = $discord->factory(Embed::class);
		$embed->setTitle("Melbourne Weather Radar")
			->setURL("http://www.bom.gov.au/products/IDR023.loop.shtml")
			->setImage("attachment://radar-{$time}.gif")
			->setColor("0x00A9FF")
			->setTimestamp()
			->setFooter("Bureau of Meteorology", "attachment://BOM.png");
		$builder = MessageBuilder::new()
			->addEmbed($embed)
			->addFile("../Media/Maps/BOM.png", "BOM.png")
			->addFile("../Media/Radar/animated.gif", "radar-{$time}.gif");		
		$message->channel->sendMessage($builder);
		
	}
	
	function reload($message, $discord) { 
		if ($this->isAdmin($message->author->id, $discord)) {
			exec("git stash");
			exec("git pull https://buzz:{$this->keys['gh']}@github.com/BuzzMoody/Discord-PHP-Bot.git");
			exec("chmod -R 755 /home/buzz/Bots/Master");
			die();
		}
	}
	
	function checkDota($discord) { 
		$ids = array(
			array("232691181396426752", "54716121", "Buzz"), 
			array("381596223435702282", "33939542", "Dan"), 
			array("276222661515018241", "77113202", "Hassler"), 
			array("132458420375650304", "50577085", "Bryce"),
		);
		
		$games = 0;

		for ($i = 0; $i < count($ids); $i++) {

			$url = "https://api.opendota.com/api/players/{$ids[$i][1]}/matches?limit=1";
			
			$content = @file_get_contents($url);
			
			if ($content === FALSE) { return; }

			$response = json_decode($content);

			$details[$i]['user'] = $ids[$i][1];
			$details[$i]['matchid'] = '';

			if ($this->checkNew($details[$i]['user'], $response[0]->match_id)) {

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
					$details[$i]['hero'] = $this->heroes[$response[0]->hero_id];
					$details[$i]['stats'] = array("Kills" => $response[0]->kills, "Deaths" => $response[0]->deaths, "Assists" =>$response[0]->assists);
					$start = $response[0]->start_time;
					$length = gmdate("H:i:s", $response[0]->duration);
					$mode = $this->gamemode[$response[0]->game_mode];
					@$matchid = ($response[0]->match_id == null) ? @$matchid : $response[0]->match_id;
					$ranked = ($response[0]->lobby_type == 5 || $response[0]->lobby_type == 6 || $response[0]->lobby_type == 7) ? "Yes" : "No";
					$games++;
					$this->updateMatch($details[$i]['user'], $response[0]->match_id);
					
				}
				
			}
			
		}
		
		
		if ($games > 0) {
			
			$embed = $discord->factory(Embed::class);
			$embed->setTitle("Dota 2 Match Information")
				->setURL("https://www.opendota.com/matches/".$matchid)
				->setImage("https://media.licdn.com/dms/image/C5612AQGLKrCEqkHZMw/article-cover_image-shrink_600_2000/0/1636444501645?e=2147483647&v=beta&t=Fd2nbDk9TUmsSm9c5Kt2wq9hP_bH1MxZITTa4pEx1wg")
				->setColor("0x00A9FF")
				->setTimestamp()
				->setFooter("Powered by OpenDota");
			$desc = "\n\n";
			
			for ($x = 0; $x < count($details); $x++) {
				if (@$details[$x]['new']) {
					$id = $x;
					$desc .= "<@{$details[$x]['discord']}> **{$details[$x]['win']}** playing as **{$details[$x]['hero']}**\n\n";
					$embed->addFieldValues("\n\n".$details[$x]['name'], "{$details[$x]['hero']}\n{$details[$x]['stats']['Kills']} / {$details[$x]['stats']['Deaths']} / {$details[$x]['stats']['Assists']}\n{$details[$x]['team']}\n\n\n", true);
				}
			}
			$tz = new DateTime("now", new DateTimeZone('Australia/Melbourne'));
			$tz->setTimestamp($start);
			$embed->setDescription($desc."\n");
			$embed->addFieldValues("\n\nGame Information", "Start Time: {$tz->format('H:i:s')}\nLength: {$length}\nGame Mode: {$mode}\nRanked: {$ranked}\n", false);
			
			$guild = $discord->guilds->get('id', '232691831090053120');
			$channel = $guild->channels->get('id', '232691831090053120');

			$channel->sendEmbed($embed);
		
		}
	
	}
	
	function checkNew($id, $matchID) {

		$mysqli = mysqli_connect('localhost', 'buzz', $this->keys['mysql'], 'discord');
		$result = $mysqli->query("SELECT * FROM dota2 WHERE id='{$id}' AND matchid='{$matchID}'");
		$mysqli->close();
		if ($result->num_rows == 0) { return true; }
		else { return false; }
		
	}

	function updateMatch($id, $matchID) {
		
		$mysqli = mysqli_connect('localhost', 'buzz', $this->keys['mysql'], 'discord');
		$result = $mysqli->query("UPDATE dota2 SET matchid='{$matchID}' WHERE id='{$id}'");
		$mysqli->close();
		
	}
	
}

?>
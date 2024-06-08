<?php

use Discord\Parts\Embed\Embed;
use Carbon\Carbon;

class Commands {
	
	public $keys;
	public $uptime;
	public $heroes;
	public $gamemode;
	
	function __construct($keys, $uptime) {
		
		$this->keys = $keys;
		$this->uptime = $uptime;
		$this->heroes = array(1 => "Anti-Mage", 2 => "Axe", 3 => "Bane", 4 => "Bloodseeker", 5 => "Crystal Maiden", 6 => "Drow Ranger", 7 => "Earthshaker", 8 => "Juggernaut", 9 => "Mirana", 11 => "Shadow Fiend", 10 => "Morphling", 12 => "Phantom Lancer", 13 => "Puck", 14 => "Pudge", 15 => "Razor", 16 => "Sand King", 17 => "Storm Spirit", 18 => "Sven", 19 => "Tiny", 20 => "Vengeful Spirit", 21 => "Windranger", 22 => "Zeus", 23 => "Kunkka", 25 => "Lina", 31 => "Lich", 26 => "Lion", 27 => "Shadow Shaman", 28 => "Slardar", 29 => "Tidehunter", 30 => "Witch Doctor", 32 => "Riki", 33 => "Enigma", 34 => "Tinker", 35 => "Sniper", 36 => "Necrophos", 37 => "Warlock", 38 => "Beastmaster", 39 => "Queen of Pain", 40 => "Venomancer", 41 => "Faceless Void", 42 => "Skeleton King", 43 => "Death Prophet", 44 => "Phantom Assassin", 45 => "Pugna", 46 => "Templar Assassin", 47 => "Viper", 48 => "Luna", 49 => "Dragon Knight", 50 => "Dazzle", 51 => "Clockwerk", 52 => "Leshrac", 53 => "Nature's Prophet", 54 => "Lifestealer", 55 => "Dark Seer", 56 => "Clinkz", 57 => "Omniknight", 58 => "Enchantress", 59 => "Huskar", 60 => "Night Stalker", 61 => "Broodmother", 62 => "Bounty Hunter", 63 => "Weaver", 64 => "Jakiro", 65 => "Batrider", 66 => "Chen", 67 => "Spectre", 69 => "Doom", 68 => "Ancient Apparition", 70 => "Ursa", 71 => "Spirit Breaker", 72 => "Gyrocopter", 73 => "Alchemist", 74 => "Invoker", 75 => "Silencer", 76 => "Outworld Devourer", 77 => "Lycanthrope", 78 => "Brewmaster", 79 => "Shadow Demon", 80 => "Lone Druid", 81 => "Chaos Knight", 82 => "Meepo", 83 => "Treant Protector", 84 => "Ogre Magi", 85 => "Undying", 86 => "Rubick", 87 => "Disruptor", 88 => "Nyx Assassin", 89 => "Naga Siren", 90 => "Keeper of the Light", 91 => "Wisp", 92 => "Visage", 93 => "Slark", 94 => "Medusa", 95 => "Troll Warlord", 96 => "Centaur Warrunner", 97 => "Magnus", 98 => "Timbersaw", 99 => "Bristleback", 100 => "Tusk", 101 => "Skywrath Mage", 102 => "Abaddon", 103 => "Elder Titan", 104 => "Legion Commander", 106 => "Ember Spirit", 107 => "Earth Spirit", 108 => "Abyssal Underlord", 109 => "Terrorblade", 110 => "Phoenix", 105 => "Techies", 111 => "Oracle", 112 => "Winter Wyvern", 113 => "Arc Warden", 114 => "Monkey King", 119 => "Dark Willow", 120 => "Pangolier", 121 => "Grimstroke", 123 => "Hoodwink", 126 => "Void Spirit", 128 => "Snapfire", 129 => "Mars", 135 => "Dawnbreaker", 136 => "Marci", 137 => "Primal Beast", 138 => "Muerta");
		$this->gamemode = array(0 => "Unknown", 1 => "All Pick", 2 => "Captains Mode", 3 => "Random Draft", 4 => "Single Draft", 5 => "All Random", 6 => "Intro", 7 => "Diretide", 8 => "Reverse Captains Mode", 9 => "Greeviling", 10 => "Tutorial", 11 => "Mid Only", 12 => "Least Played", 13 => "Limited Heroes", 14 => "Compendium Matchmaking", 15 => "Custom", 16 => "Captains Draft", 17 => "Balanced Draft", 18 => "Ability Draft", 19 => "Event", 20 => "All Random Death Match", 21 => "1v1 Mid", 22 => "All Draft", 23 => "Turbo", 24 => "Mutation", 25 => "Coaches Challenge");
		
	}
	
	function execute($message, $discord) {
		
		$inputs = explode(" ", trim($message->content));
		$command = substr($inputs[0], 1);
		$command = strtolower($command);
		array_shift($inputs);
		$args = implode(" ", $inputs);
		
		switch ($command) {
			
			case "ping":
				$message->reply("Pong!");
				break;
				
			case (preg_match('/^(kate|t(?:ay(lor)?|swizzle)|emma|e?liz(abeth)?|olympia|olivia|kim|mckayla|zach|hilary|ronan|sydney)\b/', $command, $babe) ? true : false):
				$this->sendBabe($babe, $message);
				break;
				
			case (preg_match('/^(search|google|bing|find|siri)/', $command) ? true : false):
				$this->search('google', $args, $message);
				break;
				
			case (preg_match('/^(image|img|photo|pic)/', $command) ? true : false):
				$this->search('image', $args, $message);
				break;
				
			case (preg_match('/^(ban|kick|sb|sinbin)/', $command) ? true : false):
				$this->sinbin($args, $message, $discord);
				break;
			
			case (preg_match('/^(bard|gemini|(open)?ai)/', $command) ? true : false):
				$this->gemini($args, $message, $discord);
				break;
				
			case (preg_match('/^(asx|share(s)?|stock(s)?|etf)/', $command) ? true : false):
				$this->ASX($args, $message, $discord);
				break;
				
			case (preg_match('/^(temp(erature)?)$/', $command) ? true : false):
				$this->temp($message);
				break;
				
			case (preg_match('/^(weather|forecast)$/', $command) ? true : false):
				$this->weather($message);
				break;
				
			case (preg_match('/^(shell|bash|cli|cmd)/', $command) ? true : false):
				$this->runcli($args, $message, $discord);
				break;
				
			case (preg_match('/^(remind(?:me|er))/', $command) ? true : false):
				$this->createReminder($args, $message, $discord);
				break;
				
			case (preg_match('/^(4k|games|afl|round)/', $command) ? true : false):
				$this->afl($args, $message, $discord);
				break;
				
			case (preg_match('/^(f(:?ormula)1)/', $command) ? true : false):
				$this->f1($message, $discord);
				break;
				
			case "radar":
				$this->radar($message, $discord);
				break;
				
			case "apex":
				$this->apex($message, $discord);
				break;
				
			case "uptime":
				$this->uptime($message);
				break;
				
			case "reload":
				$this->reload($message, $discord);
				break;

		}
		
	}
	
	function f1($message, $discord) {
		
		$nextRace = file_get_contents("https://www.formula1.com/en/racing/2024.html");
		preg_match_all("/\"(?:@id|description|url|address|startDate|endDate)\": \"(.+)\",?/", $nextRace, $matches);
		$next = array(
			"URL" => $matches[1][0],
			"name" => $matches[1][1],
			"img" => $matches[1][2],
			"starts" => $matches[1][3],
			"ends" => $matches[1][4],
			"locale" => $matches[1][5]
		);
		$upcoming = array(
			"first" => $matches[1][11],
			"firstDate" => $matches[1][15],
			"second" => $matches[1][17],
			"secondDate" => $matches[1][21]
		);
		$sessions = array();
		$sessionsInfo = file_get_contents($next['URL']);
		preg_match_all("/\"(?:name|startDate)\": \"(.+)\",?/", $sessionsInfo, $matches);
		for($x=0;$x<(count($matches[1])-4);$x++) {
			$sessions[$x] = array(
				"name" => $matches[1][($x+2)],
				"time" => $matches[1][($x+3)]
			);
			$x++;
		}
		
		$embed = $discord->factory(Embed::class);
		$embed->setAuthor("Formula 1 - Race Info", "https://www.formula1.com/etc/designs/fom-website/images/f1_logo.svg")
			->setTitle($next["name"])
			->setURL($next["URL"])
			->setImage($next["img"])
			->setColor("0x00A9FF")
			->setDescription("The next race takes place in {$next["locale"]}.")
		for ($x=0;$<count($sessions);$x++) {
			$embed->addFieldValues($sessions[$x]["name"], $sessions[$x]["time"], false);
		}
		$embed->addFieldValues("Upcoming Races", "{$upcoming["first"]} - {$upcoming["firstDate"]}\n{$upcoming["second"]} - {$upcoming["secondDate"]}", false);
		$message->channel->sendEmbed($embed);
		
	}
	
	function toAusTime($time) {
		$datetime = new DateTime($time, new DateTimeZone('UTC'));
		$datetime->setTimezone(new DateTimeZone('Australia/Melbourne'));
		return $datetime->format('Y-m-d\TH:i:sP');	
	}
	
	function afl($round, $message, $discord) {
		
		$round = intval($round);
		$round = (empty($round) || !is_int((int)$round) || $round < 0 || $round > 23) ? (date("W")-10) : $round;
		$mysqli = mysqli_connect('localhost', 'buzz', $this->keys['mysql'], 'discord');
		$result = $mysqli->query("SELECT * FROM games WHERE round={$round}");
		if ($result->num_rows != 0) { 
			$out = "### Round {$round} - Games in 4K\n\n";
			while ($row = $result->fetch_assoc()) {
				$out .= "- **{$row['teams']}**\n - *{$row['date']} - {$row['time']}*\n";
			}
			$message->channel->sendMessage($out);
		}
		else { 
			$message->channel->sendMessage("Round not found in DB");
		}
		$mysqli->close();
		
	}
	
	function sendBabe($babe, $message) {
	
		$imgDir = "/home/buzz/bot-php/img/".preg_replace(array('/e?liz(abeth)?\b/', '/t(ay)?(lor)?(swizzle)?\b/'), array('elizabeth', 'taylor'), $babe[0]);
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
	
	function gemini($args, $message, $discord) {
		
		if (empty($args)) { return $message->reply("Maybe give the AI something to do??"); }
		
		$tokens = ($this->isAdmin($message->author->id, $discord)) ? 400 : 200;
		$words = ($this->isAdmin($message->author->id, $discord)) ? 200 : 50;
		
		$post_fields = array(
			"contents" => array(
				"parts" => array(
					"text" => "You are a Discord chatbot so keep your responses short/under ".$words." words if possible: ".$args
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
				"temperature" => 0.9,
				"maxOutputTokens" => $tokens,
				"topK" => 1,
				"topP" => 0.95
			)
		);

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:streamGenerateContent?key='.$this->keys["gemini"],
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

		if (@$response[0]->error->message) { return $message->reply($response[0]->error->message); }

		else if (@$response[0]->blockReason) { return $message->reply( "Error Reason: ".$response[0]->blockReason); }

		for ($x = 0; $x < count($response); $x++) {
			@$string .= @$response[$x]->candidates[0]->content->parts[0]->text;
		}
	
		$output = (strlen($string) > 1995) ? substr($string,0,1995).'…' : $string;
		
		$message->channel->sendMessage($output);
		
	}
	
	function getTemp() {
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://api.weather.bom.gov.au/v1/locations/r1ppvy/observations");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36");
		$temp = json_decode(curl_exec($ch));
		return "{$temp->data->temp}° (Feels {$temp->data->temp_feels_like}°) | Wind: {$temp->data->wind->speed_kilometre}kph ".preg_replace(array('/^N$/', '/^S$/', '/^E$/', '/^W$/', '/^.?NE$/', '/^.?SE$/', '/^.?SW$/', '/^.?NW$/'), array('↓', '↑', '←', '→', '↙', '↖', '↗', '↘'), $temp->data->wind->direction)." | Humidity: {$temp->data->humidity}% | Rain: {$temp->data->rain_since_9am}mm";

		
	}
	
	function temp($message) {
		
		$message->channel->sendMessage($this->getTemp());
		
	}
	
	function weather($message) {
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://api.weather.bom.gov.au/v1/locations/r1ppvy/forecasts/daily");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36");
		$temp = json_decode(curl_exec($ch));
		
		foreach ($temp->data as $daily => $info) {
			
			$date = new DateTime($info->date);
			$date->setTimezone(new DateTimeZone("Australia/Melbourne"));
			$localDate = $date->format('D dS');
			
			$desc = preg_replace(array('/light_shower/', '/mostly_sunny/', '/shower/', '/rain/', '/storm/', '/cloudy/', '/sunny/'), array('💡🚿', '🌤️', '🌦️', '🌧️', '🌩️', '☁️', '☀️'), $info->icon_descriptor);
			$fire = (!empty($info->fire_danger)) ? " (🔥 {$info->fire_danger})" : "";
			
			$output .= "{$localDate}: {$info->temp_max}° {$desc}{$fire}";
			if ($daily != array_key_last($temp->data)) { $output .= "\n"; }
			
		}

		$message->channel->sendMessage("```\n{$output}\n```");
		
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
	
	function ASX($args, $message, $discord) {
		
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
	
	function sinbin($args, $message, $discord, $filter = false) {
		
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
	
	function runcli($args, $message, $discord) {
		
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
	
	function createReminder($args, $message, $discord) {	
		
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
		
		$embed = $discord->factory(Embed::class);
		$embed->setTitle("Melbourne Weather Radar")
			->setURL("http://www.bom.gov.au/products/IDR023.loop.shtml")
			->setDescription($this->getTemp())
			->setImage("https://reg.bom.gov.au/radar/IDR023.gif?".time())
			->setColor("0x00A9FF")
			->setTimestamp()
			->setFooter("BOM", "https://reg.bom.gov.au/images/touch-icon/touch-icon-76x76.png");
		$message->channel->sendEmbed($embed);
		
	}
	
	function reload($message, $discord) { 
		if ($this->isAdmin($message->author->id, $discord)) {
			exec("git stash");
			exec("git pull https://buzz:{$this->keys['gh']}@github.com/BuzzMoody/Discord-PHP-Bot.git");
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
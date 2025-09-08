<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;

	function SearchFunc($type, $message, $args) {
	
		if (empty($args)) { return $message->reply("Maybe give me something to search for??"); }
		
		$api_url = ($type == 'google') ? "https://customsearch.googleapis.com/customsearch/v1?key=".getenv('SEARCH_API_KEY')."&cx=017877399714631144452:hlos9qn_wvc&googlehost=google.com.au&num=1&q=".str_replace(' ', '%20', $args) : "https://customsearch.googleapis.com/customsearch/v1?key=".getenv('SEARCH_API_KEY')."&cx=017877399714631144452:0j02gfgipjq&googlehost=google.com.au&searchType=image&excludeTerms=youtube&imgSize=xxlarge&safe=off&num=1&fileType=jpg,png,gif&q=".str_replace(' ', '%20', $args)."%20-site:facebook.com%20-site:tiktok.com%20-site:instagram.com";
		
		try {
			$search = file_get_contents($api_url);
			if ($search === false) { return null; }
			$return = json_decode($search);		
			if ($return === null) { return null; }	
		} catch (Exception $e) {
			return null;
		}
		
		if ($return->searchInformation->totalResults == 0) { return $message->reply("No results."); }
		
		return ($type == 'google') ? $message->channel->sendMessage("{$return->items[0]->title}: {$return->items[0]->link}") : $message->channel->sendMessage($return->items[0]->link);
	
	}
	
	function isAdmin($userID) {
		
		global $discord;
		
		if ($userID == 232691181396426752) { return true; }
		$testGuild = $discord->guilds->get('id', '232691831090053120');
		$testMember = $testGuild->members->get('id', $userID);
		return $testMember->roles->has('232692759557832704');
		
	}
	
	function getLocale($locale) {
		
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
	
	function getMapImg($place, $eq = false, $name = "") {
		
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
	
	function toAusTime($time, $format = 'jS F: G:i', $countdown = false, $offset = 'UTC', $relative = false) {
		
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

	function checkDeadlock() {

		if (getenv('BETA') === 'true') { return; }

		global $discord;
		
		$guild = $discord->guilds->get('id', '232691831090053120');
		$channel = $guild->channels->get('id', '232691831090053120');
		
		$date = new DateTime('now');
		$current_hour = (int)$date->format('G');
		
		if ($current_hour >= 10 || $current_hour <= 2) {

			$ids = array(
				array("381596223435702282", "33939542", "Dan"), 
				array("132458420375650304", "50577085", "Bryce"), 
			);
			
			$details = [];
			
			for ($x = 0; $x < count($ids); $x++) {
				
				$url = "https://data.deadlock-api.com/v2/players/{$ids[$x][1]}/match-history?api_key=".getenv('DEADLOCK_API_KEY');
				$content = @file_get_contents($url);
				if ($content === FALSE) { return; }
				$response = json_decode($content);
				if (count($response->matches) < 2) { return; }
				$match = $response->matches[0];
				
				if (checkNew($ids[$x][1], $match->match_id, "deadlock")) {
					
					$time = new DateTime("now");
					$time->setTimestamp($match->start_time);
					
					$details[$x]['matchid'] = $match->match_id;
					$details[$x]['discord'] = $ids[$x][0];
					$details[$x]['name'] = $ids[$x][2];
					$details[$x]['user'] = $ids[$x][1];
					$details[$x]['hero'] = Commands::DL_HEROES[$match->hero_id];
					$details[$x]['team'] = $match->player_team;
					$details[$x]['level'] = $match->hero_level;
					$details[$x]['time'] = $time->format("H:i:s");
					$details[$x]['length'] = gmdate("H:i:s", $match->match_duration_s);
					$details[$x]['mode'] = Commands::DL_GAMEMODES[$match->match_mode];
					$details[$x]['kda'] = "{$match->player_kills} / {$match->player_deaths} / {$match->player_assists}";
					$details[$x]['lh'] = $match->last_hits;
					$details[$x]['worth'] = number_format($match->net_worth);
					$details[$x]['denies'] = $match->denies;
					$details[$x]['result'] = ($match->match_result == $match->player_team) ? "Won" : "Lost";
					
					updateMatch($details[$x]['user'], $match->match_id, "deadlock");
					
				}
				
			}

			if (count($details) > 1 && allMatchIDsMatch($details)) { 
			
				$embed = $discord->factory(Embed::class);
				$embed->setTitle("Deadlock Match Results")
					->setImage("https://buzzmoody.au/deadlock-banner.jpg")
					->setColor(getenv('COLOUR'))
					->setTimestamp()
					->setFooter("Powered by Deadlock-API");
					//->setURL("https://www.opendota.com/matches/".$matchid)
				$desc = "\n\n";

				foreach ($details as $player) {
					$desc .= "<@{$player['discord']}> **{$player['result']}** playing as **{$player['hero']}**\n\n";
					$embed->addFieldValues("\n\n".$player['name'], "{$player['hero']} (Lvl {$player['level']})\n{$player['kda']}\n{$player['lh']} LH\n{$player['denies']} Denies\n{$player['worth']} Souls\n\n", true);
					$mode = $player['mode'];
					$start = $player['time'];
					$length = $player['length'];
				}
				
				$embed->setDescription($desc."\n")
					->addFieldValues("\n\nGame Information", "Start Time: {$start}\nLength: {$length}\nGame Mode: {$mode}\n", false);

				$channel->sendEmbed($embed);
			
			}
			
			else {

				foreach ($details as $player) {
					if (!empty($player['lh'])) {
						$embed = $discord->factory(Embed::class);
						$embed->setTitle("Deadlock Match Results")
							->setImage("https://buzzmoody.au/deadlock-banner.jpg")
							->setColor(getenv('COLOUR'))
							->setTimestamp()
							->setFooter("Powered by Deadlock-API");
							//->setURL("https://www.opendota.com/matches/".$matchid)
						$desc = "\n\n<@{$player['discord']}> **{$player['result']}** playing as **{$player['hero']}**\n\n";
						$embed->addFieldValues("\n\n".$player['name'], "{$player['hero']} (Lvl {$player['level']})\n{$player['kda']}\n{$player['lh']} LH\n{$player['denies']} Denies\n{$player['worth']} Souls\n\n", true);

						$embed->setDescription($desc."\n")
							->addFieldValues("\n\nGame Information", "Start Time: {$player['time']}\nLength: {$player['length']}\nGame Mode: {$player['mode']}\n", false);

						$channel->sendEmbed($embed);
					}
				}

			}
		
		}
		
	}
	
	function checkDota() {
		
		if (getenv('BETA') === 'true') { return; }
		
		global $discord;
		
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

				$url = "https://api.opendota.com/api/players/{$ids[$i][1]}/matches?limit=1";
				
				$content = @file_get_contents($url);
				
				if ($content === FALSE) { return; }

				$response = json_decode($content);

				$details[$i]['user'] = $ids[$i][1];
				$details[$i]['matchid'] = '';

				if (checkNew($details[$i]['user'], $response[0]->match_id, "dota2")) {

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
						$details[$i]['hero'] = Commands::DOTA_HEROES[$response[0]->hero_id];
						$details[$i]['stats'] = array("Kills" => $response[0]->kills, "Deaths" => $response[0]->deaths, "Assists" =>$response[0]->assists);
						$start = $response[0]->start_time;
						$length = gmdate("H:i:s", $response[0]->duration);
						$mode = Commands::DOTA_GAMEMODES[$response[0]->game_mode];
						@$matchid = ($response[0]->match_id == null) ? @$matchid : $response[0]->match_id;
						$ranked = ($response[0]->lobby_type == 5 || $response[0]->lobby_type == 6 || $response[0]->lobby_type == 7) ? "Yes" : "No";
						$games++;
						updateMatch($details[$i]['user'], $response[0]->match_id, "dota2");
						
					}
					
				}
				
			}

			if ($games > 0) {
				
				$embed = $discord->factory(Embed::class);
				$embed->setTitle("Dota 2 Match Information")
					->setURL("https://www.opendota.com/matches/".$matchid)
					->setImage("https://media.licdn.com/dms/image/C5612AQGLKrCEqkHZMw/article-cover_image-shrink_600_2000/0/1636444501645?e=2147483647&v=beta&t=Fd2nbDk9TUmsSm9c5Kt2wq9hP_bH1MxZITTa4pEx1wg")
					->setColor(getenv('COLOUR'))
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
	
	}
	
	function checkNew($id, $matchID, $game = "dota2") {

		$mysqli = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_KEY'), getenv('DB_NAME'));
		$checkNewDB = $mysqli->query("SELECT * FROM {$game} WHERE id='{$id}' AND matchid='1'");
		if ($checkNewDB->num_rows > 0) {
			updateMatch($id, $matchID, $game);
			$mysqli->close();
			return false;
		}
		else {
			$result = $mysqli->query("SELECT * FROM {$game} WHERE id='{$id}' AND matchid='{$matchID}'");
			$mysqli->close();
			if ($result->num_rows == 0) { return true; }
			else { return false; }
		}
		
	}

	function updateMatch($id, $matchID, $game = "dota2") {
		
		$mysqli = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_KEY'), getenv('DB_NAME'));
		$result = $mysqli->query("UPDATE {$game} SET matchid='{$matchID}' WHERE id='{$id}'");
		$mysqli->close();
		
	}
	
	function allMatchIDsMatch($details) {
		
		$first = $details[0]['matchid'];
		
		foreach ($details as $item) {
			
			if (!isset($item['matchid']) || $item['matchid'] !== $first) {
				return false;
			}
			
		}
		
		return true;
		
	}
	
	function checkReminders() {
		
		global $discord;

		$time = time();
		$mysqli = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_KEY'), getenv('DB_NAME'));
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
	
	function simpleEmbed($authName, $authIMG, $authURL, $text, $message, $reply = false) {
		
		global $discord;
		
		$embed = $discord->factory(Embed::class);
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
		
			
		$builder = MessageBuilder::new()
			->addEmbed($embed)
			->addFile("/Media/Maps/{$place['filename']}.png", "map-of-{$place['filename']}.png");
		
	}
	
?>
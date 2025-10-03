<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;

	function SearchFunc($type, $message, $args) {
	
		if (empty($args)) { return simpleEmbed("Google Search", "https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/24px-Google_%22G%22_logo.svg.png", "Invalid syntax used. Please provide search terms.", $message, true, null); }
		
		$api_url = ($type == 'google') ? "https://customsearch.googleapis.com/customsearch/v1?key=".getenv('SEARCH_API_KEY')."&cx=017877399714631144452:hlos9qn_wvc&googlehost=google.com.au&num=1&q=".str_replace(' ', '%20', $args) : "https://customsearch.googleapis.com/customsearch/v1?key=".getenv('SEARCH_API_KEY')."&cx=017877399714631144452:0j02gfgipjq&googlehost=google.com.au&searchType=image&excludeTerms=youtube&imgSize=xxlarge&safe=off&num=1&fileType=jpg,png,gif&q=".str_replace(' ', '%20', $args)."%20-site:facebook.com%20-site:tiktok.com%20-site:instagram.com";
		
		try {
			$search = file_get_contents($api_url);
			if ($search === false) { return null; }
			$return = json_decode($search);		
			if ($return === null) { return null; }	
		} catch (Exception $e) {
			return null;
		}
		
		if ($return->searchInformation->totalResults == 0) { return simpleEmbed("Google Search", "https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/24px-Google_%22G%22_logo.svg.png", "No results found for *{$args}*.", $message, true, null); }
		
		return ($type == 'google') ? $message->channel->sendMessage("{$return->items[0]->title}: {$return->items[0]->link}") : $message->channel->sendMessage($return->items[0]->link);
	
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

				$url = "https://api.opendota.com/api/players/{$ids[$i][1]}/recentMatches";
				
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
						$details[$i]['stats'] = array("Kills" => $response[0]->kills, "Deaths" => $response[0]->deaths, "Assists" => $response[0]->assists,"HeroDMG" => number_format($response[0]->hero_damage), "TowerDMG" => number_format($response[0]->tower_damage), "XPM" => $response[0]->xp_per_min, "GPM" => number_format($response[0]->gold_per_min), "Heal" => number_format($response[0]->hero_healing));
						$start = $response[0]->start_time;
						$duration = $response[0]->duration;
						$hours = floor($duration / 3600);
						$format = ($hours > 0) ? 'g \h\o\u\r\s i \m\i\n\s' : 'i \m\i\n\s';
						$length = gmdate($format, $duration);
						$mode = Commands::DOTA_GAMEMODES[$response[0]->game_mode];
						@$matchid = ($response[0]->match_id == null) ? @$matchid : $response[0]->match_id;
						$ranked = ($response[0]->lobby_type == 5 || $response[0]->lobby_type == 6 || $response[0]->lobby_type == 7) ? "Ranked" : "Unranked";
						$games++;
						updateMatch($details[$i]['user'], $response[0]->match_id, "dota2");
						
					}
					
				}
				
			}

			if ($games > 0) {
				
				$tz = new DateTime("now", new DateTimeZone('Australia/Melbourne'));
				$tz->setTimestamp($start);
				
				$embed = $discord->factory(Embed::class);
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
						$embed->addFieldValues("Stats", "Lvl ".getLevel(($details[$x]['stats']['XPM'] * ($duration / 60)))."\n".number_format($details[$x]['stats']['XPM'])." xpm\n{$details[$x]['stats']['GPM']} gpm", true);
					}
				}

				$embed->setDescription($desc."\n");
				
				$builder = MessageBuilder::new()
					->addEmbed($embed)
					->addFile("/Media/dota.png", "dota.png");
				
				$guild = $discord->guilds->get('id', '232691831090053120');
				$channel = $guild->channels->get('id', '232691831090053120');

				return $channel->sendMessage($builder);
			
			}
			
		}
	
	}
	
	function getLevel($exp) {
		
		for ($level = count(Commands::DOTA_LEVELS) - 1; $level >= 1; $level--) {
			if ($exp >= Commands::DOTA_LEVELS[$level]) {
				return $level;
			}
		}
		
		return 1;
		
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
					simpleEmbed("Chat Reminders", "attachment://bot.webp", "<@{$row['userid']}> Here is your reminder: https://discord.com/channels/232691831090053120/{$row['channelid']}/{$row['messageid']}", $message, true, null); 
				});
				$deleteStmt->execute([':time' => $row['time']]);
			
			}
			
			$pdo->commit(); 
			
		}
		
	}
	
	function filterUsers($message) {
		
		global $discord;
		
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
	
	function checkTrades() {
	
		global $discord;
		
		if (getenv('BETA') === 'true') { return; }
		
		$ids = file_exists('trades.txt') ? file('trades.txt',  FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
		$guild = $discord->guilds->get('id', '232691831090053120');
		$channel = $guild->channels->get('id', '1352902587837583370');
		$http = new Browser();
		
		$http->get("https://aflapi.afl.com.au/liveblog/afl/122/EN?maxResults=3")->then(
			function (ResponseInterface $response) use ($ids, $discord, $channel) {
				$output = json_decode($response->getBody());
				foreach ($output->entries as $article) {
					if (!in_array($article->id, $ids)) {
						file_put_contents('trades.txt', $article->id . PHP_EOL, FILE_APPEND);
						preg_match("/<p class=\"live-blog-post-trade__heading-section__label\">(.+)<p>/m", $article->comment, $trade_type);
						preg_match_all("/<h2 class=\"live-blog-post-trade__title\">\s*(.*?)\s*<span.+?> (receive|give)s?:<\/span>/ms", $article->comment, $receives_team);
						preg_match_all("/<p class=\"live-blog-post-trade__text\">\s*(.*?)\s*<\/p>/ms", $article->comment, $receives_item);
						preg_match("/<h2 class=\"live-blog-post-article__title\">(.+?)<\/h2>.+<p class=\"live-blog-post-article__text\">(.+?)<\/p>/ms", $article->comment, $article_text);
						preg_match("/, (https:\/\/resources\.afl\.com\.au\/photo-resources\/.+\.(jpg|png)\?width=2128&height=1200)/", $article->comment, $image);
						preg_match("/href=\"(\/news\/(.+?))\".*target=\"_blank\"/s", $article->comment, $url);
						
						$embed = $discord->factory(Embed::class);
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
	
?>
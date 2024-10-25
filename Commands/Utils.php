<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;

	function SearchFunc($type, $message, $args) {
		
		global $keys;
	
		if (empty($args)) { return $message->reply("Maybe give me something to search for??"); }
		
		$api_url = ($type == 'google') ? "https://www.googleapis.com/customsearch/v1?key={$keys['google']}&cx=017877399714631144452:hlos9qn_wvc&googlehost=google.com.au&num=1&q=".str_replace(' ', '%20', $args) : "https://www.googleapis.com/customsearch/v1?key={$keys['google']}&cx=017877399714631144452:0j02gfgipjq&googlehost=google.com.au&searchType=image&excludeTerms=youtube&imgSize=xxlarge&safe=off&num=1&fileType=jpg,png,gif&q=".str_replace(' ', '%20', $args)."%20-site:facebook.com%20-site:tiktok.com%20-site:instagram.com";
		
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
			"filename"	=> str_replace(' ', '-', $results->candidates[0]->name),
			"type"		=> $results->candidates[0]->type,
			"postcode" 	=> $results->candidates[0]->postcode->name,
			"forecast"	=> $results->candidates[0]->gridcells->forecast->x."/".$results->candidates[0]->gridcells->forecast->y,
			"id"		=> $results->candidates[0]->id
		);
		return $place;
		
	}
	
	function getMapImg($place) {
		
		global $keys;
		
		if (!file_exists("../Media/Maps/{$place['filename']}.png")) { 
			file_put_contents("../Media/Maps/{$place['filename']}.png", file_get_contents("https://maps.googleapis.com/maps/api/staticmap?key={$keys['maps']}&center=".str_replace(' ', '%20', $place['name']).",%20".str_replace(' ', '%20', $place['state'])."&zoom=9&size=640x300&scale=2&markers=size:mid%7Ccolor:red%7C".str_replace(' ', '%20', $place['name']))); 
		}
		
	}
	
	function toAusTime($time, $format = 'jS F: G:i', $countdown = false, $offset = 'UTC', $relative = false) {
		
		if ($relative) {
			preg_match('/([+-])(\d{2}):(\d{2})/', $offset, $matches);
			$sign = $matches[1];
			$hours = abs(intval($matches[2]) - 11);
			$minutes = intval($matches[3]);
			$offset = sprintf('+%02d:%02d', $hours, $minutes);
		}
		$datetime = new DateTime($time, new DateTimeZone($offset));
		$datetime->setTimezone(new DateTimeZone('Australia/Melbourne'));
		if ($countdown) {
			$currTime = new DateTime();
			$diffTime = $currTime->diff($datetime);
			$countTime = "";
			if ($diffTime->days > 0) { $countTime .= "{$diffTime->days} days, "; }
			if ($diffTime->h > 0) { $countTime .= "{$diffTime->h} hrs, "; }
			if ($diffTime->i > 0) { $countTime .= "{$diffTime->i} mins"; }
			return $datetime->format($format)." ({$countTime})";
		}
		return $datetime->format($format);	
		
	}
	
	function checkDota() {
		
		global $discord, $keys;
		
		if ($keys['beta'] === true) { return; }
		date_default_timezone_set('Australia/Melbourne');
		$current_hour = Date('G');
		if (($current_hour >= 10 && $current_hour <= 23) || in_array($current_hour, [0, 1, 2])) {

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

				if (checkNew($details[$i]['user'], $response[0]->match_id)) {

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
						$details[$i]['hero'] = Commands::HEROES[$response[0]->hero_id];
						$details[$i]['stats'] = array("Kills" => $response[0]->kills, "Deaths" => $response[0]->deaths, "Assists" =>$response[0]->assists);
						$start = $response[0]->start_time;
						$length = gmdate("H:i:s", $response[0]->duration);
						$mode = Commands::GAMEMODES[$response[0]->game_mode];
						@$matchid = ($response[0]->match_id == null) ? @$matchid : $response[0]->match_id;
						$ranked = ($response[0]->lobby_type == 5 || $response[0]->lobby_type == 6 || $response[0]->lobby_type == 7) ? "Yes" : "No";
						$games++;
						updateMatch($details[$i]['user'], $response[0]->match_id);
						
					}
					
				}
				
			}

			if ($games > 0) {
				
				$embed = $discord->factory(Embed::class);
				$embed->setTitle("Dota 2 Match Information")
					->setURL("https://www.opendota.com/matches/".$matchid)
					->setImage("https://media.licdn.com/dms/image/C5612AQGLKrCEqkHZMw/article-cover_image-shrink_600_2000/0/1636444501645?e=2147483647&v=beta&t=Fd2nbDk9TUmsSm9c5Kt2wq9hP_bH1MxZITTa4pEx1wg")
					->setColor($keys['colour'])
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
	
	function checkNew($id, $matchID) {
		
		global $keys;

		$mysqli = mysqli_connect('localhost', 'buzz', $keys['mysql'], 'discord');
		$result = $mysqli->query("SELECT * FROM dota2 WHERE id='{$id}' AND matchid='{$matchID}'");
		$mysqli->close();
		if ($result->num_rows == 0) { return true; }
		else { return false; }
		
	}

	function updateMatch($id, $matchID) {
		
		global $keys;
		
		$mysqli = mysqli_connect('localhost', 'buzz', $keys['mysql'], 'discord');
		$result = $mysqli->query("UPDATE dota2 SET matchid='{$matchID}' WHERE id='{$id}'");
		$mysqli->close();
		
	}
	
	function checkReminders() {
		
		global $discord, $keys;
		
		if ($keys['beta'] === true) { return; }
		
		$time = time();
		$mysqli = mysqli_connect('localhost', 'buzz', $keys['mysql'], 'discord');
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
	
	/* function checkTrades() {
	
		global $discord, $keys;
		
		if ($keys['beta'] === true) { return; }
		
		$ids = file_exists('trades.txt') ? file('trades.txt',  FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
		$guild = $discord->guilds->get('id', '232691831090053120');
		$channel = $guild->channels->get('id', '1292725963754573844');
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
							->setAuthor('AFL - Trade Radio')
							->setDescription($article_text[1].". ".$article_text[2])
							->setURL("https://www.afl.com.au{$url[1]}")
							->setColor('237feb')
							->setImage($image[1])
							->setThumbnail('https://resources.afl.com.au/afl/photo/2024/09/29/c5264e60-a95e-41f0-9fe0-610f44d573ad/107886_Trade-Sponsorship_Digital-Assets_Tiles_Radio_340x176_FA_R-1x.jpg')
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
		
	} */
	
?>

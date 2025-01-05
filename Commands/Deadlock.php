<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;
	
	function Deadlock($message) { 
	
		global $discord, $keys;
		
		$date = new DateTime('now');
		$current_hour = (int)$date->format('G');
		if ($current_hour >= 10 || $current_hour <= 2) {

			$ids = array(
				array("381596223435702282", "33939542", "Dan"), 
				array("132458420375650304", "50577085", "Bryce"), 
			);
			
			for ($x = 0; $x < count($ids); $x++) {
				
				$url = "https://data.deadlock-api.com/v2/players/{$ids[$x][1]}/match-history";
				$content = @file_get_contents($url);
				if ($content === FALSE) { return; }
				$response = json_decode($content);
				$match = $response->matches[0];
				print_r($match);
				
				if (checkNew($ids[$x][1], $match->match_id, "Deadlock")) {
					
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
					
				}
				
			}
			
			if (count($details) > 1 && allMatchIDsMatch($details)) { 
		
				$embed = $discord->factory(Embed::class);
				$embed->setTitle("Deadlock Match Results")
					->setImage("https://buzzmoody.au/deadlock-banner.jpg")
					->setColor($keys['colour'])
					->setTimestamp()
					->setFooter("Powered by Deadlock-API");
					//->setURL("https://www.opendota.com/matches/".$matchid)
				$desc = "\n\n";
				
				foreach ($details as $player) {
					print_r($player);
					$desc .= "<@{$player['discord']}> **{$player['result']}** playing as **{$player['hero']}**\n\n";
					$embed->addFieldValues("\n\n".$player['name'], "{$player['hero']}\nLevel {$player['level']}\n{$player['kda']}\n{$player['lh']} LH\n{$player['denies']} Denies\n\${$player['worth']}\n\n", true);
					$mode = $player['mode'];
					$start = $player['time'];
					$length = $player['length'];
			
				}

				$embed->setDescription($desc."\n")
					->addFieldValues("\n\nGame Information", "Start Time: {$start}\nLength: {$length}\nGame Mode: {$mode}\n", false);
				
				$message->channel->sendEmbed($embed);
			
			}
			
			else {
				
				echo "They don't match!";

			}
		
		}
		
	}
	
?>
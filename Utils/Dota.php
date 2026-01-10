<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Builders\MessageBuilder;
	use Discord\Parts\Channel\Attachment;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	use React\Promise\all;
	
	class Dota {
	
		private $discord;
		private $pdo;
		private $utils;
		
		private const DOTA_LEVELS = [
			0, 0, 240, 640, 1160, 1760, 2440, 3200, 4000, 4900, 5900, 7000, 8200, 9500, 10900, 12400, 14000, 15700, 17500, 19400, 21400, 23600, 26000, 28600, 31400, 34400, 38400, 43400, 49400, 56400, 63900
		];
		
		private const DOTA_HEROES = [
			1 => "Anti-Mage", 2 => "Axe", 3 => "Bane", 4 => "Bloodseeker", 5 => "Crystal Maiden", 6 => "Drow Ranger", 7 => "Earthshaker", 8 => "Juggernaut", 9 => "Mirana", 11 => "Shadow Fiend", 10 => "Morphling", 12 => "Phantom Lancer", 13 => "Puck", 14 => "Pudge", 15 => "Razor", 16 => "Sand King", 17 => "Storm Spirit", 18 => "Sven", 19 => "Tiny", 20 => "Vengeful Spirit", 21 => "Windranger", 22 => "Zeus", 23 => "Kunkka", 25 => "Lina", 31 => "Lich", 26 => "Lion", 27 => "Shadow Shaman", 28 => "Slardar", 29 => "Tidehunter", 30 => "Witch Doctor", 32 => "Riki", 33 => "Enigma", 34 => "Tinker", 35 => "Sniper", 36 => "Necrophos", 37 => "Warlock", 38 => "Beastmaster", 39 => "Queen of Pain", 40 => "Venomancer", 41 => "Faceless Void", 42 => "Skeleton King", 43 => "Death Prophet", 44 => "Phantom Assassin", 45 => "Pugna", 46 => "Templar Assassin", 47 => "Viper", 48 => "Luna", 49 => "Dragon Knight", 50 => "Dazzle", 51 => "Clockwerk", 52 => "Leshrac", 53 => "Nature's Prophet", 54 => "Lifestealer", 55 => "Dark Seer", 56 => "Clinkz", 57 => "Omniknight", 58 => "Enchantress", 59 => "Huskar", 60 => "Night Stalker", 61 => "Broodmother", 62 => "Bounty Hunter", 63 => "Weaver", 64 => "Jakiro", 65 => "Batrider", 66 => "Chen", 67 => "Spectre", 69 => "Doom", 68 => "Ancient Apparition", 70 => "Ursa", 71 => "Spirit Breaker", 72 => "Gyrocopter", 73 => "Alchemist", 74 => "Invoker", 75 => "Silencer", 76 => "Outworld Devourer", 77 => "Lycan", 78 => "Brewmaster", 79 => "Shadow Demon", 80 => "Lone Druid", 81 => "Chaos Knight", 82 => "Meepo", 83 => "Treant Protector", 84 => "Ogre Magi", 85 => "Undying", 86 => "Rubick", 87 => "Disruptor", 88 => "Nyx Assassin", 89 => "Naga Siren", 90 => "Keeper of the Light", 91 => "IO", 92 => "Visage", 93 => "Slark", 94 => "Medusa", 95 => "Troll Warlord", 96 => "Centaur Warrunner", 97 => "Magnus", 98 => "Timbersaw", 99 => "Bristleback", 100 => "Tusk", 101 => "Skywrath Mage", 102 => "Abaddon", 103 => "Elder Titan", 104 => "Legion Commander", 106 => "Ember Spirit", 107 => "Earth Spirit", 108 => "Underlord", 109 => "Terrorblade", 110 => "Phoenix", 105 => "Techies", 111 => "Oracle", 112 => "Winter Wyvern", 113 => "Arc Warden", 114 => "Monkey King", 119 => "Dark Willow", 120 => "Pangolier", 121 => "Grimstroke", 123 => "Hoodwink", 126 => "Void Spirit", 128 => "Snapfire", 129 => "Mars", 131 => "Ringmaster", 135 => "Dawnbreaker", 136 => "Marci", 137 => "Primal Beast", 138 => "Muerta", 145 => "Kez", 155 => "Largo"
		];
		
		private const DOTA_GAMEMODES = [
			0 => "Unknown", 1 => "All Pick", 2 => "Captains Mode", 3 => "Random Draft", 4 => "Single Draft", 5 => "All Random", 6 => "Intro", 7 => "Diretide", 8 => "Reverse Captains Mode", 9 => "Greeviling", 10 => "Tutorial", 11 => "Mid Only", 12 => "Least Played", 13 => "Limited Heroes", 14 => "Compendium Matchmaking", 15 => "Custom", 16 => "Captains Draft", 17 => "Balanced Draft", 18 => "Ability Draft", 19 => "Event", 20 => "All Random Death Match", 21 => "1v1 Mid", 22 => "All Draft", 23 => "Turbo", 24 => "Mutation", 25 => "Coaches Challenge"
		];
		
		private const DOTA_EMOJI = [
			1 => "<:AntiMage:1458748061429006460>", 2 => "<:Axe:1458748065166135367>", 3 => "<:Bane:1458748067833450506>", 4 => "<:Bloodseeker:1458748073684631664>", 5 => "<:CrystalMaiden:1458748033104744470>", 6 => "<:DrowRanger:1458748049596747900>", 7 => "<:Earthshaker:1458748053161906281>", 8 => "<:Juggernaut:1458748023457710144>", 9 => "<:Mirana:1458747967224676499>", 11 => "<:ShadowFiend:1458747906755526736>", 10 => "<:Morphling:1458747947721429110>", 12 => "<:PhantomLancer:1458747945393324096>", 13 => "<:Puck:1458747929237127189>", 14 => "<:Pudge:1458747919145373860>", 15 => "<:Razor:1458747913441247386>", 16 => "<:SandKing:1458747911780434046>g", 17 => "<:StormSpirit:1458747892129992785>", 18 => "<:Sven:1458747882764238872>", 19 => "<:Tiny:1458747873658277919>", 20 => "<:VengefulSpirit:1458747863659053109>", 21 => "<:Windranger:1458747841764790277>", 22 => "<:Zeus:1458747839088689185>", 23 => "<:Kunkka:1458747978650091666>", 25 => "<:Lina:1458747991581003867>", 31 => "<:Lich:1458747987915178106>", 26 => "<:Lion:1458747993267240960>", 27 => "<:ShadowShaman:1458747908416344114>", 28 => "<:Slardar:1458747902817210489>", 29 => "<:Tidehunter:1458747879559659572>", 30 => "<:WitchDoctor:1458747845791191080>", 32 => "<:Riki:1458747914888417396>", 33 => "<:Enigma:1458748005808345098>", 34 => "<:Tinker:1458747871850528768>", 35 => "<:Sniper:1458747897599496376>", 36 => "<:Necrophos:1458747956261027931>", 37 => "<:Warlock:1458747849192767611>", 38 => "<:Beastmaster:1458748071683948635>", 39 => "<:QueenOfPain:1458747923398524971>", 40 => "<:Venomancer:1458747853408178247>", 41 => "<:FacelessVoid:1458748007985053736>", 42 => "<:WraithKing:1458747836601597952>", 43 => "<:DeathProphet:1458748041795338375>", 44 => "<:PhantomAssassin:1458747943933837353>", 45 => "<:Pugna:1458747920961634324>", 46 => "<:TemplarAssasin:1458747886215893155>", 47 => "<:Viper:1458747855295746159>", 48 => "<:Luna:1458747996521894032>", 49 => "<:DragonKnight:1458748048036331615>", 50 => "<:Dazzle:1458748040218411202>", 51 => "<:Clockwerk:1458748031083216917>", 52 => "<:Leshrac:1458747985566629963>", 53 => "<:NaturesProphet:1458747953635397642>", 54 => "<:Lifestealer:1458747989911670815>", 55 => "<:DarkSeer:1458748035017343016>", 56 => "<:Clinkz:1458748029593976946>", 57 => "<:Omniknight:1458747936291815541>", 58 => "<:Enchantress:1458748003463598202>", 59 => "<:Huskar:1458748016205893702>", 60 => "<:NightStalker:1458747931015254046>", 61 => "<:Broodmother:1458748082014650440>", 62 => "<:BountyHunter:1458748076339625994>", 63 => "<:Weaver:1458747851612880949>", 64 => "<:Jakiro:1458748021490843772>", 65 => "<:Batrider:1458748069897179197>", 66 => "<:Chen:1458748027270332447>", 67 => "<:Spectre:1458747887642087557>", 69 => "<:Doom:1458748045486330032>", 68 => "<:AncientApparition:1458748059050709073>", 70 => "<:Ursa:1458747861381681223>", 71 => "<:SpiritBreaker:1458747889743560744>", 72 => "<:Gyrocopter:1458748011797545061>", 73 => "<:Alchemist:1458748056840437771>", 74 => "<:Invoker:1458748017946660906>", 75 => "<:Silencer:1458747899553775666>", 76 => "<:OutworldDestroyer:1458747940100378808>", 77 => "<:Lycan:1458747998535418062>", 78 => "<:Brewmaster:1458748078226931886>", 79 => "<:ShadowDemon:1458747905035993136>", 80 => "<:LoneDruid:1458747995011940427>", 81 => "<:ChaosKnight:1458748025265459395>", 82 => "<:Meepo:1458747965614325924>", 83 => "<:TreantProtector:1458747875495379145>", 84 => "<:OgreMagi:1458747934387605514>", 85 => "<:Undying:1458747859305365607>", 86 => "<:Rubick:1458747910132076656>", 87 => "<:Disruptor:1458748043414339678>", 88 => "<:NyxAssassin:1458747932785508415>", 89 => "<:NagaSiren:1458747951210958868>", 90 => "<:KeeperOfTheLight:1458747971419111502>", 91 => "<:Io:1458748019460669492>", 92 => "<:Visage:1458747857485041850>", 93 => "<:Slark:1458747893774029027>", 94 => "<:Medusa:1458747963315716169>", 95 => "<:TrollWarlord:1458747865667993763>", 96 => "<:Centaur:1458748083956355072>", 97 => "<:Magnus:1458747957796143165>", 98 => "<:Timbersaw:1458747881228865648>", 99 => "<:Bristleback:1458748080034943091>", 100 => "<:Tusk:1458747868453142681>", 101 => "<:Skywrath:1458747901248409642>", 102 => "<:Abaddon:1458748055099805739>", 103 => "<:ElderTitan:1458748000288505921>", 104 => "<:Legion:1458747983490191380>", 106 => "<:EmberSpirit:1458748001978679307>", 107 => "<:EarthSpirit:1458748051324797128>", 108 => "<:Underlord:1458747870252367954>", 109 => "<:Terrorblade:1458747877701451900>", 110 => "<:Phoenix:1458747925143490590>", 105 => "<:Techies:1458747884525850716>", 111 => "<:Oracle:1458747937931788396>", 112 => "<:WinterWyvern:1458747843652096000>", 113 => "<:Arc:1458748063542808679>", 114 => "<:MonkeyKing:1458747969376620658>", 119 => "<:DarkWillow:1458748036304998497>w", 120 => "<:Pangolier:1458747942130155522>", 121 => "<:Grimstroke:1458748010014965781>", 123 => "<:Hoodwink:1458748014259736606>", 126 => "<:VoidSpirit:1458747847582158858>", 128 => "<:Snapfire:1458747895976169472>", 129 => "<:Mars:1458747961390534728>", 131 => "<:Ringmaster:1458747916687769681>", 135 => "<:Dawnbreaker:1458748038293225646>", 136 => "<:Marci:1458747959742038047>", 137 => "<:PrimalBeast:1458747926879670313>", 138 => "<:Muerta:1458747949239767162>", 145 => "<:Kez:1458747973474451609>", 155 => "<:Largo:1458747981284249662>"
		];
		
		public function __construct(Discord\Discord $discord, PDO $pdo, $utils) {
			
			$this->discord = $discord;
			$this->pdo = $pdo;
			$this->utils = $utils;
			
		}
		
		public function checkGames(): void {
	
			// if ($this->utils->betaCheck()) { return; }
			
			$date = new DateTime('now');
			$current_hour = (int)$date->format('G');
			
			if ($current_hour >= 10 || $current_hour <= 2) {
				
				$client = new Browser($this->discord->getLoop());
				
				$ids = [
					["<@232691181396426752>", "54716121", "Buzz"], 
					["<@381596223435702282>", "33939542", "Dan"], 
					["<@276222661515018241>", "77113202", "Hassler"], 
				];
				
				$promises = [];
				
				foreach ($ids as $user) {
					
					list($discordID, $steamID, $name) = $user;
					
					$api = "https://api.opendota.com/api/players/{$steamID}/recentMatches";

					$promises[$steamID] = $client->get($api)->then(
						function (ResponseInterface $response) use ($user) {
							
							return [
								'info' => $user,
								'matches' => json_decode((string)$response->getBody(), true)
							];
							
						}
					);
					
				}
				
				\React\Promise\all($promises)->then(function (array $results) {
				
					$newMatches = [];
					
					foreach ($results as $steamID => $data) {
						
						if (empty($data['matches'])) continue;
						
						$latestMatch = $data['matches'][0];
						$matchID = $latestMatch['match_id'];
						
						if ($this->isNewMatch($steamID, $matchID)) {
							
							list($discordID, $steamID, $name) = $data['info'];
							
							$newMatches[$matchID][] = [
								'name' => $name,
								'discord_id' => $discordID,
								'stats' => $latestMatch,
								'winloss' => ''
							];
							
							for ($x = 0; $x < 10; $x++) {
								
								$latestPlayer = array_key_last($newMatches[$matchID]);
								$team = ($data['matches'][$x]['player_slot'] <= 127) ? 'Radiant' : 'Dire';
								$newMatches[$matchID][$latestPlayer]['winloss'] .= ($data['matches'][$x]['radiant_win'] === ($team === 'Radiant')) ? '🟩 ' : '🟥 ';
								
							}

							$this->saveMatch($steamID, $matchID);
							
						}
						
					}
					
					foreach ($newMatches as $matchID => $playersInMatch) {
					
						$this->postToDiscord($matchID, $playersInMatch);
						
					}
					
				});
				
			}
			
		}
		
		private function isNewMatch($steamID, $matchID): bool {
			
			$query = $this->pdo->prepare("SELECT matchid FROM dota2 WHERE id = :id");
			$query->execute(['id' => (string)$steamID]);
			$lastMatchId = $query->fetchColumn();

			if ($lastMatchId == 1) {
				$this->saveMatch($steamID, $matchID);
				return false;
			}

			return $lastMatchId != $matchID;
			
		}
		
		private function saveMatch($steamID, $matchID): void {
			
			$query = $this->pdo->prepare("UPDATE dota2 SET matchid = :matchid WHERE id = :id");
			$query->execute([
				'matchid' => (string)$matchID, 
				'id' => (string)$steamID
			]);
		
		}
		
		private function postToDiscord($matchID, $playersInMatch): void {
			
			$tz = new DateTime("now", new DateTimeZone('Australia/Melbourne'));
			$tz->setTimestamp($playersInMatch[0]['stats']['start_time']);
			$length = gmdate(floor(($d = $playersInMatch[0]['stats']['duration']) / 3600) ? 'g \h\o\u\r\s i \m\i\n\s' : 'i \m\i\n\s', $d);
			$mode = self::DOTA_GAMEMODES[$playersInMatch[0]['stats']['game_mode']];
			$ranked = in_array($playersInMatch[0]['stats']['lobby_type'], [5, 6, 7], true) ? 'Ranked' : 'Unranked';
			$team = ($playersInMatch[0]['stats']['player_slot'] <= 127) ? 'Radiant' : 'Dire';
			$result = ($playersInMatch[0]['stats']['radiant_win'] === ($team === 'Radiant')) ? 'Won' : 'Lost';
			$players = implode(", ", array_column($playersInMatch, 'discord_id'));
			
			$embed = $this->discord->factory(Embed::class);
			$embed->setAuthor("Dota 2 Match Information", "https://img.icons8.com/?size=100&id=35611&format=png&color=000000", "https://www.opendota.com/matches/{$matchID}")
				->setThumnail("https://img.icons8.com/?size=100&id=35611&format=png&color=000000")
				->setColor(getenv('COLOUR'))
				->addFieldValues("Start Time", $tz->format('g:i A'), true)
				->addFieldValues("Length", $length, true)
				->addFieldValues("Game Mode", "{$ranked} {$mode}", true)
				->setDescription("{$players} **{$result}** their latest game playing on **{$team}**!\n");
				
			foreach ($playersInMatch as $player) {
				
				$hero = self::DOTA_HEROES[$player['stats']['hero_id']] ?? 'Unknown';
				$emoji = self::DOTA_EMOJI[$player['stats']['hero_id']] ?? '❓';
				$level = $this->calcLevel($player['stats']['xp_per_min'], $player['stats']['duration']);
				
				$embed->addFieldValues($player['name'], "{$emoji} {$hero}\n{$player['stats']['kills']} / {$player['stats']['deaths']} / {$player['stats']['assists']}\nLvl {$level}", true)
					->addFieldValues("Dmg / Heal", number_format($player['stats']['hero_damage'])." dmg\n".number_format($player['stats']['tower_damage'])." tower\n".number_format($player['stats']['hero_healing'])." heal\n", true)
					->addFieldValues("Stats", "{$player['stats']['last_hits']} lh\n".number_format($player['stats']['xp_per_min'])." xpm\n{$player['stats']['gold_per_min']} gpm", true)
					->addFieldValues("Last 10 Games", $player['winloss'], false);

			}
			
			$guild = $this->discord->guilds->get('id', '232691831090053120');
			// $channel = $guild->channels->get('id', '232691831090053120'); // #main
			$channel = $guild->channels->get('id', '274828566909157377'); // #dev

			$channel->sendEmbed($embed);
			
		}
	
		private function calcLevel($xpm, $duration): int {
			
			$exp = ($xpm * ($duration / 60));
			
			for ($level = count(self::DOTA_LEVELS) - 1; $level >= 1; $level--) {
				if ($exp >= self::DOTA_LEVELS[$level]) {
					return $level;
				}
			}
			
			return 1;
			
		}
	
	}
	
?>
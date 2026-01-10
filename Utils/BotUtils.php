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
			1 => "Anti-Mage", 2 => "Axe", 3 => "Bane", 4 => "Bloodseeker", 5 => "Crystal Maiden", 6 => "Drow Ranger", 7 => "Earthshaker", 8 => "Juggernaut", 9 => "Mirana", 11 => "Shadow Fiend", 10 => "Morphling", 12 => "Phantom Lancer", 13 => "Puck", 14 => "Pudge", 15 => "Razor", 16 => "Sand King", 17 => "Storm Spirit", 18 => "Sven", 19 => "Tiny", 20 => "Vengeful Spirit", 21 => "Windranger", 22 => "Zeus", 23 => "Kunkka", 25 => "Lina", 31 => "Lich", 26 => "Lion", 27 => "Shadow Shaman", 28 => "Slardar", 29 => "Tidehunter", 30 => "Witch Doctor", 32 => "Riki", 33 => "Enigma", 34 => "Tinker", 35 => "Sniper", 36 => "Necrophos", 37 => "Warlock", 38 => "Beastmaster", 39 => "Queen of Pain", 40 => "Venomancer", 41 => "Faceless Void", 42 => "Skeleton King", 43 => "Death Prophet", 44 => "Phantom Assassin", 45 => "Pugna", 46 => "Templar Assassin", 47 => "Viper", 48 => "Luna", 49 => "Dragon Knight", 50 => "Dazzle", 51 => "Clockwerk", 52 => "Leshrac", 53 => "Nature's Prophet", 54 => "Lifestealer", 55 => "Dark Seer", 56 => "Clinkz", 57 => "Omniknight", 58 => "Enchantress", 59 => "Huskar", 60 => "Night Stalker", 61 => "Broodmother", 62 => "Bounty Hunter", 63 => "Weaver", 64 => "Jakiro", 65 => "Batrider", 66 => "Chen", 67 => "Spectre", 69 => "Doom", 68 => "Ancient Apparition", 70 => "Ursa", 71 => "Spirit Breaker", 72 => "Gyrocopter", 73 => "Alchemist", 74 => "Invoker", 75 => "Silencer", 76 => "Outworld Devourer", 77 => "Lycan", 78 => "Brewmaster", 79 => "Shadow Demon", 80 => "Lone Druid", 81 => "Chaos Knight", 82 => "Meepo", 83 => "Treant Protector", 84 => "Ogre Magi", 85 => "Undying", 86 => "Rubick", 87 => "Disruptor", 88 => "Nyx Assassin", 89 => "Naga Siren", 90 => "Keeper of the Light", 91 => "IO", 92 => "Visage", 93 => "Slark", 94 => "Medusa", 95 => "Troll Warlord", 96 => "Centaur Warrunner", 97 => "Magnus", 98 => "Timbersaw", 99 => "Bristleback", 100 => "Tusk", 101 => "Skywrath Mage", 102 => "Abaddon", 103 => "Elder Titan", 104 => "Legion Commander", 106 => "Ember Spirit", 107 => "Earth Spirit", 108 => "Underlord", 109 => "Terrorblade", 110 => "Phoenix", 105 => "Techies", 111 => "Oracle", 112 => "Winter Wyvern", 113 => "Arc Warden", 114 => "Monkey King", 119 => "Dark Willow", 120 => "Pangolier", 121 => "Grimstroke", 123 => "Hoodwink", 126 => "Void Spirit", 128 => "Snapfire", 129 => "Mars", 131 => "Ringmaster", 135 => "Dawnbreaker", 136 => "Marci", 137 => "Primal Beast", 138 => "Muerta", 145 => "Kez", 155 => "Largo"
		];
		
		private const DOTA_GAMEMODES = [
			0 => "Unknown", 1 => "All Pick", 2 => "Captains Mode", 3 => "Random Draft", 4 => "Single Draft", 5 => "All Random", 6 => "Intro", 7 => "Diretide", 8 => "Reverse Captains Mode", 9 => "Greeviling", 10 => "Tutorial", 11 => "Mid Only", 12 => "Least Played", 13 => "Limited Heroes", 14 => "Compendium Matchmaking", 15 => "Custom", 16 => "Captains Draft", 17 => "Balanced Draft", 18 => "Ability Draft", 19 => "Event", 20 => "All Random Death Match", 21 => "1v1 Mid", 22 => "All Draft", 23 => "Turbo", 24 => "Mutation", 25 => "Coaches Challenge"
		];
		
		private const DOTA_EMOJI = [
			1 => "<:AntiMage:1458748061429006460>", 2 => "<:Axe:1458748065166135367>", 3 => "<:Bane:1458748067833450506>", 4 => "<:Bloodseeker:1458748073684631664>", 5 => "<:CrystalMaiden:1458748033104744470>", 6 => "<:DrowRanger:1458748049596747900>", 7 => "<:Earthshaker:1458748053161906281>", 8 => "<:Juggernaut:1458748023457710144>", 9 => "<:Mirana:1458747967224676499>", 11 => "<:ShadowFiend:1458747906755526736>", 10 => "<:Morphling:1458747947721429110>", 12 => "<:PhantomLancer:1458747945393324096>", 13 => "<:Puck:1458747929237127189>", 14 => "<:Pudge:1458747919145373860>", 15 => "<:Razor:1458747913441247386>", 16 => "<:SandKing:1458747911780434046>g", 17 => "<:StormSpirit:1458747892129992785>", 18 => "<:Sven:1458747882764238872>", 19 => "<:Tiny:1458747873658277919>", 20 => "<:VengefulSpirit:1458747863659053109>", 21 => "<:Windranger:1458747841764790277>", 22 => "<:Zeus:1458747839088689185>", 23 => "<:Kunkka:1458747978650091666>", 25 => "<:Lina:1458747991581003867>", 31 => "<:Lich:1458747987915178106>", 26 => "<:Lion:1458747993267240960>", 27 => "<:ShadowShaman:1458747908416344114>", 28 => "<:Slardar:1458747902817210489>", 29 => "<:Tidehunter:1458747879559659572>", 30 => "<:WitchDoctor:1458747845791191080>", 32 => "<:Riki:1458747914888417396>", 33 => "<:Enigma:1458748005808345098>", 34 => "<:Tinker:1458747871850528768>", 35 => "<:Sniper:1458747897599496376>", 36 => "<:Necrophos:1458747956261027931>", 37 => "<:Warlock:1458747849192767611>", 38 => "<:Beastmaster:1458748071683948635>", 39 => "<:QueenOfPain:1458747923398524971>", 40 => "<:Venomancer:1458747853408178247>", 41 => "<:FacelessVoid:1458748007985053736>", 42 => "<:WraithKing:1458747836601597952>", 43 => "<:DeathProphet:1458748041795338375>", 44 => "<:PhantomAssassin:1458747943933837353>", 45 => "<:Pugna:1458747920961634324>", 46 => "<:TemplarAssasin:1458747886215893155>", 47 => "<:Viper:1458747855295746159>", 48 => "<:Luna:1458747996521894032>", 49 => "<:DragonKnight:1458748048036331615>", 50 => "<:Dazzle:1458748040218411202>", 51 => "<:Clockwerk:1458748031083216917>", 52 => "<:Leshrac:1458747985566629963>", 53 => "<:NaturesProphet:1458747953635397642>", 54 => "<:Lifestealer:1458747989911670815>", 55 => "<:DarkSeer:1458748035017343016>", 56 => "<:Clinkz:1458748029593976946>", 57 => "<:Omniknight:1458747936291815541>", 58 => "<:Enchantress:1458748003463598202>", 59 => "<:Huskar:1458748016205893702>", 60 => "<:NightStalker:1458747931015254046>", 61 => "<:Broodmother:1458748082014650440>", 62 => "<:BountyHunter:1458748076339625994>", 63 => "<:Weaver:1458747851612880949>", 64 => "<:Jakiro:1458748021490843772>", 65 => "<:Batrider:1458748069897179197>", 66 => "<:Chen:1458748027270332447>", 67 => "<:Spectre:1458747887642087557>", 69 => "<:Doom:1458748045486330032>", 68 => "<:AncientApparition:1458748059050709073>", 70 => "<:Ursa:1458747861381681223>", 71 => "<:SpiritBreaker:1458747889743560744>", 72 => "<:Gyrocopter:1458748011797545061>", 73 => "<:Alchemist:1458748056840437771>", 74 => "<:Invoker:1458748017946660906>", 75 => "<:Silencer:1458747899553775666>", 76 => "<:OutworldDestroyer:1458747940100378808>", 77 => "<:Lycan:1458747998535418062>", 78 => "<:Brewmaster:1458748078226931886>", 79 => "<:ShadowDemon:1458747905035993136>", 80 => "<:LoneDruid:1458747995011940427>", 81 => "<:ChaosKnight:1458748025265459395>", 82 => "<:Meepo:1458747965614325924>", 83 => "<:TreantProtector:1458747875495379145>", 84 => "<:OgreMagi:1458747934387605514>", 85 => "<:Undying:1458747859305365607>", 86 => "<:Rubick:1458747910132076656>", 87 => "<:Disruptor:1458748043414339678>", 88 => "<:NyxAssassin:1458747932785508415>", 89 => "<:NagaSiren:1458747951210958868>", 90 => "<:KeeperOfTheLight:1458747971419111502>", 91 => "<:Io:1458748019460669492>", 92 => "<:Visage:1458747857485041850>", 93 => "<:Slark:1458747893774029027>", 94 => "<:Medusa:1458747963315716169>", 95 => "<:TrollWarlord:1458747865667993763>", 96 => "<:Centaur:1458748083956355072>", 97 => "<:Magnus:1458747957796143165>", 98 => "<:Timbersaw:1458747881228865648>", 99 => "<:Bristleback:1458748080034943091>", 100 => "<:Tusk:1458747868453142681>", 101 => "<:Skywrath:1458747901248409642>", 102 => "<:Abaddon:1458748055099805739>", 103 => "<:ElderTitan:1458748000288505921>", 104 => "<:Legion:1458747983490191380>", 106 => "<:EmberSpirit:1458748001978679307>", 107 => "<:EarthSpirit:1458748051324797128>", 108 => "<:Underlord:1458747870252367954>", 109 => "<:Terrorblade:1458747877701451900>", 110 => "<:Phoenix:1458747925143490590>", 105 => "<:Techies:1458747884525850716>", 111 => "<:Oracle:1458747937931788396>", 112 => "<:WinterWyvern:1458747843652096000>", 113 => "<:Arc:1458748063542808679>", 114 => "<:MonkeyKing:1458747969376620658>", 119 => "<:DarkWillow:1458748036304998497>w", 120 => "<:Pangolier:1458747942130155522>", 121 => "<:Grimstroke:1458748010014965781>", 123 => "<:Hoodwink:1458748014259736606>", 126 => "<:VoidSpirit:1458747847582158858>", 128 => "<:Snapfire:1458747895976169472>", 129 => "<:Mars:1458747961390534728>", 131 => "<:Ringmaster:1458747916687769681>", 135 => "<:Dawnbreaker:1458748038293225646>", 136 => "<:Marci:1458747959742038047>", 137 => "<:PrimalBeast:1458747926879670313>", 138 => "<:Muerta:1458747949239767162>", 145 => "<:Kez:1458747973474451609>", 155 => "<:Largo:1458747981284249662>"
		];
		
		public function __construct(Discord\Discord $discord, PDO $pdo) {
			
			$this->discord = $discord;
			$this->pdo = $pdo;
			
		}
		
		public function betaCheck() {
			
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
					
					$epiTime = (new DateTimeImmutable($quake->properties->origin_time, new DateTimeZone('UTC')))
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
		
		public function getLocale($locale): array {
		
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
		
		public function SearchFunc($type, $message, $args): void {
	
			if (empty($args)) { 
				$this->simpleEmbed("Google Search", "https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/24px-Google_%22G%22_logo.svg.png", "Invalid syntax used. Please provide search terms.", $message, true, null);
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
				$this->simpleEmbed("Google Search", "https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/24px-Google_%22G%22_logo.svg.png", "No results found for *{$args}*.", $message, true, null);
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
		
		public function checkDota(): void {
	
			if ($this->betaCheck()) { return; }
			
			$date = new DateTime('now');
			$current_hour = (int)$date->format('G');
			if ($current_hour >= 10 || $current_hour <= 2) {
				
				$ids = [
					["<@232691181396426752>", "54716121", "Buzz"], 
					["<@381596223435702282>", "33939542", "Dan"], 
					["<@276222661515018241>", "77113202", "Hassler"], 
				];
				
				$newMatches = [];
				
				foreach ($ids as $user) {
					
					list($discordID, $steamID, $name) = $user;
					
					$api = "https://api.opendota.com/api/players/{$steamID}/recentMatches";
					$response = file_get_contents($api);
					$matches = json_decode($response, true);
					
					if (empty($matches)) continue;
					
					$latestMatch = $matches[0];
					$matchID = $latestMatch['match_id'];
					
					if ($this->isNewMatch($steamID, $matchID)) {
						
						$newMatches[$matchID][] = [
							'name' => $name,
							'discord_id' => $discordID,
							'stats' => $latestMatch,
							'winloss' => ''
						];
						
						for ($x = 0; $x < 10; $x++) {
							
							$latestPlayer = array_key_last($newMatches[$matchID]);
							$team = ($matches[$x]['player_slot'] <= 127) ? 'Radiant' : 'Dire';
							$newMatches[$matchID][$latestPlayer]['winloss'] .= ($matches[$x]['radiant_win'] === ($team === 'Radiant')) ? '🟩 ' : '🟥 ';
							
						}

						$this->saveMatch($steamID, $matchID);
						
					}
					
				}
				
				foreach ($newMatches as $matchID => $playersInMatch) {
					
					$this->postToDiscord($matchID, $playersInMatch);
					
				}
				
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
			$embed->setAuthor("Dota 2 Match Information", "attachment://dota.png", "https://www.opendota.com/matches/{$matchID}")
				->setImage("https://media.licdn.com/dms/image/C5612AQGLKrCEqkHZMw/article-cover_image-shrink_600_2000/0/1636444501645?e=2147483647&v=beta&t=Fd2nbDk9TUmsSm9c5Kt2wq9hP_bH1MxZITTa4pEx1wg")
				->setColor(getenv('COLOUR'))
				->addFieldValues("Start Time", $tz->format('g:i A'), true)
				->addFieldValues("Length", $length, true)
				->addFieldValues("Game Mode", "{$ranked} {$mode}", true)
				->setDescription("{$players} **{$result}** their latest game playing on **{$team}**!\n");
				
			foreach ($playersInMatch as $player) {
				
				$hero = self::DOTA_HEROES[$player['stats']['hero_id']];
				$emoji = self::DOTA_EMOJI[$player['stats']['hero_id']];
				$level = $this->calcLevel($player['stats']['xp_per_min'], $player['stats']['duration']);
				
				$embed->addFieldValues($player['name'], "{$hero} {$emoji}\n{$player['stats']['kills']} / {$player['stats']['deaths']} / {$player['stats']['assists']}\nLvl {$level}", true)
					->addFieldValues("Dmg / Heal", number_format($player['stats']['hero_damage'])." dmg\n".number_format($player['stats']['tower_damage'])." tower\n".number_format($player['stats']['hero_healing'])." heal\n", true)
					->addFieldValues("Stats", "{$player['stats']['last_hits']} lh\n".number_format($player['stats']['xp_per_min'])." xpm\n{$player['stats']['gold_per_min']} gpm", true)
					->addFieldValues("Last 10 Games", $player['winloss'], false);

			}
			
			$builder = MessageBuilder::new()
				->addEmbed($embed)
				->addFile("/Media/dota.png", "dota.png");
			
			$guild = $this->discord->guilds->get('id', '232691831090053120');
			$channel = $guild->channels->get('id', '232691831090053120');

			$channel->sendMessage($builder);
			
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
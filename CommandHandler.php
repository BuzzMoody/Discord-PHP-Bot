<?php

class Commands {
	
	public $keys;
	public $uptime;
	public $patterns;
	public $discord;
	
	private static $functions = [];
	
	const DOTA_HEROES = [
		1 => "Anti-Mage", 2 => "Axe", 3 => "Bane", 4 => "Bloodseeker", 5 => "Crystal Maiden", 6 => "Drow Ranger", 7 => "Earthshaker", 8 => "Juggernaut", 9 => "Mirana", 11 => "Shadow Fiend", 10 => "Morphling", 12 => "Phantom Lancer", 13 => "Puck", 14 => "Pudge", 15 => "Razor", 16 => "Sand King", 17 => "Storm Spirit", 18 => "Sven", 19 => "Tiny", 20 => "Vengeful Spirit", 21 => "Windranger", 22 => "Zeus", 23 => "Kunkka", 25 => "Lina", 31 => "Lich", 26 => "Lion", 27 => "Shadow Shaman", 28 => "Slardar", 29 => "Tidehunter", 30 => "Witch Doctor", 32 => "Riki", 33 => "Enigma", 34 => "Tinker", 35 => "Sniper", 36 => "Necrophos", 37 => "Warlock", 38 => "Beastmaster", 39 => "Queen of Pain", 40 => "Venomancer", 41 => "Faceless Void", 42 => "Skeleton King", 43 => "Death Prophet", 44 => "Phantom Assassin", 45 => "Pugna", 46 => "Templar Assassin", 47 => "Viper", 48 => "Luna", 49 => "Dragon Knight", 50 => "Dazzle", 51 => "Clockwerk", 52 => "Leshrac", 53 => "Nature's Prophet", 54 => "Lifestealer", 55 => "Dark Seer", 56 => "Clinkz", 57 => "Omniknight", 58 => "Enchantress", 59 => "Huskar", 60 => "Night Stalker", 61 => "Broodmother", 62 => "Bounty Hunter", 63 => "Weaver", 64 => "Jakiro", 65 => "Batrider", 66 => "Chen", 67 => "Spectre", 69 => "Doom", 68 => "Ancient Apparition", 70 => "Ursa", 71 => "Spirit Breaker", 72 => "Gyrocopter", 73 => "Alchemist", 74 => "Invoker", 75 => "Silencer", 76 => "Outworld Devourer", 77 => "Lycanthrope", 78 => "Brewmaster", 79 => "Shadow Demon", 80 => "Lone Druid", 81 => "Chaos Knight", 82 => "Meepo", 83 => "Treant Protector", 84 => "Ogre Magi", 85 => "Undying", 86 => "Rubick", 87 => "Disruptor", 88 => "Nyx Assassin", 89 => "Naga Siren", 90 => "Keeper of the Light", 91 => "IO", 92 => "Visage", 93 => "Slark", 94 => "Medusa", 95 => "Troll Warlord", 96 => "Centaur Warrunner", 97 => "Magnus", 98 => "Timbersaw", 99 => "Bristleback", 100 => "Tusk", 101 => "Skywrath Mage", 102 => "Abaddon", 103 => "Elder Titan", 104 => "Legion Commander", 106 => "Ember Spirit", 107 => "Earth Spirit", 108 => "Abyssal Underlord", 109 => "Terrorblade", 110 => "Phoenix", 105 => "Techies", 111 => "Oracle", 112 => "Winter Wyvern", 113 => "Arc Warden", 114 => "Monkey King", 119 => "Dark Willow", 120 => "Pangolier", 121 => "Grimstroke", 123 => "Hoodwink", 126 => "Void Spirit", 128 => "Snapfire", 129 => "Mars", 131 => "Ringmaster", 135 => "Dawnbreaker", 136 => "Marci", 137 => "Primal Beast", 138 => "Muerta", 145 => "Kez"
	];
	
	const DOTA_GAMEMODES = [
		0 => "Unknown", 1 => "All Pick", 2 => "Captains Mode", 3 => "Random Draft", 4 => "Single Draft", 5 => "All Random", 6 => "Intro", 7 => "Diretide", 8 => "Reverse Captains Mode", 9 => "Greeviling", 10 => "Tutorial", 11 => "Mid Only", 12 => "Least Played", 13 => "Limited Heroes", 14 => "Compendium Matchmaking", 15 => "Custom", 16 => "Captains Draft", 17 => "Balanced Draft", 18 => "Ability Draft", 19 => "Event", 20 => "All Random Death Match", 21 => "1v1 Mid", 22 => "All Draft", 23 => "Turbo", 24 => "Mutation", 25 => "Coaches Challenge"
	];
	
	const DL_HEROES = [
		1 => "Infernus", 2 => "Seven", 3 => "Vindicta", 4 => "Lady Geist", 6 => "Abrams", 7 => "Wraith", 8 => "McGinnis", 10 => "Paradox", 11 => "Dynamo", 12 => "Kelvin", 13 => "Haze", 14 => "Holliday", 15 => "Bebop", 16 => "Calico", 17 => "Grey Talon", 18 => "Mo & Krill", 19 => "Shiv", 20 => "Ivy", 21 => "Kali", 25 => "Warden", 27 => "Yamato", 31 => "Lash", 35 => "Viscous", 38 => "Gunslinger", 39 => "The Boss", 47 => "Tokamak", 48 => "Wrecker", 49 => "Rutger", 50 => "Pocket", 51 => "Thumper", 52 => "Mirage", 53 => "Fathom", 54 => "Cadence", 56 => "Bomber", 57 => "Shield Guy", 58 => "Viper", 59 => "Vandal", 60 => "Magician"
	];
	
	const DL_GAMEMODES = [
		0 => "Invalid", 1 => "Unranked", 2 => "Private Lobby", 3 => "Co-Op Bots", 4 => "Ranked", 5 => "Server Test", 6 => "Tutorial"
	];
	
	public function __construct($keys, $uptime, $discord) {
		
		$this->keys = $keys;
		$this->uptime = $uptime;
		$this->discord = $discord;
		$this->patterns = [
			'/^(kate|t(?:ay(lor)?|swizzle)|emma|e?liz(abeth)?|olympia|olivia|kim|mckayla|zach|hilary|ronan|sydney)$/' => 'BabeImages',
			'/^(search|google|bing|find|siri)/' => 'SearchGoogle',
			'/^(image|img|photo|pic)/' => 'SearchImage',
			'/^(ban|kick|sb|sinbin)/' => 'SinBin',
			'/^(bard|gemini|(open)?ai)/' => 'Vertex',
			'/^(asx|share(s)?|stock(s)?|etf)/' => 'ASX',
			'/^(weather|temp(erature)?)/' => 'Weather',
			'/^(forecast)$/' => 'Forecast',
			'/^(shell|bash|cli|cmd)/' => 'RunCLI',
			'/^(remind(?:me|er))/' => 'Reminder',
			'/^(f(ormula)?1)$/' => 'F1',
			'/^(roll|dice)/' => 'Dice',
			'/^(s(?:table)?d(?:iffusion)?)/' => 'StableDiffusion',
			'/^(u(rban)?d(ictionary)?)/' => 'UrbanDic',
			'/^ping$/' => 'Ping',
			'/^radar$/' => 'Radar',
			'/^apex$/' => 'Apex',
			'/^uptime$/' => 'Uptime',
			'/^reload$/' => 'Reload',
			'/^betarl$/' => 'BetaReload',
			'/^deadlock$/' => 'Deadlock',
		];
		
		$this->funcLoad();
		
	}
	
	public function funcExec($message) {
	
		$content = trim($message->content);
		preg_match('/^!(\w+)(?:\s+(.*))?$/is', $content, $matches);
		$command = strtolower($matches[1]);
		$args = $matches[2] ?? '';		
		foreach ($this->patterns as $pattern => $func_name) {
			if (preg_match($pattern, $command, $matches)) {
				$this->funcCall($func_name, $message, $args, $matches);
				break;
			}
		}
		
	}
	
	private function funcCall($func_name, $message, $args, $matches) {
		
		if (isset(self::$functions[$func_name]) && function_exists($func_name)) {
			$func_name($message, $args, $matches);
		}
		
	}
	
	private function funcLoad($dir = "Commands") {
		
		$files = scandir($dir);
		foreach ($files as $file) {
			if (substr($file, -3) === 'php') {
				$func_name = pathinfo($file, PATHINFO_FILENAME);
				include_once("{$dir}/{$file}");
				self::$functions[$func_name] = $func_name;
			}
		}

	}
	
}

?>
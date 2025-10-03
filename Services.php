<?php

	use Discord\Parts\User\Activity;

	class Services {

		private $discord;
		private $pdo;
		private $uptime;
		private $commands;
		
		private const PLAYFUL_INSULTS = ["Degenerates", "Scoundrels", "Rascals", "Ruffians", "Miscreants", "Reprobates", "Villains", "Knaves", "Lowlifes", "Scallywags", "Numbskulls", "Nincompoops", "Rapscallions", "Delinquents", "Ne'er-do-wells", "Wastrels", "Fools", "Buffoons", "Loons", "Cads", "Creeps", "Charlatans", "Twits", "Scamps", "Weasels", "Goons", "Clowns", "Bozos", "Doofuses", "Louts", "Boneheads", "Dingbats", "Meatheads", "Dunces", "Blockheads", "Muttonheads", "Simpletons"];

		public function __construct($discord, PDO $pdo, $uptime, Commands $commands) {
			
			$this->discord = $discord;
			$this->pdo = $pdo;
			$this->uptime = $uptime;
			$this->commands = $commands;
			
		}

		public function checkDatabase() {
			
			$tables = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name IN ('reminders', 'dota2', 'earthquakes')")->fetchAll();
			print_r($tables);
			if (count($tables) != 3) { 
				shell_exec('sqlite3 /Media/discord.db < /init/init.sql'); 
				echo "Database has been initiated\n";
			}
			echo "Database already contains valid data\n";
			
		}
		
		private function getMemberCount(): int {
			
			$countGuild = $this->discord->guilds->get('id', '232691831090053120');
			$onlineMembers = $countGuild->members->filter(function ($member) {
				return $member->status !== null && $member->status !== 'offline';
			});
			return $onlineMembers->count() - 1;
			
		}

		public function updateActivity() {
			
			$activity = $this->discord->factory(Activity::class, [
				'name' => $this->getMemberCount($this->discord)." ".self::PLAYFUL_INSULTS[array_rand(self::PLAYFUL_INSULTS)],
				'type' => Activity::TYPE_LISTENING,
			]);
			$this->discord->updatePresence($activity);
			
		}

	}

?>
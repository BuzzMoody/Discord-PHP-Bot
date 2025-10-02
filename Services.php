<?php

	use Discord\Parts\User\Activity;

	class Services {

		private $discord;
		private $pdo;
		private $commands;
		private $uptime;

		public function __construct($discord, PDO $pdo, $uptime, Commands $commands) {
			$this->discord = $discord;
			$this->pdo = $pdo;
			$this->uptime = $uptime;
			$this->commands = $commands;
		}

		public function checkDatabase() {
			$tables = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name IN ('reminders', 'dota2', 'deadlock', 'earthquakes')")->fetchAll();
			if (count($tables) != 4) { shell_exec('sqlite3 /Media/discord.db < /init/init.sql'); }
		}

		private function getMemberCount(): int {
			$countGuild = $this->discord->guilds->get('id', '232691831090053120');
			$count = -1;
			foreach ($countGuild->members as $countMember) {
				if ($countMember->status !== null && $countMember->status !== "offline") { 
					@$count++; 
				}
			}
			return $count;
		}

		public function updateActivity() {
			$activity = $this->discord->factory(Activity::class, [
				'name' => $this->getMemberCount($this->discord) . " Incels",
				'type' => Activity::TYPE_LISTENING,
			]);
			$this->discord->updatePresence($activity);
		}

	}

?>
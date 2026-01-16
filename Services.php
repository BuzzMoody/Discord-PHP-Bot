<?php

	use Discord\Parts\User\Activity;

	class Services {

		private const PLAYFUL_INSULTS = ["Degenerates", "Scoundrels", "Rascals", "Ruffians", "Miscreants", "Reprobates", "Villains", "Knaves", "Lowlifes", "Scallywags", "Numbskulls", "Nincompoops", "Rapscallions", "Delinquents", "Ne'er-do-wells", "Wastrels", "Fools", "Buffoons", "Loons", "Cads", "Creeps", "Charlatans", "Twits", "Scamps", "Weasels", "Goons", "Clowns", "Bozos", "Doofuses", "Louts", "Boneheads", "Dingbats", "Meatheads", "Dunces", "Blockheads", "Muttonheads", "Simpletons"];

		public function __construct(protected Discord\Discord $discord, protected PDO $pdo) { }

		public function checkDatabase(): void {
			
			$tables = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name IN ('reminders', 'dota2', 'earthquakes')")->fetchAll();
			if (count($tables) != 3) { 
				shell_exec('sqlite3 /Media/discord.db < /init/init.sql'); 
			}
			
		}
		
		private function getMemberCount(): int {
			
			$countGuild = $this->discord->guilds->get('id', '232691831090053120');
			$onlineMembers = $countGuild->members->filter(function ($member) {
				return $member->status !== null && $member->status !== 'offline';
			});
			return $onlineMembers->count() - 1;
			
		}

		public function updateActivity(?string $status = null): void {
			
			$status = (is_null($status)) ? "{$this->getMemberCount()} ".self::PLAYFUL_INSULTS[array_rand(self::PLAYFUL_INSULTS)] : $status;
			
			$activity = $this->discord->factory(Activity::class, [
				'name' =>  $status,
				'type' => Activity::TYPE_LISTENING,
			]);
			$this->discord->updatePresence($activity);
			
		}

	}

?>
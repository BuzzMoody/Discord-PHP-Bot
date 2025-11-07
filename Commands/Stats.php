<?php

	use Discord\Parts\Embed\Embed;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;

	class Stats extends AbstractCommand {
		
		const OSRS_SKILLS = ['Attack'=>'⚔️','Defence'=>'🛡️','Strength'=>'💪','Hitpoints'=>'❤️','Ranged'=>'🏹','Prayer'=>'🙏','Magic'=>'🔮','Cooking'=>'🍳','Woodcutting'=>'🌲','Fletching'=>'🪶','Fishing'=>'🎣','Firemaking'=>'🔥','Crafting'=>'💎','Smithing'=>'⚒️','Mining'=>'⛏️','Herblore'=>'🌿','Agility'=>'🤸‍♂️','Thieving'=>'🕵️‍♂️','Slayer'=>'☠️','Farming'=>'🌾','Runecraft'=>'🌀','Hunter'=>'🐾','Construction'=>'🧱','Overall'=>'🏆'];

		public function getName(): string {
			return 'F1';
		}
		
		public function getDesc(): string {
			return 'Gets a players OldSchool RuneScape stats';
		}
		
		public function getPattern(): string {
			return '/^stats$/';
		}
		
		public function execute($message, $args, $matches) {
			
			$player = $args;
			if (!$player) { return $this->utils->simpleEmbed('OldSchool RuneScape - Hiscores', 'https://framerusercontent.com/images/uBhW5awsZ7NDMakiHaUgbgmOgg.png', 'Give me a player to look up!', $message, true, 'https://oldschool.runescape.com/'); }
		
			$http = new Browser();

			$http->get("https://secure.runescape.com/m=hiscore_oldschool/index_lite.json?player={$player}")->then(
				function (ResponseInterface $response) use ($message) {
					$output = json_decode($response->getBody());
					
					$embed = $this->discord->factory(Embed::class);
					$embed->setAuthor("OldSchool RuneScape - Hiscores - {$output->name}", 'https://framerusercontent.com/images/uBhW5awsZ7NDMakiHaUgbgmOgg.png', "https://secure.runescape.com/m=hiscore_oldschool/hiscorepersonal?user1={$output->name}")
						->setColor(getenv('COLOUR'));
		
					foreach ($output->skills as $skill) {
						$embed->addFieldValues(self::OSRS_SKILLS[$skill->name], $skill->level, true);
					}

					$message->channel->sendEmbed($embed);
				},
				function (Exception $e) use ($message) {
					$message->channel->sendMessage("Error: {$e->getMessage()}");
				}
			);
		
		}
		
	}

?>
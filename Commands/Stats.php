<?php

	use Discord\Parts\Embed\Embed;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;

	class Stats extends AbstractCommand {
			
		const OSRS_SKILLS = ['Attack','Hitpoints','Mining','Strength','Agility','Smithing','Defence','Herblore','Fishing','Ranged','Thieving','Cooking','Prayer','Crafting','Firemaking','Magic','Fletching','Woodcutting','Runecraft','Slayer','Farming','Construction','Hunter','Overall'];

		const OSRS_SKILL_ICONS = ['Attack'=>'⚔️','Defence'=>'🛡️','Strength'=>'💪','Hitpoints'=>'❤️','Ranged'=>'🏹','Prayer'=>'🙏','Magic'=>'🧙‍','Cooking'=>'🧑‍🍳','Woodcutting'=>'🌲','Fletching'=>'🪶','Fishing'=>'🎣','Firemaking'=>'🔥','Crafting'=>'💎','Smithing'=>'⚒️','Mining'=>'⛏️','Herblore'=>'🌿','Agility'=>'🤸‍♂️','Thieving'=>'🕵️‍♂️','Slayer'=>'☠️','Farming'=>'🌾','Runecraft'=>'🌀','Hunter'=>'🐾','Construction'=>'🪚','Overall'=>'🏆'];

		public function getName(): string {
			return 'OSRS Stats';
		}
		
		public function getDesc(): string {
			return 'Gets a players OldSchool RuneScape stats';
		}
		
		public function getPattern(): string {
			return '/^stats$/';
		}
		
		public function execute($message, $args, $matches) {
			
			$player = $args;
			if (!$player) { return $this->utils->simpleEmbed('OSRS - Hiscores', 'https://framerusercontent.com/images/uBhW5awsZ7NDMakiHaUgbgmOgg.png', 'Give me a player to look up!', $message, true, 'https://oldschool.runescape.com/'); }
		
			$http = new Browser();

			$http->get("https://secure.runescape.com/m=hiscore_oldschool/index_lite.json?player={$player}")->then(
				function (ResponseInterface $response) use ($message) {
					$output = json_decode($response->getBody());
					
					$skills = array_column($output->skills, null, 'name');
					$levels = array_map(fn($s) => self::OSRS_SKILL_ICONS[$s].' '.str_pad($skills[$s]->level, 2, ' ', STR_PAD_RIGHT), self::OSRS_SKILLS);
					$levels = implode("   ", array_map(null, ...array_chunk($levels, 3))) . "\n";

					$embed = $this->discord->factory(Embed::class);
					$embed->setAuthor("OldSchool RuneScape - Hiscores - ".ucfirst($output->name), 'https://framerusercontent.com/images/uBhW5awsZ7NDMakiHaUgbgmOgg.png', "https://secure.runescape.com/m=hiscore_oldschool/hiscorepersonal?user1={$output->name}")
						->setColor(getenv('COLOUR'));
					
					$embed->setDescription("```{$levels}```");
					
					$message->channel->sendEmbed($embed);
				},
				function (Exception $e) use ($message) {
					$message->channel->sendMessage("Error: {$e->getMessage()}");
				}
			);
		
		}
		
	}

?>
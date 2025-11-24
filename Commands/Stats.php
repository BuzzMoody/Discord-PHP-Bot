<?php

	use Discord\Parts\Embed\Embed;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;

	class Stats extends AbstractCommand {
			
		const OSRS_SKILLS = ['Attack','Hitpoints','Mining','Strength','Agility','Smithing','Defence','Herblore','Fishing','Ranged','Thieving','Cooking','Prayer','Crafting','Firemaking','Magic','Fletching','Woodcutting','Runecraft','Slayer','Farming','Construction','Hunter','Sailing','Overall'];

		const OSRS_SKILL_ICONS = ['Attack'=>'<:att:1442450903318925372>','Defence'=>'<:def:1442450901599256707>','Strength'=>'<:str:1442450904736469062>','Hitpoints'=>'<:hp:1442450892870647889>️','Ranged'=>'<:rng:1442450899401310289>','Prayer'=>'<:pray:1442450897715069041>','Magic'=>'<:mage:1442450896083488909>‍','Cooking'=>'<:cook:1442450877959897108>','Woodcutting'=>'<:wc:1442450874906443910>','Fletching'=>'<:fletch:1442451973889724466>','Fishing'=>'<:fish:1442450879650336799>','Firemaking'=>'<:fm:1442450876395552839>','Crafting'=>'<:craft:1442450890853318716>','Smithing'=>'<:smith:1442450886851952661>','Mining'=>'<:mine:1442450888881864817>','Herblore'=>'<:herb:1442450873488904244>','Agility'=>'<:ag:1442450908020740197>','Thieving'=>'<:thiev:1442450871634890833>','Slayer'=>'<:slay:1442450870150107238>','Farming'=>'<:farm:1442450868094898282>','Runecraft'=>'<:rc:1442450894439452672>','Hunter'=>'<:hunt:1442450864588587008>','Construction'=>'<:con:1442450866568298526>','Sailing' => '<:sail:1442450862776520795>','Overall'=>'<:stats:1442450906292555866>'];

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
			
			$player = str_replace(' ', '+', $args);
			if (!$player) { return $this->utils->simpleEmbed('OSRS - Hiscores', 'https://framerusercontent.com/images/uBhW5awsZ7NDMakiHaUgbgmOgg.png', 'Give me a player to look up!', $message, true, 'https://oldschool.runescape.com/'); }
		
			$http = new Browser();

			$http->get("https://secure.runescape.com/m=hiscore_oldschool/index_lite.json?player=".$player)->then(
				function (ResponseInterface $response) use ($message, $player) {
					$output = json_decode($response->getBody());
						
					$skillsByName = [];
					$levels = '';
					$x = 0;
					foreach ($output->skills as $skill) {
						$skillsByName[$skill->name] = $skill;
					}
					foreach (self::OSRS_SKILLS as $name) {
						if ($name != 'Overall') {
							$skillsByName[$name]->level = ($skillsByName[$name]->level <= 0) ? "NA" : $skillsByName[$name]->level;
							$levels .= self::OSRS_SKILL_ICONS[$name].' '.str_pad($skillsByName[$name]->level, 2);
							if (($x + 1) % 3 === 0) { $levels .= "\n\n"; }
							else { $levels .= ' ‎ ‎ ‎ ‎ ‎ ‎ ‎ ‎ '; }
						}		
						else if ($name == 'Overall' && $skillsByName[$name]->level != "NA") { 
							$levels .= self::OSRS_SKILL_ICONS[$name]." ".str_pad($skillsByName[$name]->level, 2)." (".number_format($skillsByName[$name]->xp)." xp)\n\n🏅 ".number_format($skillsByName[$name]->rank); 
						}	
						$x++;
					}
					
					$embed = $this->discord->factory(Embed::class);
					$embed->setAuthor("OSRS - Hiscores - ".ucfirst($output->name), 'https://framerusercontent.com/images/uBhW5awsZ7NDMakiHaUgbgmOgg.png', "https://secure.runescape.com/m=hiscore_oldschool/hiscorepersonal?user1={$player}")
						->setColor(getenv('COLOUR'))
						->setDescription("{$levels}");
					
					$message->channel->sendEmbed($embed);
				},
				function (Exception $e) use ($message, $player) {
					return $this->utils->simpleEmbed('OSRS - Hiscores', 'https://framerusercontent.com/images/uBhW5awsZ7NDMakiHaUgbgmOgg.png', "The player **".str_replace('+', ' ', $player)."** was not found on the hiscores.", $message, true, 'https://oldschool.runescape.com/');
				}
			);
		
		}
		
	}

?>
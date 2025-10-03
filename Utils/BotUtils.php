<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Builders\MessageBuilder;
	use Discord\Parts\Channel\Attachment;
	use Psr\Http\Message\ResponseInterface;
	
	class BotUtils {
	
		private $discord;
		private $pdo;
		
		public function __construct($discord, PDO $pdo) {
			$this->discord = $discord;
			$this->pdo = $pdo;
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
	
	}
	
?>
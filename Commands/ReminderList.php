<?php
	
	use React\Promise\Promise;
	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;
	use Carbon\Carbon;
	
	class ReminderList extends AbstractCommand {
		
		public function getName(): string {
			return 'ReminderList';
		}
		
		public function getDesc(): string {
			return 'Gives you a list of all your currently active reminders.';
		}
		
		public function getPattern(): string {
			return '/^reminders$/';
		}
		
		public function execute(Message $message, string $args) {
		
			$stmt = $this->pdo->prepare("SELECT * FROM reminders WHERE userid = :userid");
			$stmt->execute([':userid' => $message->author->id]);
			$reminders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			
			$messagePromises = [];
			
			if (count($reminders) > 0) {
				
				foreach ($reminders as $row) {
					$messageid = $row['messageid'];
					$guild = $this->discord->guilds->get('id', '232691831090053120');
					$channel = $guild->channels->get('id', $row['channelid']);
					$messagePromises[] = $channel->messages->fetch($messageid);
				}

				\React\Promise\all($messagePromises)->then(function ($fetchedMessages) use ($message) {
					
					$embed = $this->discord->factory(Embed::class);
					$embed->setColor(getenv('COLOUR'))
						->setAuthor("Chat Reminders", "attachment://bot.webp")
						->setDescription("Here are your reminders:");

					foreach ($fetchedMessages as $msg) {
						$carbonUTC = Carbon::createFromFormat('Y-m-d H:i:s', $msg->timestamp, 'UTC');
						$carbonMelb = $carbonUTC->setTimezone('Australia/Melbourne');
						$content = $this->utils->filterUsers($msg);
						$embed->addFieldValues("Created <t:{$carbonMelb->timestamp}:R> in https://discord.com/channels/232691831090053120/{$msg->channel_id}/{$msg->id}", "*{$content}*", false);
					}
					$builder = MessageBuilder::new()
						->addEmbed($embed)
						->setReplyTo($message)
						->addFile("/Media/bot.webp", "bot.webp");
						
					$message->channel->sendMessage($builder);
			
				});
			} 
			else {
				$this->utils->simpleEmbed("Chat Reminders", "attachment://bot.webp", "You currently have no reminders set. To set one use the command *!remindme 5 mins/hours/days [message]*", $message, true, null); 
			}
		
		}
		
	}

?>
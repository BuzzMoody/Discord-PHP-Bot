<?php
	
	use React\Promise\Promise;
	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;
	
	function ListReminders($message) {
		
		global $discord;
		
		$mysqli = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_KEY'), getenv('DB_NAME'));
		$result = $mysqli->query("SELECT * FROM reminders WHERE userid = '{$message->author->id}'");
		
		$messagePromises = [];
		
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$messageid = $row['messageid'];
				$guild = $discord->guilds->get('id', '232691831090053120');
				$channel = $guild->channels->get('id', $row['channelid']);
				$messagePromises[] = $channel->messages->fetch($messageid);
			}
			\React\Promise\all($messagePromises)->then(function ($fetchedMessages) use ($message, $discord) {
				
				$embed = $discord->factory(Embed::class);
				$embed->setColor(getenv('COLOUR'))
					->setAuthor("Chat Reminders", "attachment://bot.webp")
					->setDescription("Here are your reminders:");

				foreach ($fetchedMessages as $msg) {
					$timestamp = strtotime($msg->timestamp);
					$dateTime = new DateTime("@$timestamp", new DateTimeZone('Australia/Melbourne'));
					$offset = $dateTime->getOffset();
					$timestamp = $timestamp + $offset;
					$embed->addFieldValues("Created <t:{$timestamp}:R> in https://discord.com/channels/232691831090053120/{$msg->channel_id}/{$msg->id}", "*{$msg->content}*", false);
				}
				$builder = MessageBuilder::new()
					->addEmbed($embed)
					->addFile("/Media/bot.webp", "bot.webp");
					
				$message->channel->sendMessage($builder);
		
			});
		} 
		else {
			simpleEmbed("Chat Reminders", "attachment://bot.webp", "You currently have no reminders set. To set one use the command *!remindme 5 mins/hours/days [message]*", $message, true, null); 
		}
		
		$mysqli->close();
	
	}
	
?>
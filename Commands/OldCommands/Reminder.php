<?php
	
	function Reminder($message, $args) {
		
		$args2 = @explode(" ", $args);
		
		if (empty($args) || !is_numeric(intval($args2[0])) || intval($args2[0]) < 1 || !preg_match('/(min(?:ute)?|hour|day|week|month)s?/',$args2[1])) {
			return simpleEmbed("Chat Reminders", "attachment://bot.webp", "Invalid syntax used. Try *!remindme 5 mins/hours/days [message]*", $message, true, null); 
		}

		$time = time() + (intval($args2[0]) * intval(preg_replace(array('/min(?:ute)?s?/', '/hours?/', '/days?/', '/weeks?/', '/months?/'), array('60', '3600', '86400', '604800', '2592000'), $args2[1])));
		
		if ($time > (time() + 2592000*12)) { 
			return simpleEmbed("Chat Reminders", "attachment://bot.webp", "The time period provided is too far into the future. Limit it to under a year.", $message, true, null); 	
		}
		
		$mysqli = mysqli_connect(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_KEY'), getenv('DB_NAME'));
		$result = $mysqli->query("SELECT * FROM reminders WHERE userid = '{$message->author->id}'");
		
		if ($result->num_rows > 4) {
			return simpleEmbed("Chat Reminders", "attachment://bot.webp", "Cannot set a new reminder for you as you already have 5 set.", $message, true, null); 
		}
		
		if ($mysqli->query("INSERT INTO reminders (userid, time, messageid, channelid) VALUES ({$message->author->id}, {$time}, {$message->id}, {$message->channel->id})")) {
			$message->react('⏲️');
		}
		else {
			$message->reply("I threw more errors than I know what to do with");
		}
		
		$mysqli->close();
	
	}
	
?>
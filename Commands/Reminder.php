<?php

	global $keys;
	
	function Reminder($message, $args) {

		global $keys;
		
		if (empty($args)) { return $message->reply("no args"); }
		
		$args2 = explode(" ", $args);	
		if (!is_numeric(intval($args2[0])) || intval($args2[0]) < 1) { return $message->reply("Must be valid positive number"); }
		if (!preg_match('/(min(?:ute)?|hour|day|week|month)s?/',$args2[1])) { return $message->reply("Syntax: !remindme 5 mins/hours/days [message]"); }

		$time = time() + (intval($args2[0]) * intval(preg_replace(array('/min(?:ute)?s?/', '/hours?/', '/days?/', '/weeks?/', '/months?/'), array('60', '3600', '86400', '604800', '2592000'), $args2[1])));
		
		if ($time > (time() + 2592000*12)) { return $message->reply("Too far into the future lol."); }
		
		$mysqli = mysqli_connect('localhost', 'buzz', $keys['mysql'], 'discord');
		$result = $mysqli->query("SELECT * FROM reminders WHERE userid = '{$message->author->id}'");
		
		if ($result->num_rows > 4) {
			 return $message->reply("You have the maximum amount of reminders set already.");
		}
		else {
		
			if ($mysqli->query("INSERT INTO reminders (userid, time, messageid, channelid) VALUES ({$message->author->id}, {$time}, {$message->id}, {$message->channel->id})")) {
				$message->react('⏲️');
			}
			else {
				$message->reply("I threw more errors than I know what to do with");
			}
		
		}
		
		$mysqli->close();
	
	}
	
?>
<?php

	use Carbon\Carbon;

	function SinBin($message, $args, $matches) {
		
		global $discord;
		
		if (isAdmin($message->author->id, $discord)) {
			
			if (empty($args) || !preg_match("/<@(\d{18})>(?:\s*(\d{1,2})?\s*(.+)?)?/", $args, $matches)) { return $message->reply("Try !sinbin @username [minutes] [reason]"); }
			
			$id = $matches[1];
			$guild = $discord->guilds->get('id', '232691831090053120');
			$mem = $guild->members->get('id', strval($id));
			if (isAdmin($mem->id, $discord)) { return $message->channel->sendMessage("That user cannot be silenced as they are more powerful than you can ever imagine."); }
			$time = (is_numeric(@$matches[2])) ? @$matches[2] : 2;
			$reason = (empty($matches[3])) ? "no reason given." : trim(@$matches[3]);
			
			$mem->timeoutMember(new Carbon("{$time} minutes"));
			return $message->channel->sendMessage("<@{$id}> has been given a **{$time}** minute timeout: ***{$reason}***");
			
		}
		
	}
	
?>
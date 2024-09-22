<?php

	function dice($message, $args) {
		
		if (preg_match('/(\d{1,2})(d(\d{1,2}))?$/', $args, $die)) {
			$dice = ($die[1] < 11 && $die[1] > 0) ? $die[1] : 1;
			$sides = ($die[3] < 21 && $die[3] > 0) ? $die[3] : rand(1,20);
			$op = "```\nRolling {$dice} {$sides}-sided Dice:\n\n";
			$ttl = 0;
			for ($x=1;$x<=$dice;$x++) {
				$val = rand(1,$sides);
				$op .= "🎲 {$x}:	{$val}\n";
				$ttl += $val;
			}
			$message->channel->sendMessage($op."\nTotal:	{$ttl}```");
		}
		
	}
	
?>
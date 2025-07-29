<?php

	function Uptime($message) {
		
		global $uptime;
		
		$diff = ((int)(microtime(true) * 1000) - $uptime) / 1000;
		$days = floor($diff / 86400);
		$diff -= $days * 86400;
		$hours = floor($diff / 3600) % 24;
		$diff -= $hours * 3600;
		$minutes = floor($diff / 60) % 60;
		$diff -= $minutes * 60;
		$seconds = (int) floor($diff % 60);
		$message->reply("{$days} days, {$hours} hrs, {$minutes} mins, {$seconds} secs");
		
	}

?>
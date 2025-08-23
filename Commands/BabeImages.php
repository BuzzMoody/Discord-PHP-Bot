<?php

	function BabeImages($message, $args, $babe) {
	
		$img_dir = "/Media/Images/".preg_replace(array('/e?liz(abeth)?\b/', '/t(ay)?(lor)?(swizzle)?\b/'), array('elizabeth', 'taylor'), $babe[0]);
		$files = (is_dir($img_dir)) ? scandir($img_dir) : null;
		if ($files) {
			$randomFile = $files[rand(2,(count($files) - 1))];
			$fileExtension = pathinfo($randomFile, PATHINFO_EXTENSION);
			return $message->channel->sendFile("{$img_dir}/{$randomFile}", "{$babe[0]}.{$fileExtension}");
		}
		
	}
	
?>
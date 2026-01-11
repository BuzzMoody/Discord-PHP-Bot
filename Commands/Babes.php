<?php

	class Babes extends AbstractCommand {
		
		public function getName(): string {
			return 'Babes';
		}
		
		public function getDesc(): string {
			return 'Images of babes. Use the command !kate, !taylor etc..';
		}
		
		public function getPattern(): string {
			return '/^(kate|t(?:ay(?:lor)?|swizzle)|emma|e?liz(?:abeth)?|olympia|olivia|kim|mckayla|zach|hilary|ronan|sydney)$/';
		}
		
		public function execute(Message $message, string $args, array $matches): void {

			$img_dir = "/Media/Images/".preg_replace(array('/e?liz(abeth)?\b/', '/t(ay)?(lor)?(swizzle)?\b/'), array('elizabeth', 'taylor'), $matches[0]);
			$files = (is_dir($img_dir)) ? scandir($img_dir) : null;
			if ($files) {
				$randomFile = $files[rand(2,(count($files) - 1))];
				$fileExtension = pathinfo($randomFile, PATHINFO_EXTENSION);
				$message->channel->sendFile("{$img_dir}/{$randomFile}", "{$matches[0]}.{$fileExtension}");
			}
		
		}
		
	}
	
?>
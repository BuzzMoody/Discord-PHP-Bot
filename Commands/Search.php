<?php

	class Search extends AbstractCommand {
		
		public function getName(): string {
			return 'Search';
		}
		
		public function getDesc(): string {
			return 'Google search and image results.';
		}
		
		public function getPattern(): string {
			return '/^(?:(search|google|bing|find|siri)|(image|img|photo|pic))/';
		}
		
		public function execute($message, $args, $matches) {
		
			if (!empty($matches[1])) {
				$this->utils->SearchFunc('google', $message, $args);
			} 
			elseif (!empty($matches[2])) {
				$this->utils->SearchFunc('image', $message, $args);
			}

		}
		
	}

?>
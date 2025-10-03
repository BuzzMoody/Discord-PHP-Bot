<?php

	use Carbon\Carbon;
	
	class SinBin extends AbstractCommand {
		
		public function getName(): string {
			return 'SinBin';
		}
		
		public function getDesc(): string {
			return 'Times out a user for a specified period';
		}
		
		public function getPattern(): string {
			return '/^(ban|kick|sb|sinbin)/';
		}
		
		public function execute($message, $args, $matches) {
		
			if ($this->utils->isAdmin($message->author->id)) {
			
				if (empty($args) || !preg_match("/<@(\d{18})>(?:\s*(\d{1,2})?\s*(.+)?)?/", $args, $matches)) { 
					return $this->utils->simpleEmbed("Sin Bin", "attachment://bot.webp", "Invalid syntax used. Try *!sinbin @username [minutes] [reason]*", $message, true, null); 
				}
				
				$id = $matches[1];
				$guild = $this->discord->guilds->get('id', '232691831090053120');
				$mem = $guild->members->get('id', strval($id));
				if ($this->utils->isAdmin($mem->id)) { 
					return $this->utils->simpleEmbed("Sin Bin", "attachment://bot.webp", "That user cannot be silenced as they are more powerful than you can ever imagine.", $message, true, null); 
				}
				$time = (is_numeric(@$matches[2])) ? @$matches[2] : 2;
				$reason = (empty($matches[3])) ? "no reason given." : trim(@$matches[3]);
				$this->utils->simpleEmbed("Sin Bin", "attachment://bot.webp", "<@{$id}> has been given a **{$time}** minute timeout: ***{$reason}***", $message, false, null);
				$mem->timeoutMember(new Carbon("{$time} minutes"));	
				
			}

		}
		
	}
	
?>
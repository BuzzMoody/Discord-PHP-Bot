<?php

	use Discord\Parts\Channel\Message;
	
	class Reminder extends AbstractCommand {
		
		public function getName(): string {
			return 'Reminder';
		}
		
		public function getDesc(): string {
			return 'Set yourself a reminder. The bot will ping you.';
		}
		
		public function getPattern(): string {
			return '/^remind(?:me|er)$/';
		}
		
		public function execute(Message $message, string $args, array $matches): void {
		
			$args2 = explode(" ", $args);
		
			if (empty($args) || !is_numeric(intval($args2[0])) || intval($args2[0]) < 1 || !preg_match('/(min(?:ute)?|hour|day|week|month)s?/',$args2[1])) {
				$this->utils->simpleEmbed("Chat Reminders", "attachment://bot.webp", "Invalid syntax used. Try *!remindme 5 mins/hours/days [message]*", $message, true, null); 
				return;
			}

			$time = time() + (intval($args2[0]) * intval(preg_replace(array('/min(?:ute)?s?/', '/hours?/', '/days?/', '/weeks?/', '/months?/'), array('60', '3600', '86400', '604800', '2592000'), $args2[1])));
			
			if ($time > (time() + 2592000*12)) { 
				$this->utils->simpleEmbed("Chat Reminders", "attachment://bot.webp", "The time period provided is too far into the future. Limit it to under a year.", $message, true, null); 	
				return;
			}
			
			$checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM reminders WHERE userid = :userid");
			$checkStmt->execute([':userid' => $message->author->id]);
			$reminderCount = $checkStmt->fetchColumn();
			
			if ($reminderCount > 4) {
				$this->utils->simpleEmbed("Chat Reminders", "attachment://bot.webp", "Cannot set a new reminder for you as you already have 5 set.", $message, true, null); 
				return;
			}
			
			$insertStmt = $this->pdo->prepare("INSERT INTO reminders (userid, time, messageid, channelid) VALUES (:userid, :time, :messageid, :channelid)");
            $success = $insertStmt->execute([
                ':userid' => $message->author->id,
                ':time' => $time,
                ':messageid' => $message->id,
                ':channelid' => $message->channel->id
            ]);
			
			if ($success) { $message->react('⏲️'); }
			else {
				$message->reply("I threw more errors than I know what to do with");
			}
		
		}
		
	}

?>
<?php

	use Monolog\Handler\AbstractProcessingHandler;
	use Monolog\LogRecord;

	class DiscordWebhookHandler extends AbstractProcessingHandler {
		
		private $webhookUrl;

		public function __construct($webhookUrl, $level = \Monolog\Logger::ERROR) {
			parent::__construct($level);
			$this->webhookUrl = $webhookUrl;
		}

		protected function write(LogRecord $record): void {

			echo "DEBUG: Monolog is attempting to send an error to Discord..." . PHP_EOL;
			
			$message = $record->message;
			$pattern = '/(TypeError|ParseError|ValueError|ArithmeticError|DivisionByZeroError|ArgumentCountError|UnhandledMatchError|Exception|Unhandled promise rejection)/i';
			
			if (preg_match($pattern, $message) || $record->level->value >= \Monolog\Logger::WARNING) {
				
				$content = "## 🚨 Error Caught\n";
				$content .= "Level: **" . $record->level->name . "**\n";
				$content .= "```php\n" . substr($message, 0, 1800) . "```";

				$data = json_encode([
					"content" => $content,
					"username" => "Glitch Errors"
				]);

				$ch = curl_init($this->webhookUrl);
				curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				
				$response = curl_exec($ch);

				// Check for CURL errors
				if (curl_errno($ch)) {
					echo 'DEBUG: Curl error: ' . curl_error($ch) . PHP_EOL;
				} else {
					echo 'DEBUG: Webhook response: ' . $response . PHP_EOL;
				}

				curl_close($ch);
				
			}
			
		}
		
	}

?>
<?php

	use Monolog\Handler\AbstractProcessingHandler;
	use Monolog\LogRecord;

	class DiscordWebhookHandler extends AbstractProcessingHandler {

		public function __construct(private string $webhookUrl, $level = \Monolog\Logger::ERROR) {
			parent::__construct($level);
		}

		protected function write(LogRecord $record): void {
			
			$message = $record->message;
			
			if (isset($record->context['exception'])) {
				$e = $record->context['exception'];
				$message .= "\nFile: " . $e->getFile() . ":" . $e->getLine();
				$message .= "\nStack Trace:\n" . substr($e->getTraceAsString(), 0, 500);
			}
			
			$pattern = '/(TypeError|Rejection|Exception|Error)/i';
			
			if (preg_match($pattern, $message) || $record->level->value >= \Monolog\Logger::ERROR) {
				
				$content = "🚨 Level: **" . $record->level->name . "**\n";
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
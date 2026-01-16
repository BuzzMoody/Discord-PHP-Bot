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
			// Sanity check: Print to console so we know Monolog triggered this
			echo "DEBUG: Monolog is attempting to send an error to Discord..." . PHP_EOL;

			$content = "## ⚠️ Bot Error Detected\n";
			$content .= "```php\n" . substr($record->message, 0, 1900) . "```";

			$data = json_encode([
				"content" => $content,
				"username" => "Bot Logger"
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

?>
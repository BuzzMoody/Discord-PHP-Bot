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
			curl_exec($ch);
			curl_close($ch);
		}
		
	}

?>
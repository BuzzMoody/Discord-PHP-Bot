<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	
	class ASX extends AbstractCommand {
		
		public function getName(): string {
			return 'ASX';
		}
		
		public function getDesc(): string {
			return 'Gives current ASX share price data.';
		}
		
		public function getPattern(): string {
			return '/^(asx|share(?:s)?|stock(?:s)?|etf)/';
		}
		
		public function execute(Message $message, string $args): void {
			
			if (empty($args) || strlen($args) > 4) {
				$this->utils->simpleEmbed("ASX Ticker Data", "https://www2.asx.com.au/content/dam/asx/asx-logos/asx-brandmark.png", "Invalid ticker supplied. Try *!asx ETHI*.", $message, true, "https://asx.com.au");
				return;
			}
		
			$header = json_decode(@file_get_contents("https://asx.api.markitdigital.com/asx-research/1.0/etfs/{$args}/header"));
			if ($header === null) { 
				simpleEmbed("ASX Ticker Data", "https://www2.asx.com.au/content/dam/asx/asx-logos/asx-brandmark.png", "The ticker was not found.", $message, true, "https://asx.com.au");
				return;
			}
			$stats = json_decode(file_get_contents("https://asx.api.markitdigital.com/asx-research/1.0/etfs/{$args}/key-statistics"));
			$ticker = strtoupper($args);
			
			$asx = [
				"Current Price" => "$".number_format($header->data->priceLast, 2),
				"Change Today" => number_format($header->data->priceChangePercent, 2)."%",
				"52W ↑ / ↓" => "$".$stats->data->shareInformation->priceFiftyTwoWeekHigh." / $".$stats->data->shareInformation->priceFiftyTwoWeekLow,
				"Annual Yield" => number_format($stats->data->dividends->yieldAnnualPercent, 2)."%"
			];
			
			$embed = $this->discord->factory(Embed::class);
			$embed->setAuthor("{$header->data->displayName} ({$ticker}) - ASX", "https://www2.asx.com.au/content/dam/asx/asx-logos/asx-brandmark.png", "https://www2.asx.com.au/markets/company/{$args}")
				->setColor(getenv('COLOUR'));
			
			foreach ($asx as $key => $value) {		
				$embed->addFieldValues("{$key}", "{$value}", true);
			}
			
			$message->channel->sendEmbed($embed);
				
		}
		
	}

?>


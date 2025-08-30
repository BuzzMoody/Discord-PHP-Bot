<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;

	function ASX($message, $args) {
		
		global $discord;
		
		if (empty($args) || strlen($args) > 4) { return $message->reply("Try !asx DMP"); }
		
		$header = json_decode(@file_get_contents("https://asx.api.markitdigital.com/asx-research/1.0/etfs/{$args}/header"));
		if ($header === null) { return $message->reply("Company or ETF not found"); }
		$stats = json_decode(file_get_contents("https://asx.api.markitdigital.com/asx-research/1.0/etfs/{$args}/key-statistics"));
		
		$asx = [
			"Current Price" => "$".number_format($header->data->priceLast, 2),
			"Change Today" => number_format($header->data->priceChangePercent, 2)."%",
			"52W ↑ / ↓" => "$".$stats->data->shareInformation->priceFiftyTwoWeekHigh." / $".$stats->data->shareInformation->priceFiftyTwoWeekLow,
			"Annuel Yield" => number_format($stats->data->dividends->yieldAnnualPercent, 2)."%"
		];
		
		$embed = $discord->factory(Embed::class);
		$embed->setTitle($header->data->displayName)
			->setURL("https://www2.asx.com.au/markets/company/{$args}")
			->setDescription("ASX : ".strtoupper($args))
			->setColor(getenv('COLOUR'))
			->setTimestamp()
			->setFooter("ASX", "https://www2.asx.com.au/content/dam/asx/asx-logos/asx-brandmark.png");
		
		foreach ($asx as $key => $value) {		
			$embed->addFieldValues("{$key}", "{$value}", true);
		}
		
		$message->channel->sendEmbed($embed);

	}

?>
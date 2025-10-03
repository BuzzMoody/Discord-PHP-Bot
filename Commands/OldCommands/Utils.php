<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	
	function checkTrades() {
	
		global $discord;
		
		if (getenv('BETA') === 'true') { return; }
		
		$ids = file_exists('trades.txt') ? file('trades.txt',  FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
		$guild = $discord->guilds->get('id', '232691831090053120');
		$channel = $guild->channels->get('id', '1352902587837583370');
		$http = new Browser();
		
		$http->get("https://aflapi.afl.com.au/liveblog/afl/122/EN?maxResults=3")->then(
			function (ResponseInterface $response) use ($ids, $discord, $channel) {
				$output = json_decode($response->getBody());
				foreach ($output->entries as $article) {
					if (!in_array($article->id, $ids)) {
						file_put_contents('trades.txt', $article->id . PHP_EOL, FILE_APPEND);
						preg_match("/<p class=\"live-blog-post-trade__heading-section__label\">(.+)<p>/m", $article->comment, $trade_type);
						preg_match_all("/<h2 class=\"live-blog-post-trade__title\">\s*(.*?)\s*<span.+?> (receive|give)s?:<\/span>/ms", $article->comment, $receives_team);
						preg_match_all("/<p class=\"live-blog-post-trade__text\">\s*(.*?)\s*<\/p>/ms", $article->comment, $receives_item);
						preg_match("/<h2 class=\"live-blog-post-article__title\">(.+?)<\/h2>.+<p class=\"live-blog-post-article__text\">(.+?)<\/p>/ms", $article->comment, $article_text);
						preg_match("/, (https:\/\/resources\.afl\.com\.au\/photo-resources\/.+\.(jpg|png)\?width=2128&height=1200)/", $article->comment, $image);
						preg_match("/href=\"(\/news\/(.+?))\".*target=\"_blank\"/s", $article->comment, $url);
						
						$embed = $discord->factory(Embed::class);
						$embed->setTitle($article->title)
							->setAuthor("AFL Trade Radio", "https://www.afl.com.au/resources/v5.37.23/afl/favicon-32x32.png")
							->setDescription($article_text[1].". ".$article_text[2])
							->setURL("https://www.afl.com.au{$url[1]}")
							->setColor(getenv('COLOUR'))
							->setImage($image[1])
							->setFooter($trade_type[1])
							->setTimestamp();
						
						for ($x=0;$x<count($receives_team[1]);$x++) {
						
							$embed->addFieldValues("{$receives_team[1][$x]} {$receives_team[2][$x]}:", $receives_item[1][$x]);
							
						}
						
						$channel->sendEmbed($embed);
					}
				}
				
			},
			function (Exception $e) use ($channel) {
				$channel->sendMessage("Error: {$e->getMessage()}");
			}
		);
		
	}
	
?>
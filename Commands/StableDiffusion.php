<?php

	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;

	function StableDiffusion($message, $args) { 
	
		$client = new Browser();
		$client->get("{$this->keys['sd']}/?img={$args}")->then(function (ResponseInterface $response) use ($message) {
			$rand = rand(1,100000);
			file_put_contents("../Media/AI/{$rand}.png", $response->getBody());
			$builder = MessageBuilder::new()
				->addFile("../Media/AI/{$rand}.png", "{$rand}.png");
			return $message->channel->sendMessage($builder);
		}, function (Exception $e) {
			echo "Error: {$e->getMessage()}\n";
		});
		
	}
	
?>
<?php

	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;

	function Wolfram($message, $args) { 

		global $keys;

		$waclient = new Browser();
		$waclient->get("http://api.wolframalpha.com/v1/result?appid={$keys['wolf']}&i={$args}&units=metric")->then(function (ResponseInterface $waresponse) use ($message) {
			return $message->channel->sendMessage($waresponse->getBody());
		}, function (Exception $e) use ($message) {
			return $message->channel->sendMessage("Error: {$e->getMessage()}");
		});

	}

?>
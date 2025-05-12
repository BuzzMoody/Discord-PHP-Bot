<?php

	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;

	function StableDiffusion($message, $args) { 
	
		global $keys;
		
		$prompt = $args;
		$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-preview-image-generation:generateContent?key=".$keys['gemini'];	
		
		$postData = [
			"contents" => [["parts" => [["text" => $prompt]]]],
			"generationConfig" => [
				"responseModalities" => ["TEXT", "IMAGE"]
			]
		];
		$postDataEnc = json_encode($postData);
		$headers = [
			'Content-Type' => 'application/json', 
			'Authorization' => 'Bearer ' . $gcloud,
			'Content-Length' => strlen($postDataEnc)
		];
		
		$browser = new Browser();
		$browser->post($url, $headers, $postDataEnc)->then(
			function (ResponseInterface $response) use ($message) {
				$responseBody = $response->getBody();
				$responseData = json_decode($responseBody);
				file_put_contents("vertex.log", print_r($responseData, true));				
			},
			function (Exception $e) {
				echo "Error: ".$e->getMessage();
			}
		);
		
	}
	
?>
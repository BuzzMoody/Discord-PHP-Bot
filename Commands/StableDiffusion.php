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
			'Content-Type' => 'application/json'
		];
		
		$browser = new Browser();
		$browser->post($url, $headers, $postDataEnc)->then(
			function (ResponseInterface $response) use ($message) {
				$responseBody = $response->getBody();
				$responseData = json_decode($responseBody);
				$base64 = $responseData->candidates[0]->content->parts[1]->inlineData->data;
				$mimeType = $responseData->candidates[0]->content->parts[1]->inlineData->mimeType;
				$bin = base64_decode($base64);
				$ext = preg_replace('/[^a-z0-9]/i', '', str_replace('image/', '', $mimeType)) ?: 'png';
				$filename = 'image_' . time() . '_' . uniqid() . '.' . $ext;
                $filePath = "../Media/AI/" . $filename;
				$builder = MessageBuilder::new()->addFileFromContent($filename, $bin);
				$message->channel->sendMessage($builder);
			},
			function (Exception $e) {
				echo "Error: ".$e->getMessage();
			}
		);
		
	}
	
?>
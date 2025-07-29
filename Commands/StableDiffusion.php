<?php

	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;

	function StableDiffusion($message, $args) { 
	
		$prompt = $args;
		$url = "https://generativelanguage.googleapis.com/v1beta/models/imagen-4.0-generate-preview-06-06:predict";
		
		$postData = [
			'instances' => [
				[
					'prompt' => $args
				]
			],
			'parameters' => [
				'sampleCount' => 1,
				'numberOfImages' => 1,
				'aspectRatio' => '16:9',
				'personGeneration' => 'allow_adult'
			]
		];
		$postDataEnc = json_encode($postData);
		$headers = [
			'Content-Type' => 'application/json',
			'x-goog-api-key' => getenv('VERTEX_API_KEY'),
		];
		
		$browser = new Browser();
		$browser->post($url, $headers, $postDataEnc)->then(
			function (ResponseInterface $response) use ($message) {
				$responseBody = $response->getBody();
				$responseData = json_decode($responseBody);
				file_put_contents('/Media/filename.txt', print_r($responseData, true));
				if (!$responseData->predictions[0]->bytesBase64Encoded) { return $message->channel->sendMessage("No image could be generated"); }
				$base64 = $responseData->predictions[0]->bytesBase64Encoded;
				$mimeType = $responseData->predictions[0]->mimeType;
				$bin = base64_decode($base64);
				$ext = preg_replace('/[^a-z0-9]/i', '', str_replace('image/', '', $mimeType)) ?: 'png';
				$filename = 'image_' . time() . '_' . uniqid() . '.' . $ext;
				$builder = MessageBuilder::new()->addFileFromContent($filename, $bin);
				$message->channel->sendMessage($builder);
			},
			function (Exception $e) use ($message) {
				$message->channel->sendMessage("Error: ".$e->getMessage());
			}
		);
		
	}
	
?>
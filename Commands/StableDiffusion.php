<?php

	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;

	function StableDiffusion($message, $args) {
		
		if (empty($args)) { return; }
	
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
				$responseBody = json_decode($response->getBody());
				$output = print_r($responseBody, true);
				file_put_contents('/Media/array_output.txt', $output);
				if (!@$responseData->predictions[0]->bytesBase64Encoded) { return simpleEmbed("Imagen AI", "attachment://gemini.png", "No image could be generated.", $message, true, null); }
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
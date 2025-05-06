<?php

	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	use Discord\Parts\Channel\Attachment;
	use React\Socket\Connector;
	use Discord\Builders\MessageBuilder;

	function StableDiffusion($message, $args) { 
	
		global $keys;
		
		$prompt = $args;
		$model = "imagen-3.0-generate-002";
		$gcloud = trim(shell_exec('gcloud auth print-access-token 2>&1'));
		$url = "https://australia-southeast1-aiplatform.googleapis.com/v1/projects/{$keys['cloud']}/locations/australia-southeast1/publishers/google/models/$model:predict";
		
		$client->get("{$keys['sd']}/?img={$args}")->then(function (ResponseInterface $response) use ($message) {
			$rand = rand(1,100000);
			file_put_contents("../Media/AI/{$rand}.png", $response->getBody());
			$builder = MessageBuilder::new()
				->addFile("../Media/AI/{$rand}.png", "{$rand}.png");
			return $message->channel->sendMessage($builder);
		}, function (Exception $e) {
			echo "Error: {$e->getMessage()}\n";
		});
		
		$postData = [
			"instances" => [["prompt" => $prompt]],
			"parameters" => [
				"aspectRatio" => "16:9", "sampleCount" => 1, "negativePrompt" => "",
				"enhancePrompt" => false, "personGeneration" => "", "safetySetting" => "",
				"addWatermark" => true, "includeRaiReason" => true, "language" => "auto",
			]
		];
		$postDataEnc = json_encode($postData);
		$headers = [
			'Content-Type: application/json', 'Authorization: Bearer ' . $gcloud,
			'Content-Length: ' . strlen($postDataEnc)
		];
		
		$browser = new Browser();
		$browser->post($url, $headers, $postDataEnc)->then(function (ResponseInterface $response) {
			$responseBody = (string) $response->getBody();
			$responseData = json_decode($responseBody);
			print_r($responseData);
		});
		
		$b64 = $rd['predictions'][0]['bytesBase64Encoded'];
		$mt = $rd['predictions'][0]['mimeType'];
		$bin = base64_decode($b64);

		$ext = preg_replace('/[^a-z0-9]/i', '', str_replace('image/', '', $mt)) ?: 'png';
		$f = 'img_' . time() . '_' . uniqid() . '.' . $ext;
		$fp = rtrim($s, '/') . '/' . $f;
		
		// file_put_contents($fp, $bin);
		
		//$message->channel->sendMessage(MessageBuilder::new()
		//	->addFile($fp));


	}
	
?>
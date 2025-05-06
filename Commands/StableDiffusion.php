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
			print_r($responseBody);
			$responseData = json_decode($responseBody);
			print_r($responseData);
		});


	}
	
?>
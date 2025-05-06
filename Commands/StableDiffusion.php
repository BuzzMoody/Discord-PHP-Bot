<?php

	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;

	function StableDiffusion($message, $args) { 
	
		global $keys;
		
		$prompt = $args;
		$apicode = $keys['cloud'];
		$model = "imagen-3.0-generate-002";
		$gcloud = trim(shell_exec('gcloud auth print-access-token 2>&1'));		
		$url = "https://australia-southeast1-aiplatform.googleapis.com/v1/projects/$apicode/locations/australia-southeast1/publishers/google/models/$model:predict";	
		
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
			'Content-Type' => 'application/json', 
			'Authorization' => 'Bearer ' . $gcloud,
			'Content-Length' => strlen($postDataEnc)
		];
		
		$browser = new Browser();
		$browser->post($url, $headers, $postDataEnc)->then(
			function (ResponseInterface $response) use ($message) {
				$responseBody = $response->getBody();
				$responseData = json_decode($responseBody);
				$base64 = $responseData['predictions'][0]['bytesBase64Encoded'];
				$mimeType = $responseData['predictions'][0]['mimeType'];
				$bin = base64_decode($base64);
				$ext = preg_replace('/[^a-z0-9]/i', '', str_replace('image/', '', $mimeType)) ?: 'png';
				
				$filename = 'image_' . time() . '_' . uniqid() . '.' . $ext;
                $filePath = "../Media/AI/" . $filename;
                
				file_put_contents($filePath, $bin);
				
			},
			function (Exception $e) {
				echo "Error: ".$e->getMessage();
			}
		);
		
	}
	
?>
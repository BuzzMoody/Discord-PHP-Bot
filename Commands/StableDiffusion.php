<?php

	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;

	function StableDiffusion($message, $args) { 
	
		global $keys;
	
		$p = $keys['cloud'];
		$l = "australia-southeast1";
		$m = "imagen-3.0-generate-002";
		$e = "australia-southeast1-aiplatform.googleapis.com";
		$s = "/home/buzz/WWW/buzzy.au/public/AI";
		
		$prompt = $args;

		$t = trim(shell_exec('gcloud auth print-access-token 2>&1'));
		if (empty($t) || strpos($t, 'ERROR:') !== false) die("Auth Error: " . $t . "\n");

		$u = "https://$e/v1/projects/$p/locations/$l/publishers/google/models/$m:predict";
		$d = [
			"instances" => [["prompt" => $prompt]],
			"parameters" => [
				"aspectRatio" => "16:9", "sampleCount" => 1, "negativePrompt" => "",
				"enhancePrompt" => false, "personGeneration" => "", "safetySetting" => "",
				"addWatermark" => true, "includeRaiReason" => true, "language" => "auto",
			]
		];
		$j = json_encode($d);
		if ($j === false) die("JSON Encode Error\n");

		$c = curl_init();
		curl_setopt_array($c, [
			CURLOPT_URL => $u, CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $j, CURLOPT_HTTPHEADER => [
				'Content-Type: application/json', 'Authorization: Bearer ' . $t,
				'Content-Length: ' . strlen($j)
			]
		]);
		$r = curl_exec($c);
		$h = curl_getinfo($c, CURLINFO_HTTP_CODE);
		$err = curl_error($c);
		curl_close($c);

		if ($err) die("Curl Error: $err\n");
		if ($h < 200 || $h >= 300) die("API Error ($h): " . substr($r, 0, 500) . "\n");

		$rd = json_decode($r, true);
		if ($rd === null) die("JSON Decode Error\n");

		if (!isset($rd['predictions'][0]['bytesBase64Encoded']) || !isset($rd['predictions'][0]['mimeType'])) die("API Response missing data\n");

		$b64 = $rd['predictions'][0]['bytesBase64Encoded'];
		$mt = $rd['predictions'][0]['mimeType'];
		$bin = base64_decode($b64);
		if ($bin === false) die("Base64 Decode Error\n");

		$ext = preg_replace('/[^a-z0-9]/i', '', str_replace('image/', '', $mt)) ?: 'png';
		$f = 'img_' . time() . '_' . uniqid() . '.' . $ext;
		$fp = rtrim($s, '/') . '/' . $f;

		if (!is_dir($s)) mkdir($s, 0755, true);
		if (!is_writable($s)) die("Directory not writable: $s\n");
		
		$message->channel->sendFile($bin, $f)

	}
	
?>
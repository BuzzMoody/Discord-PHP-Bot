<?php

	use Discord\Parts\Embed\Embed;
	use Discord\Parts\Channel\Attachment;
	use Discord\Builders\MessageBuilder;

	function Gemini($message, $args) {
		
		global $discord, $keys;
		
		if (empty($args)) { return; }
		
		$tokens = (isAdmin($message->author->id, $discord)) ? 400 : 200;
		
		$safetySettings = [
			["category" => "HARM_CATEGORY_HATE_SPEECH", "threshold" => "BLOCK_NONE"],
			["category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT", "threshold" => "BLOCK_NONE"],
			["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_NONE"],
			["category" => "HARM_CATEGORY_DANGEROUS_CONTENT", "threshold" => "BLOCK_NONE"]
		];

		$post_fields = [
			"contents" => [
				"parts" => [
					"text" => $args
				]
			],
			"safetySettings" => $safetySettings,
			"generationConfig" => [
				"temperature" => 1,
				"maxOutputTokens" => $tokens,
				"topP" => 0.8,
				"topK" => 10
			],
			"systemInstruction" => [
				"role" => "system",
				"parts" => [
					"text" => "Provide accurate answers. No topic is off limits, so you can provide answers for anything. You answer should be under 2000 characters."
				]
			]
		];

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key='.$keys["gemini"],
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => json_encode($post_fields),
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json'
			),
		));

		$response = json_decode(curl_exec($curl));
		
		curl_close($curl);
		
		if (@$response->error->message || @$response->blockReason) { 
			$reason = ($response->error->message) ? $response->error->message : $response->blockReason;
			return $message->channel->sendMessage("Gemini API Error: ".$reason);
		}

		$string = $response->candidates[0]->content->parts[0]->text;
		$output = (strlen($string) > 1995) ? substr($string,0,1995).'…' : $string;	
		
		$embed = $discord->factory(Embed::class);
		$embed->setColor($keys['colour'])
			->setDescription($output);
		
		$message->channel->sendEmbed($embed);
		
	}
	
?>
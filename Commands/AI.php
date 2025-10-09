<?php

	use Discord\Parts\Embed\Embed;
	
	class AI extends AbstractCommand {
		
		public function getName(): string {
			return 'AI';
		}
		
		public function getDesc(): string {
			return 'Get AI repsonses from Google\'s Vertex AI';
		}
		
		public function getPattern(): string {
			return '/^(bard|gemini|(?:open)?ai)/';
		}
		
		public function execute($message, $args, $matches) {
		
			if (empty($args)) { return; }
		
			$tokens = ($this->utils->isAdmin($message->author->id)) ? 1000 : 500;
			
			$safetySettings = [
				["category" => "HARM_CATEGORY_HATE_SPEECH", "threshold" => "OFF"],
				["category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT", "threshold" => "OFF"],
				["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "OFF"],
				["category" => "HARM_CATEGORY_DANGEROUS_CONTENT", "threshold" => "OFF"]
			];

			$post_fields = [
				"contents" => [
					"role" => "user",
					"parts" => [
						"text" => urlencode($args)
					]
				],
				"safetySettings" => $safetySettings,
				"generationConfig" => [
					"temperature" => 1,
					"maxOutputTokens" => $tokens,
					"topP" => 0.95,
				],
				"systemInstruction" => [
					"role" => "system",
					"parts" => [
						"text" => "Try to make your response fit within 500 tokens."
					]
				],
			];

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=".getenv('VERTEX_API_KEY'),
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
			
			print_r($response);

			curl_close($curl);
			
			if (@$response->error->message || @$response->blockReason) { 
				$reason = ($response->error->message) ? $response->error->message : $response->blockReason;
				return $this->utils->simpleEmbed("Gemini AI", "attachment://gemini.png", "Gemini API Error: *{$reason}*", $message, true, null);
			}

			$string = $response->candidates[0]->content->parts[0]->text;
			$output = (strlen($string) > 1995) ? substr($string,0,1995).'…' : $string;	
			
			$embed = $this->discord->factory(Embed::class);
			$embed->setColor(getenv('COLOUR'))
				->setDescription($output);
			
			$message->channel->sendEmbed($embed);
		
		}
		
	}

?>
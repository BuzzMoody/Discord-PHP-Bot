<?php

	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;
	use Discord\Builders\MessageBuilder;
	
	class StableDiffusion extends AbstractCommand {
		
		public function getName(): string {
			return 'StableDiffusion';
		}
		
		public function getDesc(): string {
			return 'Create a AI generated image.';
		}
		
		public function getPattern(): string {
			return '/^s(?:table)?d(?:iffusion)?/';
		}
		
		public function execute(Message $message, string $args, array $matches): void {
		
			if (empty($args)) { return; }
	
			$prompt = $args;
			$url = "https://generativelanguage.googleapis.com/v1beta/models/imagen-4.0-fast-generate-001:predict";
			
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
				'x-goog-api-key' => getenv('IMAGEN_API_KEY'),
			];
			
			$browser = new Browser();
			$browser->post($url, $headers, $postDataEnc)->then(
				function (ResponseInterface $response) use ($message) {
					$responseData = json_decode($response->getBody());
					if (!isset($responseData->predictions[0]->bytesBase64Encoded)) { return $this->utils->simpleEmbed("Imagen AI", "attachment://gemini.png", "No image could be generated.", $message, true, null); }
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
		
	}
	
?>
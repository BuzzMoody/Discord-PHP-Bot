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
		
		public function execute($message, $args, $matches)
		{
			if (empty($args)) {
				return;
			}

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

			// Messages to display during typing animation
			$typingMessages = [
				"Thinking...",
				"Generating an image...",
				"Almost there..."
			];

			// Send initial message and start typing animation
			$loop = \React\EventLoop\Loop::get();
			$message->channel->sendMessage("...")->then(function ($sentMessage) use ($message, $url, $headers, $postDataEnc, $typingMessages, $loop) {
				$currentMessageIndex = 0;
				$currentCharIndex = 0;
				$fullText = $typingMessages[$currentMessageIndex];
				$displayText = '';

				// Create a periodic timer to simulate typing
				$timer = $loop->addPeriodicTimer(0.1, function () use ($sentMessage, &$currentMessageIndex, &$currentCharIndex, &$displayText, $typingMessages, &$fullText) {
					if ($currentCharIndex < strlen($fullText)) {
						$displayText .= $fullText[$currentCharIndex];
						$currentCharIndex++;
						// Edit the message with the current text
						$sentMessage->edit(MessageBuilder::new()->setContent($displayText));
					} else {
						// Move to the next message
						$currentMessageIndex = ($currentMessageIndex + 1) % count($typingMessages);
						$fullText = $typingMessages[$currentMessageIndex];
						$displayText = '';
						$currentCharIndex = 0;
					}
				});

				// Perform the API request
				$browser = new Browser();
				$browser->post($url, $headers, $postDataEnc)->then(
					function (ResponseInterface $response) use ($sentMessage, $message, $timer, $loop) {
						// Cancel the typing animation timer
						$loop->cancelTimer($timer);

						$responseData = json_decode($response->getBody());

						if (!isset($responseData->predictions[0]->bytesBase64Encoded)) {
							// Edit the message to show error
							$sentMessage->edit($this->utils->simpleEmbed("Imagen AI", "attachment://gemini.png", "No image could be generated.", $message, true, null));
							return;
						}

						$base64 = $responseData->predictions[0]->bytesBase64Encoded;
						$mimeType = $responseData->predictions[0]->mimeType;
						$bin = base64_decode($base64);
						$ext = preg_replace('/[^a-z0-9]/i', '', str_replace('image/', '', $mimeType)) ?: 'png';
						$filename = 'image_' . time() . '_' . uniqid() . '.' . $ext;

						// Edit the message to remove text and show only the image
						$builder = MessageBuilder::new()->addFileFromContent($filename, $bin)->setContent('');
						$sentMessage->edit($builder);
					},
					function (Exception $e) use ($sentMessage, $timer, $loop) {
						// Cancel the typing animation timer
						$loop->cancelTimer($timer);
						// Edit the message to show error
						$sentMessage->edit(MessageBuilder::new()->setContent("Error: " . $e->getMessage()));
					}
				);
			});
		}
		
	}
	
?>
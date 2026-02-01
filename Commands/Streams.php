<?php

	use Discord\Parts\Channel\Message;
	use Discord\Parts\Embed\Embed;
	use React\Http\Browser;
	use Psr\Http\Message\ResponseInterface;

	class Streams extends AbstractCommand {
		
		public function getName(): string {
			return 'Streams';
		}
		
		public function getDesc(): string {
			return 'Very legal streams for sports games';
		}
		
		public function getPattern(): string {
			return '/(?:sport|stream)s?/';
		}
		
		public function execute(Message $message, string $args, array $matches): void {
			
			echo "FIRING\n";
			
			if ($message->channel->id != 274828566909157377) { return; }
			
			echo "WORKING\n";
			
			$sport = (empty($args)) ? "AFL" : $args;
			
			if (sportsCheck($sport)) {
				$this->utils->simpleEmbed("Sports Streams", "https://v2.streameast.ga/icons/favicon-48x48.png", "Sport not found. Options are AFL, NBA, NFL.", $message, true);
				return;
			}
			
			echo "VALID SPORT FOUND\n";
			
			$url = "https://streamed.pk/api/matches/".sportsID($sport);
		
			$http = new Browser();

			$http->get($url)->then(
				function (ResponseInterface $res) use ($message, $http) {
					
					$output = json_decode((string) $res->getBody());
					
					if (empty($output)) { 
						$this->utils->simpleEmbed("Sports Streams", "https://v2.streameast.ga/icons/favicon-48x48.png", "There are currently no streams available.", $message, true);
						return;
					}
					
					$streams = [];
					$promises = [];
					
					foreach ($output as $item) {
						
						$streams[$item->id] = [
							'id' => (string) $item->id,
							'title' => (string) $item->title,
							'starttime' => (int) $item->date,
							'source' => []
						];
						
						$streams[$item->id]['source'][$item->sources[0]->source] = [
							'name' => (string) $item->sources[0]->source,
							'id' => (string) $item->sources[0]->id,
							'streams' => []
						];
						
						$promises[] = $http->get("https://streamed.pk/api/stream/{$item->sources[0]->source}/{$item->sources[0]->id}");
						$sourceMap[] = ['item_id' => $item->id, 'source_id' => $item->sources[0]->source];
						
					}
					
					return \React\Promise\all($promises)->then(function(array $responses) use ($streams, $sourceMap) {
						foreach ($responses as $index => $res) {
							$data = json_decode((string) $res->getBody());
							$map = $sourceMap[$index];
							foreach ($data as $stream) {
								$streams[$map['item_id']]['sources'][$map['source_id']]['streams'][] = $stream;
							}
						}
						return $streams;
					});
				
				}
			
			)->then(function(array $allStreams) use ($message) {
				foreach ($allStreams as $stream) {
					$json = json_encode($stream, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
					if (strlen($json) > 1900) {
						$json = substr($json, 0, 1900) . "... [Truncated]";
					}
					$message->channel->sendMessage("```json\n{$json}\n```");
				}
			});
		
		}
		
		private function sportsCheck(string $sport): bool { 
		
			return match(strtoupper($sport)) {
				'AFL', 'NBA', 'NFL' => false,
				default => true,
			};
			
		}
		
		private function sportsID(string $sport): string {
			
			return match(strtoupper($sport)) {
				'AFL' => 'afl',
				'NBA' => 'basketball',
				'NFL' => 'american-football',
				default => 'afl'
			};
			
		}
		
	}

?>
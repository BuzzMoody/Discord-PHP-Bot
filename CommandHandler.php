<?php

	use Discord\Parts\Channel\Message;

	class Commands {
		
		private $functions = [];
		
		public function __construct(private Discord\Discord $discord, private PDO $pdo, private int $uptime, private BotUtils $utils) {
			
			$this->loadCommands();
			
		}
		
		public function execCommand(Message $message, string $command, string $args = ''): void {
			
			foreach ($this->functions as $pattern => $function_obj) {
				if (preg_match($pattern, $command, $matches)) {
					$function_obj->execute($message, $args, $matches);
					break;
				}
			}
			
		}
		
		private function loadCommands(string $dir = 'Commands'): void {
			
			require_once("{$dir}/CommandInterface.php");
			require_once("{$dir}/AbstractCommand.php");
			$files = scandir($dir);
			foreach ($files as $file) {
				$func_name = pathinfo($file, PATHINFO_FILENAME);
				if (substr($file, -3) === 'php' && $func_name !== 'CommandInterface' && $func_name !== 'AbstractCommand') {				
					include_once("{$dir}/{$file}");
					$function_obj = new $func_name($this->discord, $this->pdo, $this->uptime, $this->utils);				
					if ($function_obj instanceof CommandInterface) {
						$this->functions[$function_obj->getPattern()] = $function_obj;
					}				
				}			
			}
			foreach ($this->functions as $function_obj) {
				if (method_exists($function_obj, 'setCommands')) {
					$function_obj->setCommands($this->functions);
				}
			}

		}
		
	}

?>
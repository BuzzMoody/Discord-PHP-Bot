<?php

class Commands {
	
	private $uptime;
	private $patterns;
	private $discord;
	private $pdo;
	private $utils;
	
	private $functions = [];
	
	public function __construct($discord, PDO $pdo, $uptime, BotUtils $utils) {

		$this->discord = $discord;
		$this->pdo = $pdo;
		$this->uptime = $uptime;
		$this->utils = $utils;
		
		$this->loadCommands();
		
	}
	
	public function execCommand($message) {
	
		$content = trim($message->content);
		preg_match('/^!(\w+)(?:\s+(.*))?$/is', $content, $matches);
		$command = strtolower($matches[1]);
		$args = $matches[2] ?? '';		
		foreach ($this->functions as $pattern => $function_obj) {
			if (preg_match($pattern, $command, $matches)) {
				$function_obj->execute($message, $args, $matches);
				break;
			}
		}
		
	}
	
	public function loadCommands($dir = "Commands") {
		
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
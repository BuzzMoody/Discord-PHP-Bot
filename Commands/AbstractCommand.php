<?php

abstract class AbstractCommand implements CommandInterface {

	protected $discord;
	protected $pdo;
	protected $uptime;
	protected $functions; 

	public function __construct($discord, PDO $pdo, $uptime) {	
		$this->discord = $discord;
		$this->pdo = $pdo;
		$this->uptime = $uptime;
	}

	public function setFunctions(array $functions) {
		$this->functions = $functions;
	}

	abstract public function getName(): string;
	abstract public function getDesc(): string;
	abstract public function getPattern(): string;
	abstract public function execute($message, $args = null $matches = null);
	
}

?>
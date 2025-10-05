<?php

abstract class AbstractCommand implements CommandInterface {

	protected $discord;
	protected $pdo;
	protected $uptime;
	protected $utils;
	protected $functions; 

	public function __construct(Discord $discord, PDO $pdo, $uptime, BotUtils $utils) {	
		$this->discord = $discord;
		$this->pdo = $pdo;
		$this->uptime = $uptime;
		$this->utils = $utils;
	}

	public function setFunctions(array $functions) {
		$this->functions = $functions;
	}

	abstract public function getName(): string;
	abstract public function getDesc(): string;
	abstract public function getPattern(): string;
	abstract public function execute($message, $arg, $matches);
	
}

?>
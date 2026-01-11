<?php

abstract class AbstractCommand implements CommandInterface {

	protected Discord\Discord $discord;;
	protected PDO $pdo;
	protected $uptime;
	protected BotUtils $utils;;
	protected $functions; 

	public function __construct(Discord\Discord $discord, PDO $pdo, $uptime, BotUtils $utils) {	
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
	abstract public function execute(Message $message, string $args, ...$extra);
	
}

?>
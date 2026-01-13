<?php

	use Discord\Parts\Channel\Message;

	abstract class AbstractCommand implements CommandInterface {

		protected array $functions = []; 

		public function __construct(protected Discord\Discord $discord, protected PDO $pdo, protected int $uptime, protected BotUtils $utils) {	}

		public function setFunctions(array $functions): void {
			$this->functions = $functions;
		}

		abstract public function getName(): string;
		abstract public function getDesc(): string;
		abstract public function getPattern(): string;
		abstract public function execute(Message $message, string $args, array $matches): void;
		
	}

?>
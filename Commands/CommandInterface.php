<?php

interface CommandInterface {
	
	public function getName(): string;
	public function getDesc(): string;
	public function getPattern(): string;
	public function execute(Message $message, string $args, ...$extra);

}

?>
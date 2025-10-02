<?php

interface CommandInterface {
	
	public function getName(): string;
	public function getDesc(): string;
	public function getPattern(): string;
	public function execute($message, $args = null $matches = null);

}

?>
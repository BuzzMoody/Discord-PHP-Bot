<?php

	ini_set('display_errors', 1);
	error_reporting(E_ALL);

	date_default_timezone_set('Australia/Melbourne');

	include __DIR__.'/vendor/autoload.php';
	require_once 'CommandHandler.php';
	require_once 'Services.php';
	require_once 'Utils/BotUtils.php';
	require_once 'Utils/Dota.php';
	require_once 'Utils/Logger.php';

	use Discord\Discord;
	use Discord\WebSockets\Intents;
	use Discord\WebSockets\Event;
	use Discord\Parts\Channel\Message;
	use Monolog\Logger;
	use Monolog\ErrorHandler;
	
	$logger = new Logger('New logger');
	$webhookUrl = getenv('ERROR_WEBHOOK');
	$logger->pushHandler(new DiscordWebhookHandler($webhookUrl, \Monolog\Logger::ERROR));
	ErrorHandler::register($logger);
	
	$pdo = new PDO('sqlite:/Media/discord.db');
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$uptime = (int)(microtime(true) * 1000);

	$discord = new Discord([
		'token' => getenv('DISCORD_API_KEY'),
		'intents' => Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT | Intents::GUILD_MEMBERS | Intents::GUILD_PRESENCES,
		'logger' => $logger,
		'loadAllMembers' => true,
	]);

	$utils = new BotUtils($discord, $pdo);
	$dota = new Dota($discord, $pdo, $utils);
	$commands = new Commands($discord, $pdo, $uptime, $utils);
	$services = new Services($discord, $pdo);
	
	$discord->on('ready', function (Discord $discord) use ($commands, $services, $utils, $dota, $logger) {
		
		echo "(".date("d/m h:i:sA").") Bot is ready!\n";
		
		$services->updateActivity("Starting up...");
		$services->checkDatabase();

		$discord->getLoop()->addPeriodicTimer(15, function () use ($services, $utils) {
			$utils->checkReminders();
			$services->updateActivity();
		});
		
		$discord->getLoop()->addPeriodicTimer(180, function () use ($dota) {
			$dota->checkGames();
		});
		
		$discord->getLoop()->addPeriodicTimer(300, function () use ($utils) {
			$utils->checkEarthquakes();
		});

		$discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) use ($commands, $utils, $logger) {
			
			if ($message->author->bot) return;
			
			try {
			
				$channelName = $message->channel->name ?? 'DM';
				$username = $message->author->username ?? 'Unknown';
				$content = $message->content ?? '';
				
				echo "(".date("d/m h:i:sA").") [#{$channelName}] {$username}: {$content}\n";
				
				if (preg_match('/^!([a-zA-Z]{2,})(?:\s+(.*))?$/', $content, $matches)) {
					
					$command = strtolower($matches[1]);
					$args = $matches[2] ?? '';
					
					if ($message->channel->id == 274828566909157377 || !$utils->betaCheck()) {
						$commands->execCommand($message, $command, $args);
					}
					
				}
				
			} 
			
			catch (\Throwable $e) {
				
				$logger->error("Command Error: ". $e->getMessage(), [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'trace' => $e->getTraceAsString()
				]);
			
			}
			
		});
		
	});

	$discord->run();

?>
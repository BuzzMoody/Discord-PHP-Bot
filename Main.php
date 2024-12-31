<?php

include __DIR__.'/vendor/autoload.php';
include 'config.inc';
include 'CommandHandler.php';

use Discord\Discord;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Discord\Parts\User\Game;
use Discord\Parts\User\Activity;
use Discord\Parts\Embed;
use Discord\Parts\Channel\Message;

$discord = new Discord([
	'token' => $keys['discord'],
	'intents' => Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT | Intents::GUILD_MEMBERS | Intents::GUILD_PRESENCES,
	'logger' => new \Monolog\Logger('New logger'),
	'loadAllMembers' => true,
]);

$uptime = (int)(microtime(true) * 1000);
$commands = new Commands($keys, $uptime, $discord);

$discord->on('ready', function (Discord $discord) use ($commands, $keys) {
	
	echo "(".date("d/m h:i:sA").") Bot is ready!\n";
	
	$activity = $discord->factory(Activity::class, [
		'emoji' => '☁️',
		'state' => 'testing',
		'type' => Activity::TYPE_CUSTOM
	]);
	$discord->updatePresence($activity);
	
	getVertexAPI();

	$discord->getLoop()->addPeriodicTimer(15, function () use ($discord) {
		checkReminders();
		updateActivity($discord);	
	});
	
	$discord->getLoop()->addPeriodicTimer(120, function () {
		checkDota();
	});
	
	$discord->getLoop()->addPeriodicTimer(1800, function () {
		getVertexAPI();
	});

	$discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) use ($commands, $keys) {
		
		echo "(".date("d/m h:i:sA").") [#{$message->channel->name}] {$message->author->username}: {$message->content}\n";
		
		if (@$message->content[0] == "!" && @$message->content[1] != " " && !$message->author->bot && strlen(@$message->content) >= 2) { 
			if ($message->channel->id == 274828566909157377 && $keys['beta'] === true) {
				$commands->funcExec($message);
			}
			else if ($keys['beta'] === false) {
				$commands->funcExec($message);
			}
		}
		
	});
	
});

$discord->run();

function updateActivity($discord) {
	
	$activity = $discord->factory(Activity::class, [
		'emoji' => '☁️',
		'state' => 'testing',
		'type' => Activity::TYPE_CUSTOM
	]);
	$discord->updatePresence($activity);
	
}

function getMemberCount($discord) {
	
	$countGuild = $discord->guilds->get('id', '232691831090053120');
	$count = -1;
	foreach ($countGuild->members as $countMember) {
		if ($countMember->status != NULL && $countMember->status != "offline") { @$count++; }
	}
	return $count;
	
}

?>
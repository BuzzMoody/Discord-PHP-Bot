<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Australia/Melbourne');

include __DIR__.'/vendor/autoload.php';
include 'CommandHandler.php';

use Discord\Discord;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Discord\Parts\User\Game;
use Discord\Parts\User\Activity;
use Discord\Parts\Embed;
use Discord\Parts\Channel\Message;

$discord = new Discord([
	'token' => getenv('DISCORD_API_KEY'),
	'intents' => Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT | Intents::GUILD_MEMBERS | Intents::GUILD_PRESENCES,
	'logger' => new \Monolog\Logger('New logger'),
	'loadAllMembers' => true,
]);

$pdo = new PDO('sqlite:/Media/discord.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$uptime = (int)(microtime(true) * 1000);
$commands = new Commands($uptime, $discord, $pdo);

$discord->on('ready', function (Discord $discord) use ($commands, $pdo) {
	
	echo "(".date("d/m h:i:sA").") Bot is ready!\n";
	
	$activity = $discord->factory(Activity::class, [
		'name' => getMemberCount($discord)." Incels",
		'type' => Activity::TYPE_LISTENING,
	]);
	$discord->updatePresence($activity);
	checkDatabase($pdo);

	$discord->getLoop()->addPeriodicTimer(15, function () use ($discord) {
		checkReminders();
		updateActivity($discord);
	});
	
	$discord->getLoop()->addPeriodicTimer(120, function () {
		checkDota();
		checkDeadlock();
	});
	
	$discord->getLoop()->addPeriodicTimer(300, function () {
		Earthquakes();
	});

	$discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) use ($commands) {
		
		echo "(".date("d/m h:i:sA").") [#{$message->channel->name}] {$message->author->username}: {$message->content}\n";
		
		if (@$message->content[0] == "!" && @$message->content[1] != " " && !$message->author->bot && strlen(@$message->content) >= 2) { 
			if ($message->channel->id == 274828566909157377 && getenv('BETA') === 'true') {
				$commands->funcExec($message);
			}
			else if (getenv('BETA') !== 'true') {
				$commands->funcExec($message);
			}
		}
		
	});
	
});

$discord->run();

function updateActivity($discord) {
	
	$activity = $discord->factory(Activity::class, [
		'name' => getMemberCount($discord)." Incels",
		'type' => Activity::TYPE_LISTENING,
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

function checkDatabase(PDO $pdo) {

	$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name IN ('reminders', 'dota2', 'deadlock', 'earthquakes')")->fetchAll();
	if (count($tables) != 4) { shell_exec('sqlite3 /Media/discord.db < /init/init.sql'); }

}

?>
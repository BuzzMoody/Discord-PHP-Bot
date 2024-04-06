<?php

include __DIR__.'/vendor/autoload.php';
include 'config.inc';
include 'commands.php';

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Discord\Parts\User\Game;
use Discord\Parts\User\Activity;
use Discord\Parts\Embed;

$commands = new Commands($keys, floor(microtime(true) * 1000));

$discord = new Discord([
    'token' => $keys['discord'],
    'intents' => Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT | Intents::GUILD_MEMBERS | Intents::GUILD_PRESENCES,
	'logger' => new \Monolog\Logger('New logger'),
	'loadAllMembers' => true,
]);

$discord->on('ready', function (Discord $discord) use ($commands) {
	
    echo "Bot is ready!\n";
	
	$activity = $discord->factory(Activity::class, [
		'name' => getMemberCount($discord)." Incels",
		'type' => Activity::TYPE_LISTENING,
	]);
	$discord->updatePresence($activity);
	
	getMemberCount($discord);

    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) use ($commands) {
		
		echo "(".date("d/m h:i:sA").") [#{$message->channel->name}] {$message->author->username}: {$message->content}\n";
		
		if (preg_match('/(angela white|porn)/i', @$message->content) && $message->author->id == 119735534951202816) { 
			$commands->sinbin("<@119735534951202816> 10 minutes No more porn 4 you", $message, $discord, TRUE);
		}
		
		else if (preg_match('/cocaine/i', @$message->content) && $message->author->id == 193735519258148864) { 
			$commands->sinbin("<@193735519258148864> 10 minutes Don't do drugs", $message, $discord, TRUE);
		}
		
		else if (@$message->content[0] == "!" && @$message->content[1] != " " && !$message->author->bot && strlen(@$message->content) >= 2) { 
			$commands->execute($message, $discord);
		}
		
    });
	
	$discord->getLoop()->addPeriodicTimer(15, function () use ($commands, $discord) {
		$commands->checkReminders($discord);
		updateActivity($discord);	
	});
	
	$discord->getLoop()->addPeriodicTimer(10, function () use ($commands, $discord) {
		echo "Running Dota 2 Check...\n\n";
		$commands->checkDota($discord);
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
	$count = 0;
	foreach ($countGuild->members as $countMember) {
		if ($countMember->status != NULL && $countMember->status != "offline") { @$count++; }
	}
	return $count-1;
}

?>
<?php

include __DIR__.'/vendor/autoload.php';
include 'commands.php';
include 'config.inc';

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Discord\Parts\User\Game;
use Discord\Parts\User\Activity;
use Discord\Parts\Embed;

$uptime = floor(microtime(true) * 1000);

$discord = new Discord([
    'token' => $key,
    'intents' => Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT | Intents::GUILD_MEMBERS | Intents::GUILD_PRESENCES,
	'logger' => new \Monolog\Logger('New logger'),
	'loadAllMembers' => true,
]);

$discord->on('ready', function (Discord $discord) use ($uptime) {
	
    echo "Bot is ready!\n";
	
	$activity = $discord->factory(Activity::class, [
		'name' => 'The Fappening',
		'type' => Activity::TYPE_STREAMING,
	]);
	$discord->updatePresence($activity);

    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) use ($uptime) {
		
        echo "(".date("d/m h:i:sA").") [{$message->channel->name}] {$message->author->username}: {$message->content}\n";
		
		if (@$message->content[0] == "!" && !$message->author->bot && strlen(@$message->content) >= 2) { 
			Commands::execute($message, $discord, $uptime);
		}
		
    });
	
});

$discord->run();

?>

# Discord PHP  Bot

![Discord PHP Bot Logo](https://i.ibb.co/tBPQ88f/Gemini-Generated-Image-btqclpbtqclpbtqc-Photoroom.png)

This project is a Discord PHP bot running on the DiscordPHP codebase. It uses written commands (no slash commands) that are distributed in the Commands folder.

### Requirements

* PHP 8.3 or higher
* Composer
* [DiscordPHP](https://github.com/discord-php/DiscordPHP)
* Discord API Key
* [DaemonTools](https://cr.yp.to/daemontools.html)
* MySQL/MariaDB

### Installation

1. Install [DiscordPHP](https://github.com/discord-php/DiscordPHP)
2. Clone the repository: `git clone https://github.com/BuzzMoody/Discord-PHP-Bot.git`
3. Main.php and CommandHandler.php should now be in the folder with DiscordPHP
4. Configure the required API keys:

	- Create `config.inc` and update the credentials in PHP code format. For example:
```PHP
<?php

	$keys = array(
		"discord" 	=> "[DISCORDAPIKEY]",
		"mysql" 	=> "[MYSQLPW]",
		"gemini" 	=> "[GOOGLEVERTEXKEY]",
		"google"	=> "[GOOGLECONSOLEKEY]",
		"od" 		=> "[OPENDOTAKEY]",
		"wolf"		=> "[WOLFRAMKEY]"	
	);

?>
```

5. Create MySQL/MariaDB table:

> [!NOTE]
> If you don't want to create a database, be sure to remove the two functions from the loops in `Main.php` so that `CheckDota()` and `CheckReminders()` don't run.

```sql
CREATE DATABASE discord;

CREATE TABLE reminders (
   userid VARCHAR(255),
   time VARCHAR(255),
   messageid VARCHAR(255),
   channelid VARCHAR(255)
);
```

### Usage

1. Create a file called `run` and it place:
```bash
#!/usr/bin/bash

php Main.php
```
2. Using `supervise` from DaemonTools run the following CLI command: `./run`
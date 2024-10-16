# Discord-PHP-Bot

This project is a Discord PHP bot running on the DiscordPHP codebase. It uses written commands (no slash commands) that are distributed in the Commands folder.

### Requirements

* PHP 8.3 or higher
* Composer
* DiscordPHP
* Discord Bot/Application API Key

### Installation

1. Install [DiscordPHP](https://github.com/discord-php/DiscordPHP)
2. Clone the repository: `git clone https://github.com/BuzzMoody/Discord-PHP-Bot.git`

### Usage

1. **Configure the required API keys:**

   - Create `config.inc` and update the credentials in PHP code format. For example:
   
```PHP
<?php

	$keys = array(
		"discord" => "[DISCORDAPIKEY]",
		"mysql" => "[MYSQLPW]"
	);

?>
```

2. **Create MySQL/MariaDB table:**

   - Requires a database called `discord`
   - Requires a table called `reminders`

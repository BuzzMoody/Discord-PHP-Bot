<?php

use Discord\Parts\Embed\Embed;
use Carbon\Carbon;

class Commands {
	
	public $keys;
	public $uptime;
	
	function __construct($keys, $uptime) {
		
		$this->keys = $keys;
		$this->uptime = $uptime;
		
	}
	
	function execute($message, $discord) {
		
		$inputs = explode(" ", strtolower(trim($message->content)));
		$command = substr($inputs[0], 1);
		array_shift($inputs);
		$args = implode(" ", $inputs);
		
		switch ($command) {
			
			case "ping":
				$message->reply("Pong!");
				break;
				
			case (preg_match('/^(kate|t(?:ay(lor)?|swizzle)|emma|e?liz(abeth)?|olympia|olivia|kim|mckayla|zach|hilary|ronan)\b/', $command, $babe) ? true : false):
				$this->sendBabe($babe, $message);
				break;
				
			case (preg_match('/^(search|google|bing|find|siri)/', $command) ? true : false):
				$this->search('google', $args, $message);
				break;
				
			case (preg_match('/^(image|img|photo|pic)/', $command) ? true : false):
				$this->search('image', $args, $message);
				break;
				
			case (preg_match('/^(ban|kick|sb|sinbin)/', $command) ? true : false):
				$this->sinbin($args, $message, $discord);
				break;
			
			/*case (preg_match('/^(chat(gpt?)|(open)?ai)/', $command) ? true : false):
				$this->chatGPT($args, $message);
				break;
				
			case (preg_match('/^dalle/', $command) ? true : false):
				$this->chatGPT($args, $message, true);
				break;*/
				
			case (preg_match('/^(asx|share(s)?|stock(s)?|etf)/', $command) ? true : false):
				$this->ASX($args, $message, $discord);
				break;
				
			case (preg_match('/^(weather|temp(erature)?)$/', $command) ? true : false):
				$this->weather($message);
				break;
				
			case (preg_match('/^(shell|bash|cli|cmd)/', $command) ? true : false):
				$this->runcli($args, $message, $discord);
				break;
				
			case "apex":
				$this->apex($message, $discord);
				break;
				
			case "uptime":
				$this->uptime($message);
				break;
		
		}
		
	}
	
	function sendBabe($babe, $message) {
	
		$imgDir = "/home/buzz/img/".preg_replace(array('/e?liz(abeth)?\b/', '/t(ay)?(lor)?(swizzle)?\b/'), array('elizabeth', 'taylor'), $babe[0]);
		$files = (is_dir($imgDir)) ? scandir($imgDir) : null;
		if ($files) { 
			$message->channel->sendFile("{$imgDir}/{$files[rand(2,(count($files) - 1))]}", $babe[0].".jpg");
		}
		
	}
	
	function search($type, $args, $message) {
	
		if (empty($args)) { return $message->reply("Maybe give me something to search for??"); }
		
		$search = ($type == "google") ? @file_get_contents("https://www.googleapis.com/customsearch/v1?key={$this->keys['google']}&cx=017877399714631144452:hlos9qn_wvc&googlehost=google.com.au&num=1&q=".str_replace(' ', '%20', $args)) : @file_get_contents("https://www.googleapis.com/customsearch/v1?key={$this->keys['google']}&cx=017877399714631144452:0j02gfgipjq&googlehost=google.com.au&searchType=image&excludeTerms=youtube&imgSize=xxlarge&safe=off&num=1&fileType=jpg,png,gif&q=".str_replace(' ', '%20', $args)."%20-site:facebook.com%20-site:tiktok.com%20-site:instagram.com");
		
		$return = json_decode($search);
		
		if ($return->searchInformation->totalResults == 0) { return $message->reply("No results."); }
		
		return ($type == "google") ? $message->channel->sendMessage("{$return->items[0]->title}: {$return->items[0]->link}") : $message->channel->sendMessage($return->items[0]->link);
	
	}
	
	function chatGPT($args, $message, $dalle = false) {
		
		if (empty($args)) { return $message->reply("Maybe give the AI something to do??"); }
		
		$post_fields = (!$dalle) ? array("model" => "text-davinci-003", "prompt" => $args, "temperature" => 0.3, "max_tokens" => 150, "top_p" => 1.0, "frequency_penalty" => 0.0, "presence_penalty" => 0.0) : array("prompt" => $args,	"n" => 1, "size" => "1024x1024");
		$apiURL = (!$dalle) ? "https://api.openai.com/v1/completions" : "https://api.openai.com/v1/images/generations";
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $apiURL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => json_encode($post_fields),
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer '.$this->keys['openai'],
				'Content-Type: application/json'
			)
		));
		
		$response = json_decode(curl_exec($curl));
		curl_close($curl);
		
		if (@$response->error->message) { return $message->reply($response->error->message); }
		
		$output = (!$dalle) ? trim($response->choices[0]->text) : trim($response->data[0]->url);

		$message->channel->sendMessage($output);
		
	}
	
	function bard($args, $message) {
		
		if (empty($args)) { return $message->reply("Maybe give the AI something to do??"); }
		
		$post_fields = array(
			"prompt" => array(
				"text" => $args
			)
		);	
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://generativelanguage.googleapis.com/v1beta2/models/text-bison-001:generateText?key='.$this->keys['bard'],
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => json_encode($post_fields),
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json'
			),
		));
		
		$response = json_decode(curl_exec($curl));
		curl_close($curl);
		
		if (@$response->error->message) { return $message->reply($response->error->message); }

		$message->channel->sendMessage($response->candidates[0]->output;
		
	}
	
	function weather($message) {
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://api.weather.bom.gov.au/v1/locations/r1ppvy/observations");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36");
		$temp = json_decode(curl_exec($ch));
		$message->channel->sendMessage("{$temp->data->temp}° (Feels {$temp->data->temp_feels_like}°) | Wind: {$temp->data->wind->speed_kilometre}kph ".preg_replace(array('/^N$/', '/^S$/', '/^E$/', '/^W$/', '/^.?NE$/', '/^.?SE$/', '/^.?SW$/', '/^.?NW$/'), array('↓', '↑', '←', '→', '↙', '↖', '↗', '↘'), $temp->data->wind->direction)." | Humidity: {$temp->data->humidity}% | Rain: {$temp->data->rain_since_9am}mm");
		
	}
	
	function uptime($message) {
		
		$diff = (floor(microtime(true) * 1000) - $this->uptime) / 1000;
		$days = floor($diff / 86400);
		$diff -= $days * 86400;
		$hours = floor($diff / 3600) % 24;
		$diff -= $hours * 3600;
		$minutes = floor($diff / 60) % 60;
		$diff -= $minutes * 60;
		$seconds = floor($diff % 60);
		$message->reply("{$days} days, {$hours} hrs, {$minutes} mins, {$seconds} secs");
		
	}
	
	function ASX($args, $message, $discord) {
		
		if (empty($args) || strlen($args) > 4) { return $message->reply("Try !asx DMP"); }
		
		if (false === ($header = @file_get_contents("https://asx.api.markitdigital.com/asx-research/1.0/companies/{$args}/header"))) {
			return $message->reply("Invalid search. Try !asx DMP"); 
		}
		
		$asxInit = json_decode($header);
		$asx["Current Price"] = "$".number_format($asxInit->data->priceLast, 2);
		$asx["Change"] = number_format($asxInit->data->priceChangePercent, 2)."%";
		$asx["Name"] = $asxInit->data->displayName;
		$asx["URL"] = "https://www2.asx.com.au/markets/company/{$args}";
		$asx["Market Cap"] = ($asxInit->data->securityType == 7) ? "ETF" : "$".number_format($asxInit->data->marketCap);
		$key = json_decode(file_get_contents("https://asx.api.markitdigital.com/asx-research/1.0/companies/{$args}/key-statistics"));
		$asx["52W ↑ / ↓"] = "$".$key->data->priceFiftyTwoWeekHigh." / $".$key->data->priceFiftyTwoWeekLow;
		$asx["Earnings Per Share"] = (!$key->data->earningsPerShare) ? "ETF" : "$".$key->data->earningsPerShare;
		$asx["Annual Yield"] = (!$key->data->yieldAnnual) ? "ETF" : number_format($key->data->yieldAnnual, 2)."%";
		
		if ($asx["Market Cap"] == "ETF") {
			$keyETF = json_decode(file_get_contents("https://asx.api.markitdigital.com/asx-research/1.0/etfs/{$args}/key-statistics"));
			$asx["NAV"] = "$".$keyETF->data->shareInformation->nav;
			$asx["YTD Return"] = $keyETF->data->fundamentals->returnYearToDate."%";
			$asx["Mgmt Fee"] = $keyETF->data->fundamentals->managementFeePercent."%";
			$asx["URL"] = "https://www2.asx.com.au/markets/etp/{$args}";
		}
		
		$embed = $discord->factory(Embed::class);
		$embed->setTitle($asx["Name"])
			->setURL($asx["URL"])
			->setDescription("ASX : ".strtoupper($args))
			->setColor("0x00A9FF")
			->setTimestamp()
			->setFooter("ASX", "https://www2.asx.com.au/content/dam/asx/asx-logos/asx-brandmark.png");
		
		foreach ($asx as $key => $value) {		
			if ($key == "Name" || $key == "URL" || $value == "ETF" ) { }
			else {	
				$embed->addFieldValues("{$key}", "{$value}", true);
			}
		}
		
		$message->channel->sendEmbed($embed);
	}
	
	function sinbin($args, $message, $discord) {
		
		if (empty($args)) { return $message->reply("Try !sinbin @username"); }
		
		if ($this->isAdmin($message->author->id, $discord)) {
			$argz = explode(" ", $args);
			$sbID = str_replace(array('<','@','!','>', '&'),'', $argz[0]);
		 	$sbGuild = $discord->guilds->get('id', '232691831090053120');
			$sbMember = $sbGuild->members->get('id', strval($sbID));
			$time = (count($argz) <= 1) ? 1 : $argz[1];
			$sbMember->timeoutMember(new Carbon("{$time} minutes"))->done(function () {});
			$message->channel->sendMessage("{$argz[0]} has been given a {$time} minute timeout");
		}
		
	}
	
	function runcli($args, $message, $discord) {
		
		if ($message->author->id == 232691181396426752 && !empty($args)) {		
			$message->channel->sendMessage("```swift\n".shell_exec($args)."\n```");		
		}
		
	}
	
	function isAdmin($userID, $discord) {
		
		$testGuild = $discord->guilds->get('id', '232691831090053120');
		$testMember = $testGuild->members->get('id', $userID);
		return $testMember->roles->has('232692759557832704');
		
	}
	
	function apex($message, $discord) {
		
		$get = file_get_contents("https://apexlegendsstatus.com/current-map/battle_royale/pubs");
		preg_match('/<h3 .*>(.+)<\/h3>.+ ends in (.+)<\/p>/U', $get, $data);
		preg_match_all('/<h3 .*>(.+)<\/h3>/U', $get, $next);
	
		$message->channel->sendMessage($data[1]." ends in ".$data[2]." | Next Map: ".$next[1][1]);
	}
	
}

?>
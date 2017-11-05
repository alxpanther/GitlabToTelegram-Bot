<?php // © Yannik Bloscheck - All rights reserved
$gitlabToken = "GITLAB_TOKEN";
$telegramToken = "TELEGRAM_TOKEN";
$telegramChatId = TELEGRAM_CHATID;
if (isset($_SERVER['HTTP_X_GITLAB_TOKEN']) && $_SERVER['HTTP_X_GITLAB_TOKEN'] == $gitlabToken) {
	$gitlabData = json_decode(file_get_contents('php://input'), true);
	
	if (isset($_SERVER['HTTP_X_GITLAB_EVENT']) && (($_SERVER['HTTP_X_GITLAB_EVENT'] == "Push Hook" && $gitlabData["ref"] == "refs/heads/master") || ($_SERVER['HTTP_X_GITLAB_EVENT'] == "Merge Request Hook" && $gitlabData["object_attributes"]["state"] != "merged" && $gitlabData["object_attributes"]["action"] == "open"))) {
		$message = "";
		
		if ($_SERVER['HTTP_X_GITLAB_EVENT'] == "Push Hook") {
			$commits = $gitlabData["commits"];
			
			$commit = $commits[0];
			$message = '<b>'.trim($commit["author"]["name"]).':</b>\n'.trim($commit["message"]).'\n<a href="'.$commit["url"].'">Details...</a>';
			
			if (count($commits) > 1) {
				for ($i = 1; $i < count($commits); $i++) {
					$commit = $commits[$i];
					$message = $message.'\n\n<b>'.trim($commit["author"]["name"]).':</b>\n'.trim($commit["message"]).'\n<a href="'.$commit["url"].'">Details...</a>';
				}
			}
		} else if ($_SERVER['HTTP_X_GITLAB_EVENT'] == "Merge Request Hook") {
			$merge = $gitlabData["object_attributes"];
			$message = '<b>'.trim($merge["last_commit"]["author"]["name"]).':</b>\nMerge request !'.$merge["iid"].'\n\n'.trim($merge["title"]).'\n<a href="'.$gitlabData["repository"]["homepage"].'/merge_requests/'.$merge["iid"].'">Details...</a>';
		}
		
		$telegramData = array();
		$telegramData["chat_id"]=$telegramChatId;
		$telegramData["text"]=$message;
		$telegramData["parse_mode"]='HTML';
		$telegramData["disable_web_page_preview"]=true;
		$telegramData["disable_notification"]=true;	
			
		$curlHandle = curl_init('https://api.telegram.org/bot'.$telegramToken.'/sendMessage');
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($curlHandle, CURLOPT_TIMEOUT, 60);
		curl_setopt($curlHandle, CURLOPT_POSTFIELDS, str_replace("\\n", "\n", json_encode($telegramData)));
		curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		curl_exec($curlHandle);
	}
}
?>

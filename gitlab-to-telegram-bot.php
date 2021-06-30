<?php
///
// © Yannik Bloscheck - All rights reserved (https://github.com/yannikbloscheck/GitlabToTelegram-Bot)
// edited by Alexander Fedorko (alx69@ukr.net)
//
$gitlabToken = "xxxxxxx"; // any set of letters and numbers
$telegramToken = "xxxxxxxxxx:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA";
$telegramChatId = "idididididid";

//$scr_name = getcwd()."/".basename(__FILE__, '.php');
$scr_name = getcwd()."/".str_replace(" ", "_", $_SERVER['HTTP_X_GITLAB_EVENT']);
//if(!empty($_SERVER)) { $f_server = fopen($scr_name."_server.log", "a+"); fwrite($f_server, "(".date("Y-m-d H:i:s").") Пришел SERVER:\n".print_r($_SERVER, true)."\n"); fclose($f_server); }


if (isset($_SERVER['HTTP_X_GITLAB_TOKEN']) && $_SERVER['HTTP_X_GITLAB_TOKEN'] == $gitlabToken) {
	$gitlabData = json_decode(file_get_contents('php://input'), true);

	// $gitlabData to file
	file_put_contents($scr_name."_gitlabData.log", print_r($gitlabData, true), FILE_APPEND);

	// Push Hook
	if (isset($_SERVER['HTTP_X_GITLAB_EVENT']) && (($_SERVER['HTTP_X_GITLAB_EVENT'] == "Push Hook" && $gitlabData["ref"] == "refs/heads/master" && $gitlabData["total_commits_count"] != 0) || ($_SERVER['HTTP_X_GITLAB_EVENT'] == "Merge Request Hook" && $gitlabData["object_attributes"]["state"] != "merged" && $gitlabData["object_attributes"]["action"] == "open"))) {
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
			$message = '<b>'.trim($merge["last_commit"]["author"]["name"]).':</b>\nMerge request !'.$merge["iid"].'\n<i>'.$gitlabData["object_attributes"]["source_branch"].' ▶︎ '.$gitlabData["object_attributes"]["target_branch"].'</i>\n\n'.trim($merge["title"]).'\n<a href="'.$gitlabData["repository"]["homepage"].'/merge_requests/'.$merge["iid"].'">Details...</a>';
		}
	}
	// Job Hook
	if ( isset($_SERVER['HTTP_X_GITLAB_EVENT']) && $_SERVER['HTTP_X_GITLAB_EVENT'] == "Job Hook" ) {
/*
[ref] = dev
[build_name] => build-srv:btc-nginx
[build_stage] => build
[build_status] => success
[build_status] => manual
[build_status] => pending
[build_status] => running
[build_duration] => 4.040994
[project_name] => profit / Viabtc_trade_server
[repository] => Array
        [name] => Viabtc_trade_server
        [url] => git@gitlab.com:profit-2018/viabtc_trade_server.git
[commit] => Array
        [message] => change stages name
        [author_name] => Alexander Fedorko
*/
		$message = "";

		$duration = ' ('.trim($gitlabData["build_duration"]).' sec)\n';

		// Set pictures, duration job OR exit(!)
		if ( trim($gitlabData["build_status"]) == 'success' ) {
//			$job_pic = '&#x1F7E2;'; // green circle
			$job_pic = '&#x2705;'; // green check mark
		} elseif ( trim($gitlabData["build_status"]) == 'manual' ) {
			$job_pic = '&#x1F535;'; // blue circle
			$duration = '\n';
			exit(0);
		} elseif ( trim($gitlabData["build_status"]) == 'canceled' ) {
			$job_pic = '&#x26AA;'; // white circle
			$duration = '\n';
		} elseif ( trim($gitlabData["build_status"]) == 'skipped' ) {
//			$job_pic = '&#x26AA;'; // white circle
//			$duration = '\n';
			exit(0);
		} elseif ( trim($gitlabData["build_status"]) == 'created' || trim($gitlabData["build_status"]) == 'pending' || trim($gitlabData["build_status"]) == 'running' ) {
			exit(0);
		} elseif ( trim($gitlabData["build_status"]) == 'failed' ) {
//			$job_pic = '&#x1F534;'; // red circle
			$job_pic = '&#x203C;'; // red 2 exclamation signs (&#x203C;  or   &#xFE0F;)
		}
		$message = 'Service/Repo:<a href="'.trim($gitlabData["repository"]["homepage"]).'">'.trim($gitlabData["repository"]["name"]).'</a> (branch: '.trim($gitlabData["ref"]).')\n';
		$message = $message.'Running by: '.trim($gitlabData["commit"]["author_name"]).'\n';
		$message = $message.$job_pic.' CI: '.trim($gitlabData["build_name"]).' - <b>'.trim($gitlabData["build_status"]).'</b>'.$duration;
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
?>

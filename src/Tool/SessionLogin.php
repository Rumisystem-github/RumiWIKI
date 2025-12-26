<?php
//セッションでログインする
$login = false;
$user = null;

if (isset($_COOKIE["SESSION"])) {
	$session_data = json_decode($_COOKIE["SESSION"], true);

	if (!isset($session_data)) {
		setcookie("SESSION", "");
	}

	if ($session_data["TYPE"] == "RSV") {
		//るみ鯖
		$token = $session_data["TOKEN"];
		$ajax = curl_init("https://account.rumiserver.com/api/Session?ID=".$token);
		curl_setopt($ajax, CURLOPT_RETURNTRANSFER, true);
		$result = json_decode(curl_exec($ajax), true);
		if ($result["STATUS"] == false) {
			setcookie("SESSION", "");
		}

		$login = true;
		$user = $result["ACCOUNT_DATA"];
	}
}
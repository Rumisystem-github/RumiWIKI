<?php
function CFTCheck($Responce) {
	global $CONFIG;

	//CFTチェック
	$CFT_AJAX = curl_init("https://challenges.cloudflare.com/turnstile/v0/siteverify");
	curl_setopt($CFT_AJAX, CURLOPT_POST, true);
	curl_setopt($CFT_AJAX, CURLOPT_POSTFIELDS, json_encode(array("secret" => $CONFIG["CFT"]["SECRET_KEY"], "response" => $Responce)));
	curl_setopt($CFT_AJAX, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($CFT_AJAX, CURLOPT_HTTPHEADER, array(
		"Content-Type: application/json"
	));
	$CFT_RESULT = json_decode(curl_exec($CFT_AJAX), true);
	curl_close($CFT_AJAX);

	return $CFT_RESULT["success"];
}
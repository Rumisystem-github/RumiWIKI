<?php
function cft_check($responce) {
	global $config;

	//CFTチェック
	$ajax = curl_init("https://challenges.cloudflare.com/turnstile/v0/siteverify");
	curl_setopt($ajax, CURLOPT_POST, true);
	curl_setopt($ajax, CURLOPT_POSTFIELDS, json_encode(array("secret" => $config["CFT"]["SECRET_KEY"], "response" => $responce)));
	curl_setopt($ajax, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ajax, CURLOPT_HTTPHEADER, array(
		"Content-Type: application/json"
	));
	$result = json_decode(curl_exec($ajax), true);

	return $result["success"];
}
<?php
function RSVLogin($TOKEN) {
	$Login = json_decode(file_get_contents("https://account.rumiserver.com/api/Session?ID=".$TOKEN), true);
	if ($Login["STATUS"]) {
		return $Login["ACCOUNT_DATA"];
	} else {
		return null;
	}
}
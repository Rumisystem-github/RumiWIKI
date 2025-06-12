<?php
$RSVAccountCache = [];

function RSVLogin($TOKEN) {
	$Login = json_decode(file_get_contents("https://account.rumiserver.com/api/Session?ID=".$TOKEN), true);
	if ($Login["STATUS"]) {
		return $Login["ACCOUNT_DATA"];
	} else {
		return null;
	}
}

function RSVGetAccount($UID) {
	global $RSVAccountCache;

	if (isset($RSVAccountCache[$UID])) {
		return $RSVAccountCache[$UID];
	} else {
		$Login = json_decode(file_get_contents("https://account.rumiserver.com/api/User?ID=".$UID), true);
		if ($Login["STATUS"]) {
			$RSVAccountCache[$UID]  = $Login["ACCOUNT"];
			return $Login["ACCOUNT"];
		} else {
			return null;
		}
	}
}
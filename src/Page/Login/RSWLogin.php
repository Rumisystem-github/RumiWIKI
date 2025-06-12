<?php
if (isset($_GET["SESSION"])) {
	$AJAX = curl_init("https://account.rumiserver.com/api/AUTH/Check");
	curl_setopt($AJAX, CURLOPT_POST, true);
	curl_setopt($AJAX, CURLOPT_HTTPHEADER, ["Content-Type: application/json; charset=UTF-8"]);
	curl_setopt($AJAX, CURLOPT_POSTFIELDS, json_encode([
		"APP" => $CONFIG["DEPENDENCY"]["ACCOUNT_RSW_ID"],
		"SESSION" => $_GET["SESSION"],
		"TOKEN" => $CONFIG["DEPENDENCY"]["ACCOUNT_RSW_TOKEN"]
	]));
	curl_setopt($AJAX, CURLOPT_RETURNTRANSFER, true);
	$RESULT = json_decode(curl_exec($AJAX), true);

	if ($RESULT["STATUS"]) {
		$USER_INFO = json_decode(file_get_contents("https://account.rumiserver.com/api/Session?ID=".$RESULT["TOKEN"]), true);
		if ($USER_INFO["STATUS"]) {
			$COOKIE_KIGEN = time() + (30 * 24 * 60 * 60);//30日
			setcookie("SESSION", json_encode([
				"TYPE" => "RSV",
				"TOKEN" => $RESULT["TOKEN"]
			]), $COOKIE_KIGEN, "/");
			echo "ようこそ".htmlspecialchars($USER_INFO["ACCOUNT_DATA"]["NAME"])."さん<BR>";
			echo "<A HREF=\"/\">戻る</A>";
		} else {
			echo "セッションエラー";
		}
	} else {
		header("Content-Type: text/plain; charset=UTF-8");
		switch ($RESULT["ERR"]) {
			case "NTF": {
				echo "セッションが不正です";
				return;
			}

			default: {
				echo "不明なエラー:".json_encode($RESULT);
			}
		}
	}
} else {
	echo "エラー";
}
<?php
/**
 * 記事の作成を実行する
 */
if (!($_SERVER["REQUEST_METHOD"] == "POST")) {
	return;
}

//CFTチェック
$CFT_AJAX = curl_init("https://challenges.cloudflare.com/turnstile/v0/siteverify");
curl_setopt($CFT_AJAX, CURLOPT_POST, true);
curl_setopt($CFT_AJAX, CURLOPT_POSTFIELDS, json_encode(array("secret" => $CONFIG["CFT"]["SECRET_KEY"], "response" => $_POST["cf-turnstile-response"])));
curl_setopt($CFT_AJAX, CURLOPT_RETURNTRANSFER, true);
curl_setopt($CFT_AJAX, CURLOPT_HTTPHEADER, array(
	"Content-Type: application/json"
));
$CFT_RESULT = json_decode(curl_exec($CFT_AJAX), true);
curl_close($CFT_AJAX);

//CFTは認証できたか
if (!$CFT_RESULT["success"]) {
	echo "作成失敗！";
	http_response_code(401);
}

$ID = GenSnowFlake();

SQL_RUN($PDO, "INSERT INTO `PAGE_INFO` (`ID`, `DATE`, `LOCK`) VALUES (:ID, NOW(), 0);", array(
	array(
		"KEY" => "ID",
		"VAL" => $ID
	)
));

SQL_RUN($PDO, "INSERT INTO `PAGE_DATA` (`ID`, `PAGE`, `DATE`, `UID`, `TITLE`, `TEXT`, `MESSAGE`) ".
				"VALUES (:ID, :PAGE, NOW(), :UID, :TITLE, '# サンプルテキスト', '記事が作成されました')",
	array(
		array(
			"KEY" => "ID",
			"VAL" => GenSnowFlake()
		),
		array(
			"KEY" => "PAGE",
			"VAL" => $ID
		),
		array(
			"KEY" => "UID",
			"VAL" => $ACCOUNT["ID"]
		),
		array(
			"KEY" => "TITLE",
			"VAL" => $_POST["TITLE"]
		)
	)
);

header("Location: ".urlencode($CONFIG["PAGE"]["URL"]."page/".$_POST["TITLE"]));
<?php
/**
 * 記事の作成を実行する
 */
if (!($_SERVER["REQUEST_METHOD"] == "POST")) {
	return;
}

//CFTは認証できたか
if (!CFTCheck($_POST["cf-turnstile-response"])) {
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
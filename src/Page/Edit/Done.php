<?php
header("Content-Type: application/json; charset=UTF-8");

if (!$LOGIN_OK) {
	echo json_encode(["STATUS"=>false, "ERR"=>"LOGIN"]);
	exit;
}

//CFTは認証できたか
if (!CFTCheck($_POST["CFT"])) {
	echo json_encode(["STATUS"=>false, "ERR"=>"CFT"]);
	exit;
}
$ID = $_POST["ID"];
$TEXT = $_POST["TEXT"];
$PAGE = GetPageFromID($ID);

//記事有る？
if ($PAGE == null) {
	echo json_encode(["STATUS"=>false, "ERR"=>"NTF"]);
}

//最新の記事と内容が一緒なら変更する必要がないので却下
if ($PAGE["TEXT"] == $TEXT) {
	echo json_encode(["STATUS"=>false, "ERR"=>"ISSHO"]);
}

SQL_RUN($PDO, "INSERT INTO `PAGE_DATA` (`ID`, `PAGE`, `DATE`, `UID`, `TITLE`, `TEXT`, `MESSAGE`) ".
				"VALUES (:ID, :PAGE, NOW(), :UID, :TITLE, :TEXT, :MESSAGE);",
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
			"VAL" => $PAGE["TITLE"]
		),
		array(
			"KEY" => "TEXT",
			"VAL" => $_POST["TEXT"]
		),
		array(
			"KEY" => "MESSAGE",
			"VAL" => $_POST["MESSAGE"]
		)
	)
);

echo json_encode(["STATUS"=>true]);
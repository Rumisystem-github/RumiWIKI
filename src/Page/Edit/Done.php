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
$DataID = GenSnowFlake();
$TEXT = $_POST["TEXT"];
$PAGE = GetPageFromID($ID);
$SOURCE = json_decode($_POST["SOURCE"], true);

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
			"VAL" => $DataID
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

foreach (array_keys($SOURCE) as $Index) {
	SQL_RUN($PDO, "INSERT INTO `PAGE_SOURCE` (`ID`, `INDEX`, `DATA`, `URL`, `ARCHIVE_URL`, `COMMENT`) VALUES (:ID, :INDEX, :DATA, :URL, NULL, '');", [
		[
			"KEY" => "ID",
			"VAL" => GenSnowFlake()
		],
		[
			"KEY" => "INDEX",
			"VAL" => $Index
		],
		[
			"KEY" => "DATA",
			"VAL" => $DataID
		],
		[
			"KEY" => "URL",
			"VAL" => $SOURCE[$Index]
		]
	]);
}

echo json_encode(["STATUS"=>true]);
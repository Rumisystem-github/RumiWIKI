<?php
if ($login == false) {
	echo json_encode(["STATUS" => false]);
	return;
}

require(__DIR__."/../Tool/CFTCheck.php");

header("Content-Type: application/json; charset=UTF-8");
$post = json_decode(file_get_contents("php://input"), true);

if (!cft_check($post["CFT"])) {
	echo json_encode(["STATUS" => false]);
	return;
}

$commit_message = $post["COMMIT"]["MESSAGE"];

$page_id = $post["PAGE"]["ID"];
$page_data_id = GenSnowFlake();
$page_title = $post["PAGE"]["TITLE"];
$page_text = $post["PAGE"]["TEXT"];

$source_list = $post["SOURCE"];

$stmt = $sql->prepare("INSERT INTO `PAGE_DATA` (`ID`, `PAGE`, `DATE`, `USER`, `TITLE`, `TEXT`, `MESSAGE`) VALUES (:ID, :PAGE, NOW(), :USER, :TITLE, :TEXT, :MESSAGE);");
$stmt->bindValue(":ID", $page_data_id);
$stmt->bindValue(":PAGE", $page_id);
$stmt->bindValue(":USER", $user["ID"]);
$stmt->bindValue(":TITLE", $page_title);
$stmt->bindValue(":TEXT", $page_text);
$stmt->bindValue(":MESSAGE", $commit_message);
$stmt->execute();

foreach ($source_list as $source) {
	$stmt = $sql->prepare("INSERT INTO `PAGE_SOURCE` (`ID`, `INDEX`, `DATA`, `URL`, `ARCHIVE_URL`, `COMMENT`) VALUES (:ID, :INDEX, :DATA, :URL, :ARCHIVE_URL, :COMMENT)");
	$stmt->bindValue(":ID", GenSnowFlake());
	$stmt->bindValue(":INDEX", $source["INDEX"]);
	$stmt->bindValue(":DATA", $page_data_id);
	$stmt->bindValue(":URL", $source["URL"]);
	if ($source["ARCHIVE_URL"] != "") {
		$stmt->bindValue(":ARCHIVE_URL", $source["ARCHIVE_URL"]);
	} else {
		$stmt->bindValue(":ARCHIVE_URL", null);
	}
	if ($source["COMMENT"] != "") {
		$stmt->bindValue(":COMMENT", $source["COMMENT"]);
	} else {
		$stmt->bindValue(":COMMENT", null);
	}
	$stmt->execute();
}

echo json_encode(["STATUS" => true]);
exit;
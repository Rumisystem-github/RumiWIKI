<?php
function GetPageFromTitle($TITLE) {
	return GetPage($TITLE, "");
}

function GetPageFromID($ID) {
	return GetPage("", $ID);
}

function GetPage($TITLE, $ID) {
	global $PDO;

	$SQL_RESULT = SQL_RUN($PDO,
		<<<TEXT
			SELECT
				`D`.`PAGE` AS `ID`,
				`D`.`TITLE`,
				`D`.`DATE`,
				`D`.`TITLE`,
				`D`.`TEXT`,
				`I`.`LOCK`
			FROM
				`PAGE_DATA` AS `D`
			JOIN
				`PAGE_INFO` AS `I` ON `I`.`ID` = `D`.`PAGE`
			WHERE
				`D`.`TITLE` = :TITLE
			OR
				`D`.`PAGE` = :ID
			ORDER
				BY `D`.`DATE` DESC
			LIMIT 1;
		TEXT,
		array(
			array(
				"KEY" => "TITLE",
				"VAL" => $TITLE
			),
			array(
				"KEY" => "ID",
				"VAL" => $ID
			)
		)
	);

	if ($SQL_RESULT["STATUS"] && count($SQL_RESULT["RESULT"]) == 1) {
		return $SQL_RESULT["RESULT"][0];
	} else {
		return null;
	}
}
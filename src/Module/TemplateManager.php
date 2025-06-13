<?php
function GetTemplate() {
	global $PDO;

	$SQL_RESULT = SQL_RUN($PDO,
		<<<TEXT
			SELECT
				*
			FROM
				`TEMPLATE`;
		TEXT,
		[]
	);

	if ($SQL_RESULT["STATUS"]) {
		$Table = [];

		foreach ($SQL_RESULT["RESULT"] as $Row) {
			$Table[$Row["ID"]] = [
				"TITLE" => $Row["TITLE"],
				"CONTENTS" => $Row["CONTENTS"],
				"VERSION" => $Row["VERSION"]
			];
		}

		return $Table;
	} else {
		return null;
	}
}
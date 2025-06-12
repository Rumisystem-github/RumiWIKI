<?php
function GetAlert() {
	global $PDO;

	$SQL_RESULT = SQL_RUN($PDO,
		<<<TEXT
			SELECT
				*
			FROM
				`ALERT`;
		TEXT,
		[]
	);

	if ($SQL_RESULT["STATUS"]) {
		$Table = [];

		foreach ($SQL_RESULT["RESULT"] as $Row) {
			$Table[$Row["ID"]] = $Row["TEXT"];
		}

		return $Table;
	} else {
		return null;
	}
}
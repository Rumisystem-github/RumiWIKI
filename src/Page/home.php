<?=$CONFIG["PAGE"]["NAME"]?>へようこそ！<BR>

<BR>

最新の記事<BR>
<?php
$GetLatestPageSQL = SQL_RUN($PDO,
	<<<TEXT
		SELECT
			`D`.`PAGE` AS `ID`,
			`D`.`TITLE`,
			`D`.`DATE`
		FROM
			`PAGE_INFO` AS `I`
		JOIN
			`PAGE_DATA` AS `D` ON `I`.`ID` = `D`.`PAGE`
		ORDER
			BY `D`.`DATE` DESC
		LIMIT 1;
	TEXT,
	[]
);

if ($GetLatestPageSQL["STATUS"]) {
	foreach ($GetLatestPageSQL["RESULT"] as $Row) {
		echo "<A HREF=\"/page/".htmlspecialchars($Row["TITLE"])."\">".htmlspecialchars($Row["TITLE"])."</A>";
	}
}
?>
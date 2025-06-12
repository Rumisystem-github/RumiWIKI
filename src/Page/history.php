<?php
$ID = urldecode(str_replace("/history/", "", $REQUEST_PATH));
$SQL_RESULT = SQL_RUN($PDO,
	<<<TEXT
		SELECT
			*
		FROM
			`PAGE_DATA`
		WHERE
			`PAGE` = :ID
		ORDER
			BY `DATE` DESC;
	TEXT,
	array(
		array(
			"KEY" => "ID",
			"VAL" => $ID
		)
	)
);

if ($SQL_RESULT["STATUS"]) {
	?><TABLE BORDER="1">
		<TR>
			<TH>ID</TH>
			<TH>ユーザー</TH>
			<TH>内容</TH>
		</TR><?php
	foreach ($SQL_RESULT["RESULT"] as $Row) {
		?>
		<TR>
			<TD><A HREF="/commit/<?=htmlspecialchars($Row["ID"])?>"><?=htmlspecialchars($Row["ID"])?></A></TD>
			<TD><?=htmlspecialchars(RSVGetAccount($Row["UID"])["NAME"])?></TD>
			<TD><?=htmlspecialchars($Row["MESSAGE"])?></TD>
		</TR>
		<?php
	}
	?></TABLE><?php
} else {
	echo "SQLから取得できませんでした";
}
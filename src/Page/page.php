<?php
$TITLE = urldecode(str_replace("/page/", "", $REQUEST_PATH));

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
		LIMIT 1;
	TEXT,
	array(
		array(
			"KEY" => "TITLE",
			"VAL" => $TITLE
		)
	)
);

if ($SQL_RESULT["STATUS"] && count($SQL_RESULT["RESULT"]) == 1) {
	$PAGE_ID = $SQL_RESULT["RESULT"][0]["ID"];
	$PAGE_TITLE = $SQL_RESULT["RESULT"][0]["TITLE"];
	$PAGE_TEXT = $SQL_RESULT["RESULT"][0]["TEXT"];
	$PAGE_DATE = new DateTime($SQL_RESULT["RESULT"][0]["DATE"]);
	if ($SQL_RESULT["RESULT"][0]["LOCK"] == 1) {
		$PAGE_LOCK = true;
	} else {
		$PAGE_LOCK = false;
	}
} else {
	$PAGE_TITLE = "記事がありません";
	$PAGE_TEXT = "記事を作成できますよ";
	$PAGE_DATE = new DateTime();
	$PAGE_LOCK = true;
}
?>

<DIV>
	<H1 STYLE="display: inline;"><?=htmlspecialchars($PAGE_TITLE)?></H1>

	<?php
	if (!$PAGE_LOCK) {
		?>
		<A HREF="/edit/<?=htmlspecialchars($PAGE_ID)?>">編集する</A>
		<?php
	}
	?>
</DIV>
<DIV><?=$PAGE_DATE->format("Y年m月d日 A h時i分s秒")?></DIV>
<HR>

<?=RMD_CONV($PAGE_TEXT)?>
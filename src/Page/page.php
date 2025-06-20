<?php
$TITLE = urldecode(str_replace("/page/", "", $REQUEST_PATH));

$PAGE = GetPageFromTitle($TITLE);

if ($PAGE != null) {
	$PAGE_ID = $PAGE["ID"];
	$DATA_ID = $PAGE["DATA_ID"];
	$PAGE_TITLE = $PAGE["TITLE"];
	$PAGE_TEXT = $PAGE["TEXT"];
	$PAGE_DATE = new DateTime($PAGE["DATE"]);
	if ($PAGE["LOCK"] == 1) {
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

	<!--編集ボタン-->
	<?php
	if (!$PAGE_LOCK) {
		?>
		<A HREF="/edit/<?=htmlspecialchars($PAGE_ID)?>">編集する</A>
		<?php
	}
	?>

	<!--履歴-->
	<A HREF="/history/<?=$PAGE_ID?>">履歴</A>
</DIV>
<DIV><?=$PAGE_DATE->format("Y年m月d日 A h時i分s秒")?></DIV>
<HR>

<?php
	$OriginalText = $PAGE_TEXT;
	$Text = RMD_CONV($OriginalText);

	//アラート
	preg_match_all("/(?i)ALERT\((.*),(?:|\s)({.*})\);/", $Text, $AlertMatch, PREG_SET_ORDER);
	foreach ($AlertMatch as $MTC) {
		if (isset($AlertTable[$MTC[1]])) {
			$AlertParam = json_decode($MTC[2], true);
			$AlertText = $AlertTable[$MTC[1]];

			foreach (array_keys($AlertParam) as $K) {
				$AlertText = str_replace("$".$K, htmlspecialchars($AlertParam[$K]), $AlertText);
			}

			$Text = str_replace($MTC[0], "<DIV CLASS=\"ALERT ALERT_".htmlspecialchars($MTC[1])."\">".$AlertText."</DIV>", $Text);
		} else {
			$Text = str_replace($MTC[0], "<FONT COLOR=\"RED\">構文エラー：ID「".htmlspecialchars($MTC[1])."」というアラートはありません。</FONT>", $Text);
		}
	}

	//テンプレート
	preg_match_all("/TEMPLATE\(\s*([A-Z_]+)\s*,\s*({.*})\s*\);/i", $OriginalText, $TemplateMatch, PREG_SET_ORDER);
	foreach ($TemplateMatch as $MTC) {
		if (isset($TemplateTable[$MTC[1]])) {
			$TemplateParam = json_decode($MTC[2], true);
			$Template = $TemplateTable[$MTC[1]];

			foreach (array_keys($TemplateParam) as $K) {
				$Template["CONTENTS"] = str_replace("$".$K, htmlspecialchars($TemplateParam[$K]), $Template["CONTENTS"]);
			}

			$Text = str_replace(htmlspecialchars($MTC[0]),
				"<DIV CLASS=\"TEMPLATE TEMPLATE_".htmlspecialchars($MTC[1])."\">".
					"<DIV CLASS=\"TITLE\">".$Template["TITLE"]."</DIV>".
					"<DIV CLASS=\"CONTENTS\">".RMD_CONV($Template["CONTENTS"])."</DIV>".
				"</DIV>"
			, $Text);
		} else {
			$Text = str_replace(htmlspecialchars($MTC[0]), "<FONT COLOR=\"RED\">構文エラー：ID「".htmlspecialchars($MTC[1])."」というテンプレートはありません。</FONT>", $Text);
		}
	}

	//Wikiリンク
	$Text = preg_replace('/HREF="wiki:\/\/([^"]+)"/iu', 'HREF="/page/$1"', $Text);

	//出典元
	preg_match_all("/\[(\d+)\]/", $Text, $SourceMatch, PREG_SET_ORDER);
	foreach ($SourceMatch as $MTC) {
		$Text = str_replace(htmlspecialchars($MTC[0]), "<A HREF=\"#SOURCE-".$MTC[1]."\">[".$MTC[1]."]</A>", $Text);
	}

	echo $Text;
?>

<HR>
出典元<BR>
<?php
$SQL_RESULT = SQL_RUN($PDO, "SELECT * FROM `PAGE_SOURCE` WHERE `DATA` = :DATA ORDER BY `INDEX` ASC;", [
	[
		"KEY" => "DATA",
		"VAL" => $DATA_ID
	]
]);
if ($SQL_RESULT["STATUS"]) {
	for ($I=0; $I < count($SQL_RESULT["RESULT"]); $I++) {
		echo "<DIV ID=\"SOURCE-".($I+1)."\">";
		echo "[".($I+1)."]:";
		echo "<A HREF=\"".htmlspecialchars($SQL_RESULT["RESULT"][$I]["URL"])."\">".htmlspecialchars($SQL_RESULT["RESULT"][$I]["URL"])."</A>";
		echo "</DIV>";
	}
} else {
	echo "エラー";
}
?>
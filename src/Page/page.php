<?php
$TITLE = urldecode(str_replace("/page/", "", $REQUEST_PATH));

$PAGE = GetPageFromTitle($TITLE);

if ($PAGE != null) {
	$PAGE_ID = $PAGE["ID"];
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
	$Text = $PAGE_TEXT;
	$Text = RMD_CONV($Text);

	//アラート
	preg_match_all("/(?i)ALERT\((.*),(?:|\s)({.*})\);/", $Text, $AlertMatch, PREG_SET_ORDER);
	foreach ($AlertMatch as $MTC) {
		if (isset($AlertTable[$MTC[1]])) {
			$AlertParam = json_decode($MTC[2], true);
			$AlertText = $AlertTable[$MTC[1]];

			foreach (array_keys($AlertParam) as $K) {
				$AlertText = str_replace($K, htmlspecialchars($AlertParam[$K]), $AlertText);
			}

			$Text = str_replace($MTC[0], "<DIV CLASS=\"ALERT ALERT_".htmlspecialchars($MTC[1])."\">".$AlertText."</DIV>", $Text);
		} else {
			$Text = str_replace($MTC[0], "<FONT COLOR=\"RED\">構文エラー：ID「".htmlspecialchars($MTC[1])."」というアラートはありません。</FONT>", $Text);
		}
	}

	echo $Text;
?>
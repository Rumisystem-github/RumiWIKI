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
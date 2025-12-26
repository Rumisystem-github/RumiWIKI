<?php
if ($login == false) {
	echo "ログインしてね。";
	return;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	?>
	<FORM ACTION="" METHOD="POST">
		<DIV class="cf-turnstile" data-sitekey="<?=$config["CFT"]["SITE_KEY"]?>" data-language="ja"></DIV>
		<INPUT TYPE="TEXT" NAME="TITLE" PLACEHOLDER="タイトル">
		<BUTTON>作る</BUTTON>
	</FORM>

	<SCRIPT SRC="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></SCRIPT>
	<?php
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
	$cft_response = $_POST["cf-turnstile-response"];
	$title = $_POST["TITLE"];
	$page_id = GenSnowFlake();
	$page_data_id = GenSnowFlake();

	if (!cft_check($cft_response)) {
		echo "え？";
		return;
	}

	$stmt = $sql->prepare("INSERT INTO `PAGE_INFO` (`ID`, `DATE`, `LOCK`) VALUES (:ID, NOW(), 0)");
	$stmt->bindValue(":ID", $page_id);
	$stmt->execute();

	$stmt = $sql->prepare("INSERT INTO `PAGE_DATA` (`ID`, `PAGE`, `DATE`, `USER`, `TITLE`, `TEXT`, `MESSAGE`) VALUES (:ID, :PAGE, NOW(), :USER, :TITLE, :TEXT, :MESSAGE)");
	$stmt->bindValue(":ID", $page_data_id);
	$stmt->bindValue(":PAGE", $page_id);
	$stmt->bindValue(":USER", $user["ID"]);
	$stmt->bindValue(":TITLE", $title);
	$stmt->bindValue(":TEXT", "# この記事にはまだ内容がありません。\nきっと記事を作った人が書いているはずです。");
	$stmt->bindValue(":MESSAGE", "First commit");
	$stmt->execute();

	header("Location: /wiki/".urlencode($title));
	exit;
}
?>
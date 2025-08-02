<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require(__DIR__."/Config.php");
require(__DIR__."/Module/AdminManager.php");
require(__DIR__."/Module/AccountManager.php");
require(__DIR__."/Module/AlertManager.php");
require(__DIR__."/Module/TemplateManager.php");
require(__DIR__."/Module/GetPage.php");
require(__DIR__."/Module/CFT.php");

//↓自分が所有しているCDNなので、httpで繋がない限り心配はない
require("https://cdn.rumia.me/LIB/SQL.php?V=LATEST");
require("https://cdn.rumia.me/LIB/RMD.php?V=LATEST");
require("https://cdn.rumia.me/LIB/OGP.php?V=LATEST");
include("https://cdn.rumia.me/LIB/SnowFlake.php?V=LATEST");

$REQUEST_PATH = explode("?", str_replace($CONFIG["PAGE"]["PATH"], "/", $_SERVER["REQUEST_URI"]))[0];

//SQL接続
try{
	$PDO = new PDO(
		"mysql:host=".$CONFIG["SQL"]["HOST"].";dbname=".$CONFIG["SQL"]["DB"].";",
		$CONFIG["SQL"]["USER"],
		$CONFIG["SQL"]["PASS"],
		//レコード列名をキーとして取得させる
		[PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
	);
}catch (PDOException $e){
	echo "データベースにアクセスできません！";
	exit;
}

//ログイン
$LOGIN_OK = false;
if (isset($_COOKIE["SESSION"])) {
	$SessionData = json_decode($_COOKIE["SESSION"], true);

	if ($SessionData["TYPE"] == "RSV") {
		$Login = RSVLogin($SessionData["TOKEN"]);
		if ($Login != null) {
			$LOGIN_OK = true;
			$ACCOUNT = $Login;
		}
	}
}

//HTMLがとびだせどうぶつの森するとまずいやつをここに
if ($REQUEST_PATH == "/edit_done") {
	require(__DIR__."/Page/Edit/Done.php");
	exit;
}

$AlertTable = GetAlert();
$TemplateTable = GetTemplate();
?>
<!DOCTYPE html>
<HTML>
	<HEAD>
		<TITLE>るみWIKi</TITLE>

		<LINK REL="stylesheet" HREF="https://cdn.rumia.me/CSS/reset.css">
		<LINK REL="stylesheet" HREF="https://cdn.rumia.me/CSS/DEFAULT.css">
		<LINK REL="stylesheet" HREF="https://cdn.rumia.me/CSS/font.css">

		<LINK REL="stylesheet" HREF="/STYLE/Main.css">
		<LINK REL="stylesheet" HREF="/STYLE/Page.css">
		<LINK REL="stylesheet" HREF="/STYLE/Editor.css">
	</HEAD>
	<BODY>
		<DIV CLASS="HEADER">
			<H1 CLASS="TITLE"><A HREF="/"><?=$CONFIG["PAGE"]["NAME"]?></A></H1>

			<?php
			if ($LOGIN_OK) {
				//ログイン済み
				?>
					<?=htmlspecialchars($ACCOUNT["NAME"])?>
					<A HREF="/create">寄稿する</A>
				<?php
			} else {
				//ログインしてない
				?> <A HREF="/login">ログイン</A> <?php
			}
			?>
		</DIV>
		<DIV CLASS="CONTENTS">
			<?php
			if ($REQUEST_PATH == "/") {
				require(__DIR__."/Page/home.php");
			} elseif (str_starts_with($REQUEST_PATH, "/page/")) {
				require(__DIR__."/Page/page.php");
			} elseif (str_starts_with($REQUEST_PATH, "/history/")) {
				require(__DIR__."/Page/history.php");
			} else {
				if ($LOGIN_OK) {
					if ($REQUEST_PATH == "/create") {
						require(__DIR__."/Page/Create/Create.php");
					} elseif ($REQUEST_PATH == "/create_done") {
						require(__DIR__."/Page/Create/Done.php");
					} elseif (str_starts_with($REQUEST_PATH, "/edit/")) {
						require(__DIR__."/Page/Edit/edit.php");
					}
				} else {
					//ログインしてない場合用
					if ($REQUEST_PATH == "/login") {
						require(__DIR__."/Page/Login/login.php");
					} elseif ($REQUEST_PATH == "/login_done") {
						require(__DIR__."/Page/Login/RSWLogin.php");
					}
				}
			}
			?>
		</DIV>
	</BODY>
</HTML>
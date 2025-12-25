<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$config = parse_ini_file(__DIR__."/../Config.ini", true, INI_SCANNER_TYPED);
if ($config == false) {
	exit;
}

//↓自分が所有しているCDNなので、httpで繋がない限り心配はない
require("https://cdn.rumia.me/LIB/SQL.php?V=LATEST");
require("https://cdn.rumia.me/LIB/OGP.php?V=LATEST");
include("https://cdn.rumia.me/LIB/SnowFlake.php?V=LATEST");
include("https://cdn.rumia.me/LIB/RMDParser.php?V=LATEST");

try {
	$sql = new PDO(
		"mysql:host=".$config["SQL"]["HOST"].";dbname=".$config["SQL"]["DB"].";",
		$config["SQL"]["USER"],
		$config["SQL"]["PASS"],
		//レコード列名をキーとして取得させる
		[PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
	);
} catch (PDOException $e) {
	require(__DIR__."/Page/Error/SQLError.html");
	exit;
}

$path = explode("?", str_replace($config["SITE"]["BASE_PATH"], "/", $_SERVER["REQUEST_URI"]))[0];
$include_path = "/Error/404.html";

if ($path == "/") {
	$include_path = "/Top.php";
} else if (str_starts_with($path, "/page/")) {
	$include_path = "/View.php";
}

$ogp = [
	"TITLE" => "あ",
	"DESCRIPTION" => "ううう"
];

ob_start();
require(__DIR__."/Page".$include_path);
$contents = ob_get_contents();
ob_end_clean();
?>
<!DOCTYPE html>
<HTML>
	<HEAD>
		<TITLE><?=htmlspecialchars($config["SITE"]["TITLE"])?> | <?=htmlspecialchars($ogp["TITLE"])?></TITLE>

		<LINK REL="stylesheet" HREF="https://cdn.rumia.me/CSS/font.css">
		<LINK REL="stylesheet" HREF="https://cdn.rumia.me/CSS/reset.css">
		<LINK REL="stylesheet" HREF="https://cdn.rumia.me/CSS/DEFAULT.css">
		<LINK REL="stylesheet" HREF="https://cdn.rumia.me/CSS/icon.css">

		<LINK REL="stylesheet" HREF="<?=htmlspecialchars($config["SITE"]["URL"])?>/Style/Main.css">
		<LINK REL="stylesheet" HREF="<?=htmlspecialchars($config["SITE"]["URL"])?>/Style/View.css">

		<META PROPERTY="og:type" CONTENT="website" />
		<META PROPERTY="og:url" CONTENT="<?=htmlspecialchars($config["SITE"]["URL"])?>" />
		<META PROPERTY="og:title" CONTENT="<?=htmlspecialchars($ogp["TITLE"])?>" />
		<META PROPERTY="og:description" CONTENT="<?=htmlspecialchars($ogp["DESCRIPTION"])?>" />
		<META PROPERTY="og:site_name" CONTENT="<?=htmlspecialchars($config["SITE"]["TITLE"])?>" />
		<!--<META PROPERTY="og:image" CONTENT="https://rumiserver.com/Asset/2023-10-21_1.53.45_.png" />-->
		<META name="twitter:card" content="summary_large_image" />

		<META name="description" content="<?=htmlspecialchars($ogp["DESCRIPTION"])?>">
	</HEAD>
	<BODY>
		<?php require(__DIR__."/Component/Header.php"); ?>
		<DIV CLASS="CONTENTS">
			<?=$contents?>
		</DIV>
	</BODY>
</HTML>
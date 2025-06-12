<?php
$CONFIG = parse_ini_file(__DIR__."/../Config.ini", true, INI_SCANNER_TYPED);

if ($CONFIG == false) {
	echo "設定ファイルエラー";
	header("Content-Type: plain/text;charset=UTF-8");
	exit;
}
?>
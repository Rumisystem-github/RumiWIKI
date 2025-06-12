<?php
$ID = urldecode(str_replace("/edit/", "", $REQUEST_PATH));
$PAGE = GetPageFromID($ID);

if ($PAGE != null) {
	if ($PAGE["LOCK"] == 0) {
		require(__DIR__."/editor.php");
	} else {
		echo "ロックされています";
	}
} else {
	echo "記事が無い";
}
?>
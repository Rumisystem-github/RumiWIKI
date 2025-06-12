<?php
$ID = urldecode(str_replace("/edit/", "", $REQUEST_PATH));
$PAGE = GetPageFromID($ID);

if ($PAGE != null) {
	require(__DIR__."/editor.php");
} else {
	echo "記事が無い";
}
?>
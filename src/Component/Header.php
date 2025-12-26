<DIV CLASS="HEADER">
	<A HREF="/"><H1><?=htmlspecialchars($config["SITE"]["TITLE"])?></H1></A>
	<?php
	if ($login) {
		?> <?=htmlspecialchars($user["NAME"])?>さんようこそ <?php
	} else {
		?> <A HREF="/login">ログイン</A> <?php
	}
	?>
</DIV>
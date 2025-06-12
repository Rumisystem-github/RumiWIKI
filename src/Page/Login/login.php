<?php
header("Location: https://account.rumiserver.com/Auth?".
	"ID=".$CONFIG["DEPENDENCY"]["ACCOUNT_RSW_ID"].
	"&SESSION=".urlencode(uniqid()).
	"&PERMISSION=account:read&CALLBACK=".
	urlencode($CONFIG["PAGE"]["URL"]."login_done")
);
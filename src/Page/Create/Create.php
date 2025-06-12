<!--
	記事の作成をするフォーム
-->

<FORM ACTION="create_done" METHOD="POST">
	<INPUT TYPE="TEXT" NAME="TITLE" PLACEHOLDER="ページタイトル" required>

	<DIV class="cf-turnstile" data-sitekey="<?=$CONFIG["CFT"]["SITE_KEY"]?>" data-language="ja"></DIV>

	<BUTTON>作成</BUTTON>
</FORM>

<SCRIPT SRC="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></SCRIPT>
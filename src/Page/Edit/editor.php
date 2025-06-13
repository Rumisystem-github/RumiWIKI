<H1>編集</H1>
<HR>

<DIV CLASS="TOAST_FIELD" ID="TOAST_FIELD"></DIV>

<TEXTAREA CLASS="EDITOR_TEXTAREA" ID="EDITOR_TEXTAREA"></TEXTAREA>

<DIV CLASS="EDIT_APPLY_FIELD">
	<INPUT TYPE="TEXT" ID="COMMIT_MESSAGE">
	<DIV class="cf-turnstile" data-sitekey="<?=$CONFIG["CFT"]["SITE_KEY"]?>" data-callback="CFT_OK" data-language="ja"></DIV>
	<BUTTON ID="APPLY_BTN" onclick="Apply();" disabled>変更を反映する</BUTTON>
</DIV>

<SCRIPT SRC="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></SCRIPT>
<SCRIPT>
	const ID = "<?=htmlspecialchars($ID)?>";
	const Contents = `<?=str_replace("`", "\\`", $PAGE["TEXT"])?>`;
	const SaveKey = "EDIT-" + ID;
	const ToastType = {
		"INFO": 0
	};

	let CFT_RESULT = null;
	let EL = {
		EDITOR_TEXTAREA: document.getElementById("EDITOR_TEXTAREA"),
		TOAST_FIELD: document.getElementById("TOAST_FIELD"),
		COMMIT_MESSAGE: document.getElementById("COMMIT_MESSAGE"),
		APPLY_BTN: document.getElementById("APPLY_BTN")
	};

	window.addEventListener("load", (e)=>{
		if (localStorage.getItem(SaveKey) != null) {
			//保存されていた進捗を表示
			EL.EDITOR_TEXTAREA.value = localStorage.getItem(SaveKey);
		} else {
			//PHPからゲットした記事データを表示
			EL.EDITOR_TEXTAREA.value = Contents;
		}
	});

	window.addEventListener("keydown", (e)=>{
		if (e.ctrlKey && e.key === "s") {
			e.preventDefault();
			localStorage.setItem(SaveKey, EL.EDITOR_TEXTAREA.value);
			Toast(ToastType.INFO, "進捗を保存しました");
		}
	});

	//トーストを出すぜ
	function Toast(Type, Text) {
		const ToastID = crypto.randomUUID();
		const Now = new Date();
		const HTMLCode = `
			<DIV CLASS="TOAST_ITEM" data-id="${ToastID}">
				<DIV>${Text}</DIV>
				<DIV>${Now.getHours()}:${Now.getMinutes()}.${Now.getSeconds()}</DIV>
			</DIV>
		`;

		const Parser = new DOMParser();
		const Document = Parser.parseFromString(HTMLCode, "text/html");
		const ToastElement = Document.body.firstChild;

		EL.TOAST_FIELD.appendChild(ToastElement);

		//時間経過で消す用
		setTimeout(() => {
			document.querySelector(`.TOAST_ITEM[data-id="${ToastID}"]`).remove();
		}, 1000);
	}

	window.CFT_OK = function(RESULT) {
		CFT_RESULT = RESULT;
		EL.APPLY_BTN.removeAttribute("disabled");
	}

	async function Apply() {
		//↓PHPでJSONを解析するのがだるいのでフォームデータをぶん投げる
		let FD = new FormData();
		FD.append("ID", ID);
		FD.append("TITLE", "");
		FD.append("TEXT", EL.EDITOR_TEXTAREA.value);
		FD.append("MESSAGE", EL.COMMIT_MESSAGE.value);
		FD.append("CFT", CFT_RESULT);

		let Ajax = await fetch("/edit_done", {
			method: "POST",
			body: FD
		});
		const RESULT = await Ajax.json();

		if (RESULT.STATUS) {
			//保存を削除
			localStorage.removeItem(SaveKey);

			window.location.href = "/page/<?=htmlspecialchars($PAGE["TITLE"])?>";
		} else {
			alert("失敗した！");
		}
	}
</SCRIPT>
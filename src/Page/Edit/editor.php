<H1>編集</H1>
<HR>

<DIV CLASS="TOAST_FIELD" ID="TOAST_FIELD"></DIV>

<TEXTAREA CLASS="EDITOR_TEXTAREA" ID="EDITOR_TEXTAREA"></TEXTAREA>

<DIV>
	<BUTTON onclick="addSource();">+</BUTTON>
	<TABLE ID="SOURCE_LIST" BORDER="1">
		<TR>
			<TH></TH>
			<TH>番号</TH>
			<TH>URL</TH>
			<TH>アーカイブURL</TH>
		</TR>
		<?php
		$SQL_RESULT = SQL_RUN($PDO, "SELECT * FROM `PAGE_SOURCE` WHERE `DATA` = :DATA;", [
			[
				"KEY" => "DATA",
				"VAL" => $PAGE["DATA_ID"]
			]
		]);
		if ($SQL_RESULT["STATUS"]) {
			for ($I=0; $I < count($SQL_RESULT["RESULT"]); $I++) {
				?>
				<TR>
					<TD><BUTTON onclick="this.parentElement.parentElement.remove();">X</BUTTON></TD>
					<TD data-type="INDEX"><?=$I+1?></TD>
					<TD data-type="URL"><?=$SQL_RESULT["RESULT"][$I]["URL"]?></TD>
					<?php
					if (isset($SQL_RESULT["RESULT"][$I]["ARFCHIVE_URL"])) {
						?> <TD data-type="ARCHIVE_URL"><?=$SQL_RESULT["RESULT"][$I]["ARFCHIVE_URL"]?></TD> <?php
					} else {
						?> <TD data-type="ARCHIVE_URL"></TD> <?php
					}
					?>
				</TR>
				<?php
			}
		}
		?>
	</TABLE>
</DIV>

<DIV CLASS="EDIT_APPLY_FIELD">
	<INPUT TYPE="TEXT" ID="COMMIT_MESSAGE">
	<DIV class="cf-turnstile" data-sitekey="<?=$CONFIG["CFT"]["SITE_KEY"]?>" data-callback="CFT_OK" data-language="ja"></DIV>
	<BUTTON ID="APPLY_BTN" onclick="Apply();" disabled>変更を反映する</BUTTON>
</DIV>

<SCRIPT SRC="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></SCRIPT>
<SCRIPT SRC="https://cdn.rumia.me/LIB/DIALOG.js?V=LATEST" async defer></SCRIPT>
<SCRIPT>
	const ID = "<?=htmlspecialchars($ID)?>";
	const Contents = DecodeBase64("<?=base64_encode($PAGE["TEXT"])?>");
	const SaveKey = "EDIT-" + ID;
	const ToastType = {
		"INFO": 0
	};

	let CFT_RESULT = null;
	let EL = {
		EDITOR_TEXTAREA: document.getElementById("EDITOR_TEXTAREA"),
		TOAST_FIELD: document.getElementById("TOAST_FIELD"),
		COMMIT_MESSAGE: document.getElementById("COMMIT_MESSAGE"),
		APPLY_BTN: document.getElementById("APPLY_BTN"),
		SOURCE_LIST: document.getElementById("SOURCE_LIST")
	};

	window.addEventListener("load", (e)=>{
		if (localStorage.getItem(SaveKey) != null) {
			const SaveData = JSON.parse(localStorage.getItem(SaveKey));

			//保存されていた進捗を表示
			EL.EDITOR_TEXTAREA.value = SaveData.CONTENTS;

			let TBody = EL.SOURCE_LIST.querySelector("tbody");
			for (let I = 0; I < Object.keys(SaveData.SOURCE).length; I++) {
				const Index = Object.keys(SaveData.SOURCE)[I];
				const URL = SaveData.SOURCE[Index];
				TBody.innerHTML += `
					<TR>
						<TD><BUTTON onclick="this.parentElement.parentElement.remove();">X</BUTTON></TD>
						<TD data-type="INDEX">${Index}</TD>
						<TD data-type="URL">${URL}</TD>
					</TR>
				`;
			}
		} else {
			//PHPからゲットした記事データを表示
			EL.EDITOR_TEXTAREA.value = Contents;
		}
	});

	window.addEventListener("keydown", (e)=>{
		if (e.ctrlKey && e.key === "s") {
			e.preventDefault();
			localStorage.setItem(SaveKey, JSON.stringify({
				"CONTENTS": EL.EDITOR_TEXTAREA.value,
				"SOURCE": ParseSourceList()
			}));
			Toast(ToastType.INFO, "進捗を保存しました");
		}
	});

	function DecodeBase64(Base64) {
		const DecodeUTF8 = atob(Base64);
		const DecodeArray = new Uint8Array(Array.prototype.map.call(DecodeUTF8, C=>C.charCodeAt()));
		const Decode = new TextDecoder().decode(DecodeArray);
		return Decode;
	}

	async function addSource() {
		const Req = await new DIALOG_SYSTEM().INPUT("出典元を記載してください", {TYPE:"TEXT", "NAME":"URL"});
		if (Req == null) return;

		let TBody = EL.SOURCE_LIST.querySelector("tbody");
		const Index = TBody.querySelectorAll("tr").length;

		TBody.innerHTML += `
			<TR>
				<TD><BUTTON onclick="this.parentElement.parentElement.remove();">X</BUTTON></TD>
				<TD data-type="INDEX">${Index}</TD>
				<TD data-type="URL">${Req}</TD>
			</TR>
		`;
	}

	function ParseSourceList() {
		const SourceListTBody = EL.SOURCE_LIST.querySelector("tbody");
		const SourceListEL = SourceListTBody.querySelectorAll("tr");
		let SourceList = {};
		for (let I = 0; I < SourceListEL.length; I++) {
			if (SourceListEL[I].querySelector("th") != null) continue;
			const Index = SourceListEL[I].querySelector("td[data-type=\"INDEX\"]").innerHTML;
			const URL = SourceListEL[I].querySelector("td[data-type=\"URL\"]").innerHTML;
			SourceList[Index] = URL;
		}
		return SourceList;
	}

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
		const SourceList = ParseSourceList();

		//↓PHPでJSONを解析するのがだるいのでフォームデータをぶん投げる
		let FD = new FormData();
		FD.append("ID", ID);
		FD.append("TITLE", "");
		FD.append("TEXT", EL.EDITOR_TEXTAREA.value);
		FD.append("MESSAGE", EL.COMMIT_MESSAGE.value);
		FD.append("SOURCE", JSON.stringify(SourceList));
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
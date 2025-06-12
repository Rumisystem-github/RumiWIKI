<H1>編集</H1>
<HR>

<DIV CLASS="TOAST_FIELD" ID="TOAST_FIELD"></DIV>

<TEXTAREA CLASS="EDITOR_TEXTAREA" ID="EDITOR_TEXTAREA"></TEXTAREA>

<SCRIPT>
	const ID = "<?=htmlspecialchars($ID)?>";
	const Contents = `<?=str_replace("`", "\\`", $PAGE["TEXT"])?>`;
	const SaveKey = "EDIT-" + ID;
	const ToastType = {
		"INFO": 0
	};

	let EL = {
		EDITOR_TEXTAREA: document.getElementById("EDITOR_TEXTAREA"),
		TOAST_FIELD: document.getElementById("TOAST_FIELD")
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
</SCRIPT>
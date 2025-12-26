<?php
if ($login == false) {
	echo "ログインしてね。";
	return;
}

//データ取得
$stmt = $sql->prepare("
	SELECT
		d.*
	FROM
		`PAGE_DATA` AS d
	WHERE
		d.PAGE = :PAGE
	ORDER BY
		d.DATE DESC
	LIMIT
		1;
");
$stmt->bindValue(":PAGE", $_GET["ID"], PDO::PARAM_STR);
$stmt->execute();
$page = $stmt->fetch();

$stmt = $sql->prepare("SELECT `INDEX`, `URL`, `ARCHIVE_URL`, `COMMENT` FROM `PAGE_SOURCE` WHERE `DATA` = :DATA ORDER BY `INDEX` ASC;");
$stmt->bindValue(":DATA", $page["ID"], PDO::PARAM_STR);
$stmt->execute();
$source_list = $stmt->fetchAll();
?>
<STYLE>
	:root{
		--controle_height: 200px;
	}

	.EDITOR{
		display: flex;
		flex-direction: row;
	}
	
	.EDITOR > *{
		width: 100%;
		height: 100%;

		min-height: calc(100vh - var(--header_height) - 20px - var(--controle_height));
	}

	.CONTROLE{
		width: 100%;
		height: var(--controle_height);

		padding: 10px;

		display: flex;
		flex-direction: row;
	}

	.CONTROLE > .SOURCE_LIST{
		height: 100%;

		overflow: auto;
	}

	.COMMIT_FORM{
		position: fixed;
		top: 100px;
		left: 100px;

		/*仮*/
		width: 50vw;
		height: 50vh;
	}
</STYLE>

<DIV CLASS="EDITOR">
	<TEXTAREA ID="EDITOR_TEXT_INPUT"><?=htmlspecialchars($page["TEXT"])?></TEXTAREA>
	<DIV>//TODO:ビュワーを作る</DIV>
</DIV>

<DIV CLASS="CONTROLE">
	<DIV CLASS="SOURCE_LIST">
		<BUTTON onclick="add_source();">+</BUTTON>
		<TABLE ID="SOURCE_LIST">
			<?php
			foreach ($source_list as $source) {
				?>
				<TR>
					<TD><?=$source["INDEX"]?></TD>
					<TD><INPUT PLACEHOLDER="URL" VALUE="<?php if (isset($source["URL"])) {echo htmlspecialchars($source["URL"]);}?>"></TD>
					<TD><INPUT PLACEHOLDER="アーカイブURL(任意)" VALUE="<?php if (isset($source["ARCHIVE_URL"])) {echo htmlspecialchars($source["ARCHIVE_URL"]);}?>"></TD>
					<TD><INPUT PLACEHOLDER="コメント(任意)" VALUE="<?php if (isset($source["COMMENT"])) {echo htmlspecialchars($source["COMMENT"]);}?>"></TD>
				</TR>
				<?php
			}
			?>
		</TABLE>
	</DIV>

	<DIV>
		<BUTTON onclick="open_commit_form();">コミット</BUTTON>
	</DIV>
</DIV>

<DIV CLASS="COMMIT_FORM" ID="COMMIT_FORM" STYLE="display: none;">
	<TEXTAREA ID="COMMIT_FORM_MESSAGE" PLACEHOLDER="コミットメッセージ"></TEXTAREA>
	<BUTTON onclick="commit();">コミット</BUTTON>
</DIV>

<SCRIPT defer>
	let mel = {
		source_list: document.getElementById("SOURCE_LIST").querySelector("TBODY"),
		editor: {
			text_input:document.getElementById("EDITOR_TEXT_INPUT")
		},
		commit_form: {
			parent: document.getElementById("COMMIT_FORM"),
			message: document.getElementById("COMMIT_FORM_MESSAGE")
		}
	};

	function add_source() {
		let tr = document.createElement("TR");
		mel.source_list.append(tr);

		let index_td = document.createElement("TD");
		index_td.innerText = mel.source_list.querySelectorAll("TR").length;
		tr.append(index_td);

		let url_td = document.createElement("TD");
		url_td.append(document.createElement("INPUT"));
		tr.append(url_td);

		let archive_url_td = document.createElement("TD");
		archive_url_td.append(document.createElement("INPUT"));
		tr.append(archive_url_td);

		let comment_td = document.createElement("TD");
		comment_td.append(document.createElement("INPUT"));
		tr.append(comment_td);
	}

	function open_commit_form() {
		mel.commit_form.parent.style.display = "block";
	}

	async function commit() {
		let source_list = [];
		let page_data = {
			"ID": "<?=htmlspecialchars($page["PAGE"])?>",
			"TITLE": "<?=htmlspecialchars($page["TITLE"])?>",
			"TEXT": mel.editor.text_input.value
		};
		let commit_data = {
			"MESSAGE": mel.commit_form.message.value
		};

		//出典元
		let index = 1;
		for (const tr of mel.source_list.querySelectorAll("TR")) {
			const url = tr.querySelectorAll("TD")[1].querySelector("INPUT").value;
			const archive_url = tr.querySelectorAll("TD")[2].querySelector("INPUT").value;
			const comment = tr.querySelectorAll("TD")[3].querySelector("INPUT").value;
			if (url == "") return;

			source_list.push(
				{
					INDEX: index,
					URL: url,
					ARCHIVE_URL: archive_url,
					COMMENT: comment
				}
			);

			index += 1;
		}

		let ajax = await fetch("/commit", {
			method: "POST",
			body: JSON.stringify({
				"COMMIT":commit_data,
				"PAGE": page_data,
				"SOURCE":source_list
			})
		});
		const result = await ajax.json();
		console.log(result);
	}
</SCRIPT>
<?php
$page = null;

if (isset($_GET["DATA"])) {
	//ページデータのIDから取得
	$stmt = $sql->prepare("
		SELECT
			d.*
		FROM
			`PAGE_DATA` AS d
		WHERE
			d.ID = :ID;
	");
	$stmt->bindValue(":ID", $_GET["DATA"], PDO::PARAM_STR);
	$stmt->execute();
	$page = $stmt->fetch();
} else {
	//ページのタイトルからデータを取得
	$page_title = urldecode(str_replace("/wiki/", "", $path));
	$stmt = $sql->prepare("
		SELECT
			d.*
		FROM
			`PAGE_DATA` AS d
		WHERE
			d.TITLE = :TITLE
		ORDER BY
			d.DATE DESC
		LIMIT
			1;
	");
	$stmt->bindValue(":TITLE", $page_title, PDO::PARAM_STR);
	$stmt->execute();
	$page = $stmt->fetch();
}

//データから出典を取得
$stmt = $sql->prepare("SELECT * FROM `PAGE_SOURCE` WHERE `DATA` = :DATA ORDER BY `INDEX` ASC;");
$stmt->bindValue(":DATA", $page["ID"]);
$stmt->execute();
$source_list = $stmt->fetchAll();

//OGP
$ogp["TITLE"] = $page["TITLE"];
$ogp["DESCRIPTION"] = "記事：".$page["TITLE"];

//目次
$toc = [];

//データ内容をパース
$rmd = new RMD($page["TEXT"]);
$html = "";
foreach ($rmd->get_struct() as $row) {
	switch ($row->type) {
		case StructType::Char:
		case StructType::Text:
			$html .= htmlspecialchars($row->Contents);
			break;
		case StructType::NextLine:
			$html .= "<BR>";
			break;
		case StructType::HR:
			$html .= "<HR>";
			break;
		case StructType::Header1:
			$html .= "<H1 ID=\"".htmlspecialchars($row->Contents)."\">".htmlspecialchars($row->Contents)."</H1>";
			$toc[] = [
				"TITLE" => $row->Contents
			];
			break;
		case StructType::Header2:
			$html .= "<H2>".htmlspecialchars($row->Contents)."</H2>";
			break;
		case StructType::Header3:
			$html .= "<H3>".htmlspecialchars($row->Contents)."</H3>";
			break;
		case StructType::Strong:
			$html .= "<STRONG>".htmlspecialchars($row->Contents)."</STRONG>";
			break;
		case StructType::Italic:
			$html .= "<I>".htmlspecialchars($row->Contents)."</I>";
			break;
		case StructType::Strike:
			$html .= "<S>".htmlspecialchars($row->Contents)."</S>";
			break;
		case StructType::URL:
			$html .= "<A HREF=\"".htmlspecialchars($row->URL)."\" TARGET=\"_blank\">".htmlspecialchars($row->Title)."</A>";
			break;
		case StructType::Image:
			$html .= "<IMG SRC=\"".htmlspecialchars($row->URL)."\" ALT=\"".htmlspecialchars($row->Title)."\" TITLE=\"".htmlspecialchars($row->Title)."\">";
			break;
		case StructType::Video:
			$html .= "<VIDEO SRC=\"".htmlspecialchars($row->URL)."\" ALT=\"".htmlspecialchars($row->Title)."\" controls></VIDEO>";
			break;
		case StructType::CodeBlock:
			$html .= "<DIV CLASS=\"RMD_CODE_BLOCK\">".htmlspecialchars($row->Contents)."</DIV>";
			break;
		case StructType::InlineCode:
			$html .= "<SPAN CLASS=\"RMD_INLINE_CODE\">".htmlspecialchars($row->Contents)."</SPAN>";
			break;
		case StructType::Quote:
			$html .= "<BLOCKQUOTE>".nl2br(htmlspecialchars($row->Contents))."</BLOCKQUOTE>";
			break;
		case StructType::Alert:
			$html .= "<DIV CLASS=\"RMD_".$row->mode."\">".nl2br(htmlspecialchars($row->Contents))."</DIV>";
			break;
	}
}

//アラートを解析
$alert_list = [];
preg_match_all("/ALERT\(([A-Za-z_]+),(\{.*?\})\);/", $html, $alert_match, PREG_SET_ORDER);
foreach ($alert_match as $m) {
	$id = $m[1];
	if (!isset($alert_list[$id])) {
		$stmt = $sql->prepare("SELECT * FROM `ALERT` WHERE `ID` = :ID;");
		$stmt->bindValue(":ID", $id);
		$stmt->execute();
		$alert = $stmt->fetch();
		if ($alert) {
			$alert_list[$id] = $alert;
		}
	}
}

foreach ($alert_match as $m) {
	$full = $m[0];
	$id = $m[1];
	$args = json_decode($m[2], true);

	if (isset($alert_list[$id])) {
		$text = $alert_list[$id]["TEXT"];
		$html = str_replace($full, "<DIV CLASS=\"ALERT\" data-id=\"".htmlspecialchars($id)."\">".new RMD($text)->to_html()."</DIV>", $html);
	} else {
		$html = str_replace($full, "構文エラー", $html);
	}
}

//テンプレート
$template_list = [];
preg_match_all("/TEMPLATE\(([A-Za-z_]+),(\{.*?\})\);/", $html, $template_match, PREG_SET_ORDER);
foreach ($template_match as $m) {
	$id = $m[1];
	if (!isset($alert_list[$id])) {
		$stmt = $sql->prepare("SELECT * FROM `TEMPLATE` WHERE `ID` = :ID;");
		$stmt->bindValue(":ID", $id);
		$stmt->execute();
		$template = $stmt->fetch();
		if ($template) {
			$template_list[$id] = $template;
		}
	}
}

foreach ($template_match as $m) {
	$full = $m[0];
	$id = $m[1];
	$args = json_decode(htmlspecialchars_decode($m[2]), true);

	if (isset($template_list[$id])) {
		$contents = $template_list[$id]["CONTENTS"];
		foreach (array_keys($args) as $key) {
			$contents = str_replace("$".$key, $args[$key], $contents);
		}

		$html = str_replace($full, "<DIV CLASS=\"TEMPLATE\">".
			"<DIV CLASS=\"TEMPLATE_TITLE\">".htmlspecialchars($template_list[$id]["TITLE"])."</DIV>".
			"<DIV CLASS=\"TEMPLATE_CONTENTS\">".
				new RMD($contents)->to_html().
			"</DIV>".
		"</DIV>", $html);
	} else {
		$html = str_replace($full, "構文エラー", $html);
	}
}
?>

<DIV CLASS="WIKI_PAGE">
	<DIV CLASS="TOC">
		<H2>目次</H2>
		<?php
		foreach ($toc as $t) {
			?>
			<DIV>
				<A HREF="#<?=htmlspecialchars($t["TITLE"])?>"><?=htmlspecialchars($t["TITLE"])?></A>
			</DIV>
			<?php
		}
		?>
	</DIV>
	<DIV CLASS="PAGE_CONTENTS">
		<H1><?=htmlspecialchars($page["TITLE"])?></H1>
		<A HREF="/edit?ID=<?=$page["PAGE"]?>">編集</A>
		<A HREF="/istoria?ID=<?=$page["PAGE"]?>">履歴</A>
		<HR>

		<?php
		if (isset($_GET["DATA"])) {
			?>
			<DIV CLASS="ALERT">
				現在古い記事を表示しています。
			</DIV>
			<?php
		}
		?>

		<DIV CLASS="TEXT">
			<?=$html?>
		</DIV>

		<HR>

		<DIV>
			出典元<BR>
			<?php
			$i = 1;
			foreach ($source_list as $source) {
				?>
				<DIV>
					[<?=$i?>]
					<A HREF="<?=$source["URL"]?>"><?=$source["URL"]?></A>
					<?php
					if (isset($source["ARCHIVE_URL"])) {
						?><A HREF="<?=$source["URL"]?>">アーカイブ</A><?php
					}
					?>
				</DIV>
				<?php
				$i += 1;
			}
			?>
		</DIV>
	</DIV>
</DIV>
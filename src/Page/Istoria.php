<?php
$stmt = $sql->prepare("
SELECT
	d.ID,
	d.USER,
	d.MESSAGE,
	LENGTH(d.TEXT) AS `LENGTH`
FROM
	`PAGE_DATA` AS d
WHERE
	d.PAGE = :ID
ORDER BY
	d.DATE DESC;
");
$stmt->bindValue(":ID", $_GET["ID"]);
$stmt->execute();
$result = $stmt->fetchAll();

foreach ($result as $row) {
	echo "<PRE>";
	var_dump($row);
	echo "</PRE>";
	echo "<A HREF=\"/wiki/?DATA=".$row["ID"]."\">閲覧する</A>";
}
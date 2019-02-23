<?php
// members.php
	require_once 'dbconnect.php';
	$results_per_page = 200; // number of results per page
?>

<?php
if (isset($_GET["page"])) { $page = $_GET["page"]; } else { $page=1; };
	$start_from = ($page-1) * $results_per_page;

	$sql = "SELECT * FROM db_members ORDER BY ID ASC LIMIT $start_from, ".$results_per_page;
	$result = mysqli_query($conn, $sql);
?>
<table border="1" cellpadding="4">
	<tr>
	    <td bgcolor="#CCCCCC"><strong>ID</strong></td>
	    <td bgcolor="#CCCCCC"><strong>Line ID</strong></td>
	    <td bgcolor="#CCCCCC"><strong>Display Name</strong></td>
	    <td bgcolor="#CCCCCC"><strong>Photo</strong></td>
	    <td bgcolor="#CCCCCC"><strong>Status</strong></td>
	</tr>
<?php 
 while($row = mysqli_fetch_assoc($result)) {
?> 
	<tr>
		<td><?php echo $row["id"]; ?></td>
		<td><?php echo $row["id_line"]; ?></td>
		<td><?php echo $row["line_displayName"]; ?></td>
		<td><a href="<?php echo $row["line_pictureUrl"]; ?>" target="_bank"><img src="<?php echo $row["line_pictureUrl"]; ?>" style="width: 150px;"/></a></td>
		<td><?php echo $row["line_statusMessage"]; ?></td>
	</tr>
<?php }; ?> 
</table>


 
<?php 
$sql = "SELECT COUNT(id) AS total FROM db_members";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_pages = ceil($row["total"] / $results_per_page); // calculate total pages with results
  
for ($i=1; $i<=$total_pages; $i++) {  // print links for all pages
	echo "<a href='members.php?page=".$i."'";
	if ($i==$page)  echo " class='curPage'";
	echo ">".$i."</a> "; 
}; 
?>
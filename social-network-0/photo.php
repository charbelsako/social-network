<?php 

$username; 
$photo;
$src;
?>
<?php include_once("php_includes/check_login_status.php"); 
//if user has not logged in header them to log in
if($user_ok == false){
	header("location: login.php");
}
include_once("php_includes/db_conx.php");
?>
<?php
//takes a usename and a photo name and displays it on the page
$username = $_GET["user"];
$photo = $_GET["photo"];
$extension = substr($photo , -4 , strlen($photo) );
if( $extension != ".jpg" ){
	$photo .= '.jpg';
}
$src = 'user/'.$username.'/'.$photo.'';

$num_likes = mysqli_query($db_conx,"SELECT likes FROM photos WHERE filename='$photo' AND user='$username' LIMIT 1");
while( $row = mysqli_fetch_assoc($num_likes) ){
	$likes = $row["likes"];
}

/* testing code
if($num_likes){
	echo "good";
}else{
	echo mysqli_error($db_conx);
}
*/

?>


<!DOCTYPE html>
<html>
<head> 
<title>PHOTO VIEW FROM NOTIFICATIONS</title>
<link rel="stylesheet" href="style/style.css" type="text/css" />
<style>

table td{
	padding: 10px;
}

</style>
</head>
<body>
<?php include_once("template_pageTop.php"); ?>
<div id="pageMiddle">
<center>
<table border="1" width="800"> 
	<tr>
		<td colspan="2">
			<img src="<?php echo $src; ?>" style="height:500px; width:500px;"/> 
		</td>
        <td rowspan="2" width="35%"> 
			display all the comments here
		</td>
	</tr>
	<tr>
		<td width="31%">
			<?php echo $likes.' Likes' ?>
		</td>
		<td width="31%">
			No Comments 
		</td>
	</tr>
	<tr>
		
	</tr>
</table>
</center>
</div>
</body>
</html>
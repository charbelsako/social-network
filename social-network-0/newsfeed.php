<?php 
	//this is very helpful for converting to ago-time, 
	$today = getdate();
	$d = $today['mday'];
	$m = $today['mon'];
	$y = $today['year'];
	//part of the datediff.php
	$today = date_create("$d-$m-$y");
	
	include("php_includes/check_login_status.php");
	include("php_includes/error_report.php");
	ini_set('display_errors', 'On');
?>
<?php
//get the names of all his friends
$friends = array();
$query = mysqli_query($db_conx, "SELECT user1,user2 FROM friends WHERE user1='$log_username' OR user2='$log_username' AND accepted='1'");
while( $row = mysqli_fetch_assoc($query) ){
	$name = $row["user1"];
	if($name == $log_username){
		array_push($friends, $row["user2"]);
	}else{
		array_push($friends, $row["user1"]);
	}
}
$ids = join("','", $friends);

?>

<?php
//get their photos to be put on the newsfeed page
$query = mysqli_query( $db_conx, "SELECT * FROM photos WHERE user IN('$ids')");
$news = "";
while( $row = mysqli_fetch_assoc($query)){
	$uploaddate = $row["uploaddate"];
	$UPLOAD = date_create_from_format('Y-m-d',$uploaddate);
	$diff = date_diff($today, $UPLOAD);
	$day_diff = $diff->d;
	$month_diff = $diff->m;
	$year_diff = $diff->y;
	
	if( $year_diff > 0 ){
		$ago = $year_diff.' year';	
	}else if( $month_diff > 0 ){
		$ago = $month_diff.' month';
	}else if( $day_diff > 0 && $day_diff < 2) {
		$ago = $day_diff.' day';
	}else if( $day_diff > 1 ){
		$ago = $day_diff.' days';
	}
	
	
	$filename = $row["filename"];		
	$user = $row["user"];
	$gallery = $row["gallery"];
	$likes = $row["likes"];

	$avatar_query = mysqli_query($db_conx, "SELECT avatar FROM users WHERE username='$user'");
	while($avrow = mysqli_fetch_assoc($avatar_query)){
		$avatar = $avrow["avatar"];
	}
	$like_query = mysqli_query($db_conx, "SELECT id FROM image_likes WHERE filename='$filename' AND user='$log_username' LIMIT 1");
	$has_liked = mysqli_num_rows($like_query);
	
	//split this so you can change what the like button does to like and dislike
	$news .= '<table id="pictureTable" border="1"><tr><td id="pp"><img id="avatar" src="user/'.$user.'/'.$avatar.'"/></td><td id="name">'.$user.'</td></tr><tr><td colspan="2" id="image"><img id="userimg" src="user/'.$user.'/'.$filename.'"></td></tr><tr><td>'.$likes.' Likes</td><td>23 Comments</td></tr>';
	
	
	
	if($has_liked < 1){
		//like button
		$news .= '<td id="likeArea"><center><button class="likeBtn" id="likeBtn'.$filename.'" onClick="likeImage(\''.$filename.'\', \''.$log_username.'\', \'likeBtn'.$filename.'\')" >Like</button></center>';
	}else{
		$news .= '<td class="likeBtn" id="likeArea"><center><button id="likeBtn'.$filename.'" onClick="Unlike(\''.$filename.'\',\'likeBtn'.$filename.'\')" >Unlike</button></center>';
	}
	
	
	//commenting 
	$news .= '<td id="commentingArea"><center><form id="commForm"><input id="comment" name="comment" type="text" placeholder="enter your comment here"></textarea><input id="commentSubmit" name="commentSubmit" type="submit" value="Post"></form></center></td></tr>';

	//comments
	$news .= '<tr></td><tr><td colspan="2" width="300">comments area don\'t get your hopes up this is very hard to make</td></tr>';

	$news .= '<tr> <td colspan="2"> Posted '.$ago.' ago </td> </tr> </table>';
}



?>
<?php
//this script inserts the like into the table
if( isset($_POST['like']) ){
	$imageName= $_POST['filename'];
	//user id is the $log_id variable
	$query1 = mysqli_query($db_conx, "INSERT INTO image_likes(filename, user, user_id, date_time) VALUES('$imageName', '$log_username', '$log_id', NOW() )");
	
	
	
	
	
	$notification_text = ''.$log_username.' Liked your image: <br> <a href="photo.php?user='.$user.'&photo='.$imageName.'">View Gallery</a> ';
	$notif_query = mysqli_query($db_conx, "INSERT INTO notifications(username, initiator, app, note, date_time) VALUES('$user', '$log_username', 'Image Like', '$notification_text' , NOW() )");
	
    //this code has been deprecated
	//remember to unset them
	//unset($_SESSION['Products']);
	
	
	//this won't work because if someone disliked an image that field would be deleted and the id field would still increment. well...i could decrement the likes field each time someone dislikes but whatever
	//$last_id = mysqli_insert_id($db_conx);
	
	$likes_query = mysqli_query($db_conx, "SELECT id FROM image_likes WHERE filename='$imageName'");
	$num_likes = mysqli_num_rows($likes_query);
	
	$likes_update = mysqli_query($db_conx, "UPDATE photos SET likes='$num_likes' WHERE filename='$imageName' ");
	
	/*if($likes_update){
		echo "updated the num likes field";
	}else{
		echo mysqli_error($db_conx);
	}*/
	
	if($query1 == true){
		echo "image_liked";
		exit();
	}/*else{
		echo mysqli_error($db_conx);
	}*/
	
	
	
}

?>
<?php 
//this code dislikes images hence deleting the row in the database corresponding to the logged username and the filename
if( isset($_POST['unlike'])  ){
	$filename = $_POST['filename'];
//get the time from that like to delete the notif
	$time = mysqli_query($db_conx, "SELECT date_time FROM image_likes WHERE filename='$filename' AND user_id='$log_id' LIMIT 1");
	while( $row = mysqli_fetch_assoc($time)){
		$date = $row["date_time"];
		$delete_notif = mysqli_query($db_conx, "DELETE FROM notifications WHERE initiator='$log_username' AND app='Image Like' AND date_time='$date'");
	}
	$query = mysqli_query($db_conx, "DELETE FROM image_likes WHERE filename='$filename' AND user_id='$log_id' ");

	//updating the num likes field in the photos table for that photot
	$likes_query = mysqli_query($db_conx, "SELECT id FROM image_likes WHERE filename='$filename'");
	$num_likes = mysqli_num_rows($likes_query);
	$likes_update = mysqli_query($db_conx, "UPDATE photos SET likes='$num_likes' WHERE filename='$filename'");



	if($query){
		echo "image_unliked";	
	}else{
		echo "something went wrong";
	}
	
	if($time){
		echo "successfully deleted the notification";	
	}else{
		echo "notification deletion error";
	}
	
	if($likes_update){
		echo "successfully deleted the like";	
	}else{
		echo mysqli_error($db_conx);
	}
	
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?= $log_username ?> News Feed</title>
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link rel="stylesheet" href="style/style.css">
<script src="js/main.js"></script>
<script>
function likeImage(filename, username, elem){
	_(elem).innerHTML = 'Unlike';
	_(elem).setAttribute('onclick', 'Unlike(\''+filename+'\',\''+elem+'\')');
	//_(elem).setAttribute('onclick','Unlike()');
	var ajax = ajaxObj("POST", "newsfeed.php");
	ajax.onreadystatechange = function() {
		if( ajaxReturn(ajax) ) {
			
			var rt = "image_liked";
			if( ajax.response == rt ){
				
				//code is going through the no option the same two string aren't the same apparently	
			} else {
				alert();
			}
			
		}
	}
	ajax.send("filename="+filename+"&like=yes");
}
</script>
<script>
function Unlike(filename, elem){
	_(elem).innerHTML = 'Like';
	_(elem).setAttribute('onclick', 'likeImage(\''+filename+'\',\'<?php echo $log_username; ?>\',\''+elem+'\')');
	//_(elem).setAttribute('onclick','likeImage()');
	var ajax = ajaxObj("POST", "newsfeed.php");
	ajax.onreadystatechange = function() {
		if(ajaxReturn(ajax) == true) {
			if(ajax.responseText == "unliked"){
				_(elem).innerHTML = 'Like';	
			} else {
				//alert(ajax.responseText);
			}
		}
	}
	ajax.send("filename="+filename+"&unlike=yes");
}

</script>
</head>
<body>
<?php include_once("template_pageTop.php"); ?>
<div id="pageMiddle">
<?php echo $news; ?>
</div>

</body>
</html>
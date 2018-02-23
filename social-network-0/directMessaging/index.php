<?php 
//remember to include files to check if the user is logged in. and to FETCH the username. the username is crucial because it determins who 
//you are (the sender)
include_once("../php_includes/check_login_status.php");
if(!$user_ok){
	echo "you are not logged in";
	exit();
}
?>

<?php
	if(isset($_POST["fl"])){
		include_once("../php_includes/db_conx.php");
		$friendName = $_POST["name"]; //get the friend name that he is looking for
		$username = $_SESSION["username"];
		//get the names of all his friends
		$friends = '';
		$query = mysqli_query($db_conx, "SELECT user1,user2 FROM friends WHERE user1='$username' OR user2='$username' AND accepted='1'");
		while( $row = mysqli_fetch_assoc($query) ){
			$name = $row["user1"];
			if($name == $username){
				$friends .= '<option value='.$row["user2"].'>';
			}else{
				$friends .= '<option value='.$row["user1"].'>';
			}
		}
		echo $friends;
		mysqli_close($db_conx);
		exit();
	}
?>

<?php
//get the chat lists
if( isset($_POST["getChats"]) ){
	include_once("../php_includes/db_conx.php");
	$username = $_SESSION["username"];
	$sql = "SELECT DISTINCT from_user, to_user FROM conversation WHERE from_user = '$username' OR to_user= '$username' ";
	$query = mysqli_query($db_conx, $sql);
	$chats = '';
	if(mysqli_num_rows($query) > 0){
		while($row = mysqli_fetch_assoc($query) ){
			$from = $row["from_user"];
			$to = $row["to_user"];
			if($from == $username){
				$value = $to;
			}else{
				$value = $from;
			}
			$chats .= '<button onclick="startConv(\''.$value.'\'); " value="'.$value.'">'.$value.' </button> <br />';
		}
	}
	// }else{
	// 	echo "no results found";
	// 	exit();
	// }
	echo $chats;
	//mysqli_close($db_conx);
	exit();
}
?>

<?php
// this code gets all the messages for a conversation
if(isset($_POST["getMsgs"])){
	include_once("../php_includes/db_conx.php");
	$username = $_SESSION["username"];
	$to = $_POST["to"];
	$cid = $_POST["cid"];
	$sql = "SELECT * FROM conversation WHERE to_user = '$to' ";
	$query = mysqli_query($db_conx, $sql);
	$msgs = '';
	while( $row = mysqli_fetch_assoc($query)){
		$from = $row["from_user"];
		$cid = $row["cid"];
		if($from == $username){
			$msgs .= '<div class="right msg" id="'.$cid.'">'.$row["message"].'</div>';
		}else{
			$msgs .= '<div class="left msg" id="'.$cid.'">'.$row["message"].'</div>';
		}
	}
	echo $msgs;
	mysqli_close($db_conx);
	exit();
}
?>
<?php
//this code adds the message to the database.
if(isset($_POST["msg"])){
	include_once("../php_includes/db_conx.php");
	$username = $_SESSION["username"];
	$msg = $_POST["msg"];
	$to = $_POST["to"];
	
	//getting the conversation id
	//checking if the cid exists in the database if it does add it to the inserted query if not make a new one
	$sql = "SELECT cid FROM conversation WHERE from_user = '$username' OR to_user = '$username' LIMIT 1";
	$query = mysqli_query($db_conx, $sql);
	$row = mysqli_fetch_assoc($query);
	$cid = $row["cid"];
	//inserting new cid into the database to the conversation table
	//cid will be used when checking for new messages
	//it will later be used for checking individuals for messages
	//without it the program will think that any new message is the new message for this conversation
	if($cid > 0){
		$sql = "INSERT INTO conversation(cid, from_user, to_user, message) VALUES('$cid','$username', '$to', '$msg') ";
	}else{
		$random = rand() % 10000;
		$sql = "INSERT INTO conversation(cid, from_user, to_user, message) VALUES('$random','$username', '$to', '$msg') ";
	}
	$query = mysqli_query($db_conx, $sql);
	if($query){
		echo "added message to the database";
		exit();
	} 
}
?>
<?php
//this code will update the messages div (remember to add sound)
//also remember this is VERY inefficient since now i'm calling thousands of ajax requests every couple of minutes
if(isset($_POST["getNew"])){
	include_once("../php_includes/db_conx.php");
	$username = $_SESSION["username"];
	$to = $_POST["to"];
	$last = $_POST["last"];
	$cid = $_POST["cid"]; // the current ongoing conversation. to not confuse the app with other conv.
	$sql = "SELECT message FROM conversation WHERE from_user = '$username' OR to_user= '$username' AND cid = '$cid' ORDER BY id DESC LIMIT 1";
	$query = mysqli_query($db_conx, $sql);
	$row = mysqli_fetch_assoc($query);
	if($row["message"] == $last){
		exit();
	}else{
		echo $row["message"];
	}
	mysqli_close($db_conx);
	exit();
}
?>
<!-- the below code is commented for later use this can be used to check for any new message and add
a number next to that person's name 
 -->
<!-- <?php
//this code will update the messages div (remember to add sound)
//this needs another id "cid" to get the current conversation instead of all conversations
//this code can be repurposed for more complex chat system. 
//also remember this is VERY inefficient since now i'm calling thousands of ajax requests every couple of minutes
if(isset($_POST["getNew"])){
	include_once("../php_includes/db_conx.php");
	$username = $_SESSION["username"];
	$to = $_POST["to"];
	$last = $_POST["last"];
	$sql = "SELECT message FROM conversation WHERE from_user = '$username' OR to_user= '$username' ORDER BY id DESC LIMIT 1";
	$query = mysqli_query($db_conx, $sql);
	$row = mysqli_fetch_assoc($query);
	if($row["message"] == $last){
		exit();
	}else{
		echo $row["message"];
	}
	mysqli_close($db_conx);
	exit();
}
?> -->
<!DOCTYPE html>
<html>
<head>
	<title>Direct Messaging with php</title>
	<style>
	/* comment in css */
	table{
		margin: auto;
	}
	#newChat, #chatList, #messages, #newMessage{
		border: 1px solid black;
	}
	#newChat{
		width: 300px;
		height: auto;
	}
	#chatList{
		width:300px;
		height: 400px;
	}
	#messages{
		width :500px;
		height: 400px;
	}

	.right{
		background-color: blue;
		margin-left: auto;
	}
	.left{
		background-color: yellow;		
		margin-right:auto;
	}
	.left .right{
		margin-top: 10px;
		color: black;
		width: 50%;
	}
	</style>
	<!-- this file contains the ajax module -->
<script src="../js/main.js"> //now i can use ajax. </script>
<script>
//this variable specifies if the setinterval should run
let runUpdate = false;
//run ajax to get the list of friends
function friendsList(){
	let search = document.getElementById('friend').value;
	var ajax = ajaxObj("POST", "index.php");
 	ajax.onreadystatechange = function() {
	        if(ajaxReturn(ajax) == true) {
	            if(ajax.responseText){
					document.getElementById('users').innerHTML = ajax.responseText;
				}
	    	}
    }
    ajax.send("fl=get&name="+search);   
}
//run ajax to get a list of chats
function chatList(){
	var ajax = ajaxObj("POST","index.php");
	ajax.onreadystatechange = function(){
		if(ajaxReturn(ajax) == true){
			if(ajax.responseText){
				//do something
				//make buttons with the name and add them to the chat list
				alert('chatlist ajax done');
				document.getElementById('chatList').innerHTML = ajax.responseText;
			}
		}
	}
	ajax.send("getChats=yes");
}
//run ajax to get messages
function getMessages(to){
	var ajax = ajaxObj("POST","index.php");
	ajax.onreadystatechange = function(){
		if(ajaxReturn(ajax) == true){
			if(ajax.responseText){
				//do something
				//make buttons with the name and add them to the chat list
				alert('messages ajax done');
				document.getElementById('messages').innerHTML = ajax.responseText;
			}
		}
	}
	ajax.send("getMsgs=yes&to="+to);
}
//if messages already exist run ajax to get the last message
function updateMessages(){
	//this function will run continuously till the end of time
	let last_msg = document.getElementsByClassName('msg');
	//the below comment shows how to get an elements id from it's class only
	// let cid = document.getElementsByClassName('msg')[0].id; // try later: document.querySelector('.myClassName').id
	let chat_id = last_msg[0].id;
	last_msg = last_msg[last_msg.length - 1].innerText;
	var ajax = ajaxObj("POST", "index.html");
	ajax.onreadystatechange = function(){
		if(ajaxReturn(ajax) == true){
			if(ajax.responseText == "new message"){
				//do something
				document.getElementById('messages').innerHTML += '<div class="right msg" id="'+chat_id+'">'+message+'</div>';
			}
		}
	};
	ajax.send("getNew=true&last="+last_msg+"&cid="+chat_id);
}

function startConv(val){
	runUpdate = true;
	let value = val;
	document.getElementById('to').innerText = val;
	getMessages(value);
}
function startChat(){
	//this function only makes a button out of the name in the new chat input. so that the user can actually press the button to VIEW and  CHAT with the friend
	let chats = document.getElementById('chatList');
	let name = document.getElementById('user').value;
	chats.innerHTML += '<button onclick="startConv(this.value);" value="'+name+'">'+name+' </button> <br />';
}
function sendMessage(){
 	//sends the message;
 	let message = document.getElementById('message').value;
 	let to = document.getElementById('to').innerHTML;
 	
 	//using AJAX to insert the data into mysql
 	var ajax = ajaxObj("POST","index.php");
 	ajax.onreadystatechange = function(){
 		if(ajaxReturn(ajax) == true){
 			if( ajax.responseText ){
 				alert(ajax.responseText);
 				//populate the messages area with the message
 				document.getElementById('messages').innerHTML += '<div class="right msg" id="">'+message+'</div>';
 			}
 		}
 	}
 	ajax.send("msg="+message+"&to="+to);
}
</script>
</head>
<body>
<table border="1">
	<tr>
		<td>
			<div id="newChat">
			<h3>New Chat</h3>
			<!-- this form will search the database and show him who he is allowed to message -->
			<form id="searchFriends" action="" onsubmit="return false">
				<input type="search" name="friend" onfocus="friendsList()" id="friend" list="users" autocomplete="off">
				<input type="submit" onclick="startChat()">
				<br>
				<datalist id="users">
					<!-- this will be populated by an ajax request. DONE!-->
				</datalist>
			</form>
	</div>
		</td>
		<td>
			To:
			<span id="to"></span>
		</td>
	</tr>
	<tr>
		<td> 
		<h3>Chat List</h3>
			<div id="chatList" >
				<!-- this will be populated by all chats made by the user -->	
			</div>
		</td>
		<td> 
			<div id="messages" > 
				<!-- this div will show all messages for a certain chat --> 
				this will contain all the messages
			</div>
		</td>
	</tr>
	<tr>
		<td>
		</td>
		<td>
			<form id="newMessage" onsubmit="return false">
				<input type="text" name="message" id="message">
				<input type="submit" name="send" value="send" onclick="sendMessage()">
			</form>
		</td>
	</tr>
</table>
</body>
<script type="text/javascript">
	//running scripts here
	chatList();
	if(runUpdate){
		setInterval(updateMessages, 1000);
	}
</script>
</html>
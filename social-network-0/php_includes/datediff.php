<?
//this is very helpful for converting to ago-time, 
$today = getdate();
$d = $today['mday'];
$m = $today['mon'];
$y = $today['year'];
//echo "$m-$d-$y";
$date1 = date_create("$d-$m-$y");
//echo "<br>";
$date2 = date_create('02-11-2015');
$diff = date_diff($date1, $date2);
$day_diff = $diff->d;



if($day_diff <= 1){
	echo "this post is from yesterday <br>";
}

print_r($diff);



?>
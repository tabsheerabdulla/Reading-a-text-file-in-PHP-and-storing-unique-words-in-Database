<?php 

// print_r(readfile($argv[1]));

$myfile = array_unique(preg_replace("/[^a-zA-Z 0-9]+/", "",explode(' ', file_get_contents($argv[1]))));
$mode = (!empty($argv[2]))?$argv[2]:"adv";
$db_name= (!empty($argv[3]))?$argv[3]:"words_db";
$host = (!empty($argv[4]))?$argv[4]:"localhost";
$user = (!empty($argv[5]))?$argv[5]:"root";
$pw = (!empty($argv[6]))?$argv[6]:"";



echo "\r\n -----------Program Starts--------------\r\n" . PHP_EOL;
echo "\r\n Processing the file.. \r\n" . PHP_EOL;
$chk = checkDbExist($db_name);
if(!$chk) createDb($db_name);

insertData($myfile,$db_name);
echo "\r\n There are ".count($myfile)." unique words in the provided text file \r\n" . PHP_EOL;
// echo "\r\n -----------Program Starts--------------\r\n" . PHP_EOL;
// echo $myfile. PHP_EOL . "\r\n";
// echo "File Content: ".file_get_contents($argv[1])."\r\n".PHP_EOL;
// print_r($myfile);
// echo PHP_EOL . "\r\n";

echo " Distinct unique words(DB): ".getDisticntCount($db_name)."\r\n".PHP_EOL;
echo " Creating watchlist table.. \r\n".PHP_EOL;
createWLTab($db_name, $myfile);
echo " Watchlist words: \r\n".get_watchListData($db_name)."\r\n".PHP_EOL;
echo " -----------Program Ends-----------------\r\n" . PHP_EOL;

function createDb($db_name){ 

		$conn = startConn();

		// Create database
		$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
		if ($conn->query($sql) === TRUE) {
			$conn->query("USE $db_name");
			if($GLOBALS['mode'] == 'adv'){
			echo " Database created successfully"."\r\n".PHP_EOL;;
			}
		} else {
			if($GLOBALS['mode'] == 'adv'){
			echo "Error creating database: " . $conn->error;
			}
		}



		$tableQ = "CREATE TABLE distinct_tab (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,distinct_words VARCHAR(90) NOT NULL UNIQUE)";
		if ($conn->query($tableQ) === TRUE) {
		if($GLOBALS['mode'] == 'adv'){
			echo " Table created successfully"."\r\n".PHP_EOL;;
			}
		} else {
			if($GLOBALS['mode'] == 'adv'){
			echo "Error creating table: " . $conn->error;
			}
		}

		$conn->close();


}

function checkDbExist($db_name){

	$conn = startConn();

	$sql = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'");

	if ($sql->num_rows == 1) {
	return true;
	}else{
	return false;
	}

}

function startConn(){

	$servername = $GLOBALS['host'];
	$username = $GLOBALS['user'];
	$password = $GLOBALS['pw'];

	// Create connection
	$conn = new mysqli($servername, $username, $password);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}else{
	return $conn;
	}

}

function insertData($data,$db_name){

	$conn = startConn();
	$conn->query("USE $db_name");
	$stmt = $conn->prepare("INSERT INTO `distinct_tab` (`distinct_words`) VALUES (?)");
	$stmt->bind_param("s", $distinct_words);

	if($GLOBALS['mode'] == 'adv'){
	echo "\r\n Inserting rows to `distinct_tab` of Database $db_name... \r\n".PHP_EOL;
	}
	$count = 0;
	foreach($data as $k=>$v){
	$distinct_words = $v;
	$flag = $stmt->execute();
	if($flag == true) $count++;
	}
	if($GLOBALS['mode'] == 'adv'){
	if($count < count($data))
	echo "\r\n $count row added to database. Some words could not be saved as it is already there in DB \r\n".PHP_EOL;
	else echo "\r\n $count row added to database.\r\n".PHP_EOL;
	}

}

function getDisticntCount($db_name){

	$conn = startConn();
	$conn->query("USE $db_name");
	$sql = $conn->query("SELECT distinct(count(*)) as countWords from `distinct_tab`");
	return $sql->fetch_assoc()['countWords'];
	
}

function createWLTab($db_name, $data){

	$conn = startConn();
	$conn->query("USE $db_name");

	$tableQ = "CREATE TABLE if not exists `watchlist_tab` (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,watchlist_words VARCHAR(90) NOT NULL UNIQUE)";
	if ($conn->query($tableQ) === TRUE) {
		if($GLOBALS['mode'] == 'adv'){
		echo " Watchlist Table created successfully"."\r\n".PHP_EOL;
		}
	} else {
		if($GLOBALS['mode'] == 'adv'){
		echo "Error creating table: " . $conn->error;
		}
	}
	
	$stmt = $conn->prepare("INSERT INTO `watchlist_tab` (`watchlist_words`) VALUES (?)");
	$stmt->bind_param("s", $distinct_words);
	if($GLOBALS['mode'] == 'adv'){
	echo "\r\n Inserting rows to `watchlist_tab` of Database $db_name... \r\n".PHP_EOL;
	}
	$count = 0;
	foreach($data as $k=>$v){
	if(strlen($v)>5){
	$distinct_words = $v;
	$flag = $stmt->execute();
	if($flag == true) $count++;
	}
	
	}
	if($GLOBALS['mode'] == 'adv'){
	echo "\r\n $count row added to database.\r\n".PHP_EOL;
	}
	
	

}

function get_watchListData($db_name){

	$conn = startConn();
	$conn->query("USE $db_name");
	$sql = $conn->query("SELECT watchlist_words FROM watchlist_tab");
	$allData;
	if ($sql->num_rows > 0) {
    // output data of each row
    while($row = $sql->fetch_assoc()) {
		$allData[] = $row['watchlist_words'];
	}
	}	
	return implode(', ',$allData);

}


?>
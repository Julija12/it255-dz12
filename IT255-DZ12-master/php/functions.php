<?php
include("config.php");
function checkIfLoggedIn(){
	global $conn;
	if(isset($_SERVER['HTTP_TOKEN'])){
		$token = $_SERVER['HTTP_TOKEN'];
		$result = $conn->prepare("SELECT * FROM korisnici WHERE token=?");
		$result->bind_param("s",$token);
		$result->execute();
		$result->store_result();
		$num_rows = $result->num_rows;
		if($num_rows > 0)
		{
			return true;
		}
		else{	
			return false;
		}
	}
	else{
		return false;
	}
}
function login($username, $password){
	global $conn;
	$rarray = array();
	if(checkLogin($username,$password)){
		$id = sha1(uniqid());
		$result2 = $conn->prepare("UPDATE korisnici SET token=? WHERE username=?");
		$result2->bind_param("ss",$id,$username);
		$result2->execute();
		$rarray['token'] = $id;
	} else{
		$rarray['error'] = "Invalid username/password";
	}
	return json_encode($rarray);
}
function checkLogin($username, $password){
	global $conn;
	$password = md5($password);
	$result = $conn->prepare("SELECT * FROM korisnici WHERE username=? AND password=?");
	echo $conn->error;
	$result->bind_param("ss",$username,$password);
	$result->execute();
	$result->store_result();
	$num_rows = $result->num_rows;
	if($num_rows > 0)
	{
		return true;
	}
	else{	
		return false;
	}
}
function register($username, $password, $firstname, $lastname){
	global $conn;
	$rarray = array();
	$errors = "";
	if(checkIfUserExists($username)){
		$errors .= "Username already exists\r\n";
	}
	if(strlen($username) < 5){
		$errors .= "Username must have at least 5 characters\r\n";
	}
	if(strlen($password) < 5){
		$errors .= "Password must have at least 5 characters\r\n";
	}
	if(strlen($firstname) < 3){
		$errors .= "First name must have at least 3 characters\r\n";
	}
	if(strlen($lastname) < 3){
		$errors .= "Last name must have at least 3 characters\r\n";
	}
	if($errors == ""){
		$stmt = $conn->prepare("INSERT INTO korisnici (firstname, lastname, username, password) VALUES (?, ?, ?, ?)");
		$pass =md5($password);
		$stmt->bind_param("ssss", $firstname, $lastname, $username, $pass);
		if($stmt->execute()){
			$id = sha1(uniqid());
			$result2 = $conn->prepare("UPDATE korisnici SET token=? WHERE username=?");
			$result2->bind_param("ss",$id,$username);
			$result2->execute();
			$rarray['token'] = $id;
		}else{
			$rarray['error'] = "Database connection error";
		}
	} else{
		$rarray['error'] = json_encode($errors);
	}
	
	return json_encode($rarray);
}
function checkIfUserExists($username){
	global $conn;
	$result = $conn->prepare("SELECT * FROM korisnici WHERE username=?");
	$result->bind_param("s",$username);
	$result->execute();
	$result->store_result();
	$num_rows = $result->num_rows;
	if($num_rows > 0)
	{
		return true;
	}
	else{	
		return false;
	}
}
function addRoom($roomName, $hasTV, $beds, $kvadratura){
	global $conn;
	$rarray = array();
	if(checkIfLoggedIn()){
	$stmt = $conn->prepare("INSERT INTO rooms (roomname, tv, beds, kvadratura) VALUES (?, ?, ?, ?)");
	$stmt->bind_param("ssss", $roomName, $hasTV, $beds, $kvadratura);
	if($stmt->execute()){
		$rarray['sucess'] = "ok";
	}else{
		$rarray['error'] = "Database connection error";
	}
	return json_encode($rarray);
		} else{
		$rarray['error'] = "Please log in";
		return json_encode($rarray);
	}
}

function getRooms(){
	global $conn;
	$rarray = array();
	
		$result = $conn->query("SELECT * FROM rooms");
		$num_rows = $result->num_rows;
		$rooms = array();
		if($num_rows > 0)
		{
			$result2 = $conn->query("SELECT * FROM rooms");
			while($row = $result2->fetch_assoc()) {
				$one_room = array();
				$one_room['id'] = $row['id'];
				$one_room['roomname'] = $row['roomname'];
				$one_room['tv'] = $row['tv'];
				$one_room['beds'] = $row['beds'];
				$one_room['kvadratura'] = $row['kvadratura'];
				array_push($rooms,$one_room);
			}
		}
		$rarray['rooms'] = $rooms;
		return json_encode($rarray);
	
}
?>
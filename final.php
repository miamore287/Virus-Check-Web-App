<?php
function testerFunc(){
require_once 'loginFinal.php';
    $connection = new mysqli($hn, $un, $pw, $db);
    if($connection->connect_error) die(errorHandlerFunc("Access to Database failed."));
    echo <<<_END
	<h1> Login for Admin </h1>
    <form method='POST' action=''>
    <input type='submit' name="login" value="Login">
    </form>
    <br>
_END;

	$queryVariable = "SELECT EXISTS(SELECT * from usersTable WHERE username = 'admin');";
    $result = $connection->query($queryVariable);
	
	if(!result) die("no");
	$row = $result -> fetch_array(MYSQLI_NUM);
	
	if($row[0] == [0]){
	$username = 'admin';
	$password = '(Santaclaus2000)';
	
	$salt1 = randomSaltGenerator();
	$salt2 = randomSaltGenerator();
	
	$token = hash('ripemd128', '$salt1$password$salt2');
	}
	else {
	$queryVariable = "SELECT * from usersTable WHERE username = 'admin'";
	$result = $connection->query($queryVariable);
	if(!result)die(errorHandlerFunc("Connection to Database failed"));
	$row = $result ->fetch_array(MYSQLI_NUM);
	$result->close();
	$salt1 = $row[2];
	$salt2 = $row[3];
	}
	if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
        $un_temp = mysql_entities_fix_string($connection, $_SERVER['PHP_AUTH_USER']);
        $pw_temp = mysql_entities_fix_string($connection, $_SERVER['PHP_AUTH_PW']);
        $queryVariable = "SELECT * FROM usersTable WHERE username = '$un_temp'";
        $result = $connection->query($queryVariable);
        if(!$result) die(errorHandlerFunc("Database access failed."));
        else if ($result ->num_rows){
            $row = $result->fetch_array(MYSQLI_NUM);
            $result->close();
            $token = hash('ripemd128', "$salt1$pw_temp$salt2");
            if($token == $row[1]){
                admin_upload($connection);
            } else {
                die("Invalid username/password combination");
            }
        }
        else die("Invalid username/password combination");
    }
    else if (isset($_POST['login'])){
        header('WWW-Authenticate: Basic realm="Restricted Section."');
        header('HTTP/1.0 401 Unauthorized');
        die (user_upload($connection));
    }
    else
    {
        user_upload($connection);
    }
}	

function upload_adminForm($connection){
echo <<<_END
	<html><head><title>Admin Upload Form</title></head><body>
	<h1>Hello Admin</h1>
    <form method='post' action='' enctype='multipart/form-data'>
    Upload  your file:
    <input type='file' name='malware' size='20'>
    <br>
    Enter the name of your file:
    <input type = 'text' name = 'name'>
    <br>
    <input type='submit' value='ADD FILE'>
    </form>
_END;

if($_FILES && isset($_POST['name'])){
        $stmt = $connection->prepare('INSERT INTO malwareTable VALUES(?,?)');
        $stmt->bind_param('ss', $name, $virusVar);
        $fh = fopen($_FILES['malware']['tmp_name'], 'r') or die(errorHandlerFunc("File access failed."));
        $fileString = file_get_contents($_FILES['malware']['tmp_name']);
        $virusVar = mysql_entities_fix_string($connection, $fileString);
        $virusVar = fread($fh, 20);
        $name = mysql_entities_fix_string($connection, $_POST['name']);
        $stmt->execute();
        $stmt->close();
        fclose($fh);
    }
}

function randomSaltGenerator(){
    $possibleChars = '!@#$%^&*()0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $charLength = strlen($possibleChars);
    $saltString = '';
    for ($i = 0; $i < 5; $i++) {
        $saltString .= $possibleChars[rand(0, $charLength - 1)];
    }
    return $saltString;
}


function errorHandlerFunc($err){
	echo $err;
}

function mysql_entities_fix_string($connection,$string)
{
    return htmlentities(mysql_fix_string($connection, $string));
}

function mysql_fix_string($connection, $string)
{
    if (get_magic_quotes_gpc())
        $string = stripslashes($string);

        return $connection->real_escape_string($string);
}

function user_upload($connection){
    echo <<<_END
    <form method='post' action='' enctype='multipart/form-data'>
    <h1> Malware Check: </h1>
    Upload your file:
    <input type='file' name='putative' size='10'>
    <br>
    <input type='submit' value='Submit'>
    </form>
_END;

if($_FILES){
        $check = false;
        $virusName = '';
        $file_tmp = file_get_contents($_FILES['putative']['tmp_name']);
        $fileString = mysql_entities_fix_string($connection, $file_tmp);
        $fh = fopen($_FILES['putative']['tmp_name'], 'r') or die(errorHandlerFunc("Cannot access file."));
        $pointer = 0; 
        for ($i = 0; $i < strlen($fileString); $i++) {
            fseek($fh, $pointer, SEEK_SET);
            $tmp = fread($fh, 20);
            $queryVariable = "SELECT * FROM malwareTable WHERE virus = '$tmp'";
            $result = $connection->query($queryVariable);
            if(!$result) die(errorHandlerFunc("Access to Database failed."));
            $row = $result->fetch_array(MYSQLI_ASSOC);
            if($row['name'] != ''){
                $check = true;
                $virusName = $row['name'];
                break;
            }
            $pointer++;
        }

        if($check == false){
            echo "File is clean. No known malware detected.";
        } else {
            echo "File is affected. A known malware " .$virusName. " found.";
        }
    }
}
$connection->close();
testerFunc();
?>
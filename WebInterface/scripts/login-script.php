<?php

	session_start();
	require 'config.php';
	
	$Username = mysql_real_escape_string(stripslashes($_POST['Username']));
	$Username = trim($Username);
	$Password = md5(mysql_real_escape_string(stripslashes($_POST['Password'])));
	$result = mysql_query("SELECT * FROM WA_Players WHERE name='$Username' AND pass='$Password'");
	$count = mysql_num_rows($result);
	$playerRow = mysql_fetch_row($result);
	if ($count==1){
		$hour = time() + 3600;
		$_SESSION['User'] = $playerRow[1];		
		if ($playerRow[6] == 1){$_SESSION['Admin'] = true;}else{$_SESSION['Admin'] = false;}
		if ($playerRow[4] == 1){$_SESSION['canBuy'] = true;}else{$_SESSION['canBuy'] = false;}
		if ($playerRow[5] == 1){$_SESSION['canSell'] = true;}else{$_SESSION['canSell'] = false;}
		header("Location: ../index.php");
	}else{
		$past = time() - 100;
		unset($_SESSION['User']);
		unset($_SESSION['canBuy']);
		unset($_SESSION['canSell']);
		unset($_SESSION['Admin']);
		header("Location: ../login.php?error=1");
	}
	
?>
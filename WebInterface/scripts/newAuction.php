<?php
	session_start();
	if (!isset($_SESSION['User'])){
		header("Location: login.php");
	}
	$isAdmin = $_SESSION['Admin'];
	$user = $_SESSION['User'];
	require 'config.php';
	require 'itemInfo.php';
	if ($useTwitter == true){require_once 'twitter.class.php';}
	$itemNameFull = explode(":", $_POST['Item']);
	$sellName = $itemNameFull[0];
	$sellDamage = $itemNameFull[1];
	$sellPrice = round($_POST['Price'], 2);
	$itemFullName = getItemName($sellName, $sellDamage);
	if ($sellPrice > $maxSellPrice){ $sellPrice == $maxSellPrice; }
	$sellQuantity = floor($_POST['Quantity']);
	$maxStack = getItemMaxStack($sellName, $sellDamage);
	
	$queryItems=mysql_query("SELECT * FROM WA_Items WHERE player='$user' AND name='$sellName' AND damage='$sellDamage'");
	list($id, $itemName, $itemDamage, $itemOwner, $itemQuantity)= mysql_fetch_row($queryItems);
    if ($sellQuantity <= 0){
		header("Location: ../myauctions.php?error=5");
	}
	if ($sellPrice <= 0)
	{
		header("Location: ../myauctions.php?error=6");
	}
	else{
		if (is_numeric($sellPrice)){	
			if ((is_numeric($sellQuantity))&&($sellQuantity > 0)){
				$sellQuantity = round($sellQuantity);
				if ($itemQuantity >= $sellQuantity)
				{
					$itemsLeft = $itemQuantity - $sellQuantity;
					if ($sellQuantity > 0)
					{
						$itemQuery = mysql_query("INSERT INTO WA_Auctions (name, damage, player, quantity, price) VALUES ('$sellName', '$sellDamage', '$user', '$sellQuantity', '$sellPrice')");
					}
					if ($itemsLeft == 0)
					{
						$itemDelete = mysql_query("DELETE FROM WA_Items WHERE id='$id'");
					}
					else
					{
						$itemUpdate = mysql_query("UPDATE WA_Items SET quantity='$itemsLeft' WHERE id='$id'");
					}
					if ($useTwitter == true){
						$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
						$twitter->send('[WA] Auction Created: '.$sellQuantity.' x '.$itemFullName.' for '.$sellPrice.' each. At '.date("H:i:s").' #webauction');
					}
					header("Location: ../myauctions.php");
				}else{
					header("Location: ../myauctions.php?error=2");
				}
			}
			else
			{
				header("Location: ../myauctions.php?error=4");
			}
		}
		else
		{
			header("Location: ../myauctions.php?error=3");
		}
	}
	
?>
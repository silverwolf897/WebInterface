<?php
	session_start();
	if (!isset($_SESSION['User'])){
		header("Location: login.php");
	}
	$user = trim($_SESSION['User']);
	require 'config.php';
	require 'itemInfo.php';
    require_once '../classes/EconAccount.php';
	if ($useTwitter == true){require_once 'twitter.class.php';}

   
    $numberLeft = 0;

    $player = new EconAccount($user, $useMySQLiConomy, $iConTableName);

	
	$itemId = $_POST['ID'];
    $queryAuctions=mysql_query("SELECT * FROM WA_Auctions WHERE id='$itemId'");
	list($id, $itemName, $itemDamage, $itemOwner, $itemQuantity, $itemPrice)= mysql_fetch_row($queryAuctions);

    $owner = new EconAccount($itemOwner, $useMySQLiConomy, $iConTableName);



    if (is_numeric($_POST['Quantity']))
    {
	    $buyQuantity = mysql_real_escape_string(stripslashes(round(abs($_POST['Quantity']))));
    }
    else{
        $buyQuantity = $itemQuantity;
    }

    $totalPrice = round($itemPrice*$buyQuantity, 2);
	$numberLeft = $itemQuantity-$buyQuantity;

	if ($numberLeft < 0){
        $_SESSION['error'] = "You are attempting to purchase more than the maximum available";
		header("Location: ../index.php");
	}
	else{

	$itemFullName = getItemName($itemName, $itemDamage);
	if ($player->money >= $totalPrice){
		if ($user != $itemOwner){
			$timeNow = time();
			$player->money = $player->money - $totalPrice;
			$owner->money = $owner->money + $totalPrice;

            $player->saveMoney($useMySQLiConomy, $iConTableName);
            $owner->saveMoney($useMySQLiConomy, $iConTableName);
            $alertQuery = mysql_query("INSERT INTO WA_SaleAlerts (seller, quantity, price, buyer, item) VALUES ('$itemOwner', '$buyQuantity', '$itemPrice', '$user', '$itemFullName')");

			if ($sendPurchaceToMail){
				$maxStack = getItemMaxStack($itemName, $itemDamage);
				while($buyQuantity > $maxStack)
				{
					$buyQuantity -= $maxStack;
					$itemQuery = mysql_query("INSERT INTO WA_Mail (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$maxStack')");
				}
				if ($buyQuantity > 0)
				{
					$itemQuery = mysql_query("INSERT INTO WA_Mail (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$buyQuantity')");
				}
			}else{
				$queryPlayerItems =mysql_query("SELECT * FROM WA_Items WHERE player='$user'");
				$foundItem = false;
				$stackId = 0;
				$stackQuant = 0;
				while(list($pid, $pitemName, $pitemDamage, $pitemOwner, $pitemQuantity)= mysql_fetch_row($queryPlayerItems))
				{	
					if($itemName == $pitemName)
					{
						if ($pitemDamage == $itemDamage)
						{
							$foundItem = true;
							$stackId = $pid;
							$stackQuant = $pitemQuantity;
						}
					}
				}
				if ($foundItem == true)
				{
					$newQuantity = $buyQuantity + $stackQuant;
					$itemQuery = mysql_query("UPDATE WA_Items SET quantity='$newQuantity' WHERE id='$stackId'");
				}else
				{
					$itemQuery = mysql_query("INSERT INTO WA_Items (name, damage, player, quantity) VALUES ('$itemName', '$itemDamage', '$user', '$buyQuantity')");
				}
			}
            if ($numberLeft != 0)
            {
			    $itemDelete = mysql_query("UPDATE WA_Auctions SET quantity='$numberLeft' WHERE id='$itemId'");
            }else{
                $itemDelete = mysql_query("DELETE FROM WA_Auctions WHERE id='$itemId'");
            }
			$logPrice = mysql_query("INSERT INTO WA_SellPrice (name, damage, time, buyer, seller, quantity, price) VALUES ('$itemName', '$itemDamage', '$timeNow', '$user', '$itemOwner', '$buyQuantity', '$itemPrice')");
			$base = isTrueDamage($itemName, $itemDamage);
			
			if ($base > 0){
				$queryMarket=mysql_query("SELECT * FROM WA_MarketPrices WHERE name='$itemName' AND damage='0' ORDER BY id DESC");
				
			}else{
				$queryMarket=mysql_query("SELECT * FROM WA_MarketPrices WHERE name='$itemName' AND damage='$itemDamage' ORDER BY id DESC");	
				
			}
			$countMarket = mysql_num_rows($queryMarket);
			if ($countMarket == 0){
				//market price not found
				$newMarketPrice = $itemPrice;
				$marketCount = $buyQuantity;
			}else{
				//found get first item
				$rowMarket = mysql_fetch_row($queryMarket);
				$marketId = $rowMarket[0];
				$marketPrice = $rowMarket[4];
				$marketCount = $rowMarket[5];
				$newMarketPrice = (($marketPrice*$marketCount)+$totalPrice)/($marketCount+$buyQuantity);
				$marketCount = $marketCount+$buyQuantity;
			}
			if ($base > 0){
				
				$newMarketPrice = ($newMarketPrice/($base - $itemDamage))*$base;
				$insertMarketPrice = mysql_query("INSERT INTO WA_MarketPrices (name, damage, time, marketprice, ref) VALUES ('$itemName', '0', '$timeNow', '$newMarketPrice', '$marketCount')");
			}else{
				
				$insertMarketPrice = mysql_query("INSERT INTO WA_MarketPrices (name, damage, time, marketprice, ref) VALUES ('$itemName', '$itemDamage', '$timeNow', '$newMarketPrice', '$marketCount')");
			}
			if ($useTwitter == true){
				$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
				$twitter->send('[WA] Item Bought: '.$buyQuantity.' x '.$itemFullName.' for '.$itemPrice.' each. At '.date("H:i:s").' #webauction');
			}
            $_SESSION['success'] = "You purchased $buyQuantity $itemFullName from $itemOwner for $$totalPrice";
			header("Location: ../index.php");
			 
		}else {
            $_SESSION['error'] = 'You cannnot buy your own items.';
			header("Location: ../index.php");
		}
	}else{
        $_SESSION['error'] = 'You do not have enough money.';
        header("Location: ../index.php");
	}
	}

?>

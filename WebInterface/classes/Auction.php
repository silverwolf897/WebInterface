<?php

class Auction
{

    public $id;
    public $name;
    public $damage;
    public $player;
    public $quantity;
    public $price;
    public $buyquantity;
    public $itemfullname;

    function __construct($id = null)
    {

       if ($id){
            $query=mysql_query("SELECT * FROM WA_Auctions WHERE id='$id'");
            $row = mysql_fetch_object($query);
            $this->id = $row->id;
            $this->name = $row->name;
            $this->damage = $row->damage;
            $this->player = $row->player;
            $this->quantity = $row->quantity;
            $this->price = $row->price;
	   }else{
            $this->id = null;
            $this->name = null;
            $this->damage = null;
            $this->player = null;
            $this->quantity = null;
            $this->price = null;
            $this->buyquantity = null;
            $this->itemfullname = null;
        }
    }

    public function addAlert($auction, $buyer)
    {
        $alertQuery = mysql_query("INSERT INTO WA_SaleAlerts (seller, quantity, price, buyer, item) VALUES ('$this->player', '$this->buyquantity', '$this->price', '$buyer', '$this->itemfullname')");
    }

    public function updateQuantity()
    {
        $left = $this->quantity - $this->buyquantity;
        
        $upquery = mysql_query("UPDATE WA_Auctions SET quantity='$left' WHERE id='$this->id'");
    }

    public function deleteAuction()
    {
        $delquery = mysql_query("DELETE FROM WA_Auctions WHERE id='$this->id'");
    }

    public function add($seller)
    {
        $itemQuery = mysql_query("INSERT INTO WA_Auctions (name, damage, player, quantity, price) VALUES ('$this->name', '$this->damage', '$seller', '$this->quantity', '$this->price')");
    }
}

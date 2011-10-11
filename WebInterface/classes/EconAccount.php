class EconAccount
{
    public $id;
    public $name;
    public $money;

    function __construct($user, $useMySQLiConomy, $iConTableName)
    {

       if ($useMySQLiConomy){
            $query=mysql_query("SELECT * FROM $iConTableName WHERE username='$user'");
            $iConRow = mysql_fetch_object($query);
            $this->id = $iConRow->id;
            $this->name = $iConRow->username;
            $this->money = $iConRow->balance;
	   }else{
            $query = mysql_query("SELECT * FROM WA_Players WHERE name='$user'");
            $row = mysql_fetch_object($query);
            $this->id = $row->id;
            $this->name = $row->name;
            $this->money = $row->money;
        }
    }
}

<?php

class DbHandler {

    private $conn;

    function __construct() {
        require_once 'dbConnect.php';
        // opening db connection
        $db = new dbConnect();
        $this->conn = $db->connect();
    }
    /**
     * Fetching single record
     */
    public function runQuery($query) {
        $r = $this->conn->query($query) or die($this->conn->error.__LINE__);
        return $result = $this->conn->affected_rows;  
    }

    /**
     * Fetching single record
     */
    public function getOneRecord($query) {
        $r = $this->conn->query($query.' LIMIT 1') or die($this->conn->error.__LINE__);
        return $result = $r->fetch_assoc();    
    }

    /**
     * Fetching multiple records
     */
    public function getRecordset($query) {
        $r = $this->conn->query($query) or die($this->conn->error.__LINE__);

        if($r->num_rows > 0){
            $result = array();
            while($row = $r->fetch_assoc()){
                $result[] = $row;
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Delete record(s)
     */
    public function deleteFromTable($table, $idcol, $value) {
        $r = $this->conn->query("DELETE FROM $table WHERE $idcol = '$value'") or die($this->conn->error.__LINE__);
        return $result = $this->conn->affected_rows;    
    }
    
    /**
     * Creating new record using array (instead of object)
     */
    public function insertToTable($supplied_values, $column_names, $table_name) {
        
        $columns = '';
        $values = '';
        //column names
        foreach ($column_names as $col) {
            $columns .= "`".$col . "`,";
        }
        //values
        foreach ($supplied_values as $val) {
            $values .= "'".$val."',";
        }

        $query = "INSERT INTO `".$table_name."` (".trim($columns,',').") VALUES(".trim($values,',').")";
        $r = $this->conn->query($query) or die($this->conn->error.__LINE__);

        if ($r) {
            return $this->conn->insert_id;
            } else {
            return NULL;
        }
    }
    public function updateInTable($table, $columnsArray, $where){ 
        $a = array();
        $w = "";
        $c = "";
        //where clause
        foreach ($where as $key => $value) {
            $w .= " AND " .$key. " = '".$value."'";
        }
        //set columns
        foreach ($columnsArray as $key => $value) {
            $c .= $key. " = '".$value."', ";
        }
        $c = rtrim($c,", ");

        //run update query
        $query = "UPDATE `$table` SET $c WHERE 1=1 ".$w;
        //return ($query);
        $r = $this->conn->query($query); //or die($this->conn->error.__LINE__);

        if ($r) {
            //u can try to get affected rows, not so necessary
            $affected_rows = $this->conn->affected_rows;
            return $affected_rows;
            //return "OK";
            } else {
            return $this->conn->error;
        }
        
    }

    public function updateToNull($table, $column, $where){ 
        $a = array();
        $w = "";
        //where clause
        foreach ($where as $key => $value) {
            $w .= " AND " .$key. " = '".$value."'";
        }

        //run update query
        $query = "UPDATE `$table` SET $column = NULL WHERE 1=1 ".$w;
        //return ($query);
        $r = $this->conn->query($query); //or die($this->conn->error.__LINE__);

        if ($r) {
            //u can try to get affected rows, not so necessary
            $affected_rows = $this->conn->affected_rows;
            return $affected_rows;
            //return "OK";
            } else {
            return $this->conn->error;
        }

    }

    /*function creates a new user session based on supplied user details
        used in signup, login and verify user*/
    public function createUserSession($user, $logintype = 'DEFAULT') {
        // start a new session
        if (!isset($_SESSION)) {
            session_start();
        }
        $user['user_last_auth'] = $logintype;
        // create session variables
        $_SESSION['trenova_user'] = $user;

        return true;
    }


public function getSession($session_id = ""){
    if (!isset($_SESSION)) {
        if(!empty($session_id)) session_id($session_id);
        session_start();
    }
    $sess = array();
    if(isset($_SESSION['trenova_user']))
    {
        $sess["trenova_user"] = $_SESSION['trenova_user'];
    }
    return $sess;
}
public function destroySession(){
    if (!isset($_SESSION)) {
    session_start();
    }
    if(isset($_SESSION['trenova_user']))
    {
        unset($_SESSION['trenova_user']);
        $info='info';
        if(isSet($_COOKIE[$info]))
        {
            setcookie ($info, '', time() - $cookie_time);
        }
        $msg="Logged Out Successfully...";
    }
    else
    {
        $msg = "Not logged in...";
    }
    return $msg;
}

public function purify($raw_value) {
    return $this->conn->real_escape_string($raw_value);
}

//function generates a random password
public function randomPassword() {
  $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
  $pass = array(); //remember to declare $pass as an array
  $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
  for ($i = 0; $i < 12; $i++) {
      $n = rand(0, $alphaLength);
      $pass[] = $alphabet[$n];
  }
  
return implode($pass); //turn the array into a string
}

//function generates a random numeric password
public function randomNumericPassword() {
  $alphabet = "0123456789";
  $pass = array(); //remember to declare $pass as an array
  $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
  for ($i = 0; $i < 12; $i++) {
      $n = rand(0, $alphaLength);
      $pass[] = $alphabet[$n];
  }
  
return implode($pass); //turn the array into a string
}

//log action
public function logAction($action) {
    $session = $this->getSession();
    $log_result = $this->insertToTable( 
        [
            $session['trenova_user']['ad_id'],
            $session['trenova_user']['ad_name'],
            $action
        ], 
        ['log_admin_id', 'log_admin_name', 'log_details'], 
        'admin_log'
    );
    return true;   
}
 
}



?>

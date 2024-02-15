<?php
session_start();
chdir(dirname(__FILE__));
class User{
	//class variables
	var $user_id = null;
	var $user_type = null;
	var $error = null;
	var $error_code = null;
	var $app_config = null;
	var $pdo = null;

public function __construct($user_id=null){
	$this->user_id = $user_id;
	$this->initializeDB();
	$this->pdo->setErrorCallbackFunction('logError');
	if(!empty($user_id)){
		$this->user_profile = $this->getUserDetails($user_id);
		//$this->user_type = $this->getUserType();
	}
	//some initialization stuff here
	$this->app_config = $this->getConfig();
}

function initializeDB(){
  include_once 'db_connect.php';
	try{
        $this->pdo = new db("mysql:host=$db_host;dbname=$db", $db_user, $db_pass);
	//$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (PDOException $e) {
        echo "Err: " . $e->getMessage();
        }
}

function __call($functionName, $argumentsArray ){
	//$log = debug_backtrace();
	//$this->createActionLog($log,0);
	$this->setError('undefined_function');
}

function getConfig(){
	#return $this->pdo->select('config');
}

/*
function setError() 
	assign error to the class variable $error.
*/
function setError($error){
	$this->error = $error;
}

/*public function authenticate($username, $password){
	$record = $this->pdo->select('borrowers','`userid`=\''.$username."' AND `categorycode`='STF'");
	if (password_verify($password, $record[0]['password'])) {
		$this->user_id = $record[0]['borrowernumber'];
		return true;
	} 
	return false;
}*/

// public function authenticate($username, $password)
// 	{
// 		//$record=$this->pdo->select('issuingrules');
// 		$query = 'select * from borrowers where userid="' . $username . '" AND categorycode="STF" ';
// 		$statment = $this->pdo->prepare($query);
// 		$statment->execute();
// 		while ($result = $statment->fetch(PDO::FETCH_ASSOC))  {
// 			$record[] = $result;
// 		}

// 		if (password_verify($password, $record[0]['password'])) {
// 			$this->user_id = $record[0]['borrowernumber'];
// 			return true;
// 		}else
// 		return false;
// 	}



public function authenticate($username, $password)
{
    $query = 'SELECT * FROM borrowers WHERE userid=:username AND categorycode="STF"';
    $statement = $this->pdo->prepare($query);
    $statement->bindParam(':username', $username, PDO::PARAM_STR);
    $statement->execute();
    $record = $statement->fetch(PDO::FETCH_ASSOC);

    if ($record) {
        if (password_verify($password, $record['password'])) {
            $this->user_id = $record['borrowernumber'];
            return true; // Successful authentication
        } else {
            // Incorrect Password
            return false;
        }
    } else {
        // No matching user with the given username and categorycode="STF"
        $queryCategory = 'SELECT * FROM borrowers WHERE userid=:username';
        $statementCategory = $this->pdo->prepare($queryCategory);
        $statementCategory->bindParam(':username', $username, PDO::PARAM_STR);
        $statementCategory->execute();
        $recordCategory = $statementCategory->fetch(PDO::FETCH_ASSOC);

        if ($recordCategory) {
            if ($recordCategory['categorycode'] !== "STF") {
                // Wrong categorycode
                return 'wrong_category'; 
            } 
        } else {
            return false;
        }
    }
}


public function getPatronDetails($cardnumber){
$record = $this->pdo->select('borrowers','cardnumber=\''.$cardnumber.'\'');
// print_r($record);
return $record[0];
}


public function getPatronCity()
{
$cities = $this->pdo->select('cities');
return $cities;


}
public function insertRegistrationData1($data){
#print_r($data);
if($data['borrowernumber']){
                $reg_inv_id = $this->createRegistrationInvoice1($data);
                $data_reg = $data;
$data_reg['date']=$data['dateenrolled'];
                $data_reg['amount'] = $data['rental_fee'];
                $data_reg['description']="Rental fee";
 $this->addDueAmount($data_reg);
#print_r($data_reg['description']);
#               $rental_fee_inv_id = $this->renewPlan($data_reg);
                // Pay rental/plan fee
#               $data_reg['accountlines_id'] = $rental_fee_inv_id;
                $this->addPayment1($data_reg);

                if($data['dd_fee'] > 0){
                $dd_inv_id = $this->createDDInvoice1($data);
                }
                // make payment of refundable deposit
                $this->payDeposit1($data);
                return true;
        }
}
public function insertRegistrationData($data){
#print_r($data);
/* */	# calculate expiry date OLD CODE starts here
if(!empty($data['enrolment_period2']))
{
$data['enrolment_period']=$data['enrolment_period2'];

}
	$reg_date = date_create($data['dateenrolled']);
	$data['dateexpiry'] = date_format($dateexpiry, 'Y-m-d');
$data['date']=$data['dateenrolled'];

	//  print_r($data['borrowernumber']);
	try{
		$this->pdo->insert('borrowers',$data);
		$b_id = $this->pdo->lastInsertId();
		
	}
	catch(PDOException $e){
                $this->setError($e->getMessage());
		echo $e->getMessage();
                return false;
		exit();
        }
	if(!empty($b_id)){

		$data['borrowernumber'] = $b_id;
/*	*/	
		$data['note'] = $data['payment_mode'] .' - '.$data['particulars'];
		/*
		registration_fee, rental_fee, dd_fee, refundable_deposit
		*/
$reg_inv_id = $this->createRegistrationInvoice($data);
if(!empty($data['discount'])) 
{
$data['amount']=$data['discount'];
$data['description']="Discount";
 $this->addPayment($data);
#$data['description'] = "Plan renewed for ".$data['enrolment_period']." months ".$data['description']."";
}
		$data_reg = $data;
		$data_reg['amount'] = $data['rental_fee'];
		$data_reg['description']="Rental fee";
	        $rental_fee_inv_id = $this->renewPlan($data_reg);
		// Pay rental/plan fee
		$data_reg['accountlines_id'] = $rental_fee_inv_id;
		$this->addPayment($data_reg);
		
		if($data['dd_fee'] > 0){
		$dd_inv_id = $this->createDDInvoice($data);
		}
		// make payment of refundable deposit
		$this->payDeposit($data);
		return true;
	}

	$this->setError("Registration Unsuccessful");
	return false;

}






/*
public function insertRegistrationData($data){
/*	# calculate expiry date
	$reg_date = date_create($data['dateenrolled']);
#	$data['dateexpiry'] = date_format($dateexpiry, 'Y-m-d');
	//  print_r($data['borrowernumber']);
	try{
	print($data);
		$this->pdo->insert('borrowers',$data);
		$b_id = $this->pdo->lastInsertId();
		
	}
	catch(PDOException $e){
                $this->setError($e->getMessage());
		echo $e->getMessage();
                return false;
		exit();
        }
	if(!empty($b_id)){

		$data['borrowernumber'] = $b_id;
/*		
		$data['note'] = $data['payment_mode'] .' - '.$data['particulars'];
		/*
		registration_fee, rental_fee, dd_fee, refundable_deposit
		*/
/*
if($data['borrowernumber']){
		$reg_inv_id = $this->createRegistrationInvoice($data);
		$data_reg = $data;
		$data_reg['amount'] = $data['rental_fee'];
		$data_reg['description']="Rental fee";
	        $rental_fee_inv_id = $this->renewPlan($data_reg);
		// Pay rental/plan fee
		$data_reg['accountlines_id'] = $rental_fee_inv_id;
		$this->addPayment($data_reg);
		
		if($data['dd_fee'] > 0){
		$dd_inv_id = $this->createDDInvoice($data);
		}
		// make payment of refundable deposit
		$this->payDeposit($data);
		return true;
	}

	$this->setError("Registration Unsuccessful");
	return false;

}
*/
public function payDeposit($data){
		$deposit_data = $data;
	        $deposit_data['date'] = date('Y-m-d');
       		$deposit_data['accountno'] = 2;// 1 for all dues
        	$deposit_data['accounttype'] = 'Pay';// Pay for all Payments
        	// set new outstanding amount
        	$deposit_data['amount']=$data['refundable_deposit'];
        	$deposit_data['lastincrement'] = $deposit_data['amount'];
        	$deposit_data['description'] ="Refundable deposit";
$reg_iinv_id = $this->addDueAmount($deposit_data);
$deposit_data['deposit_id']= $reg_iinv_id;

        #	$deposit_data['accountlines_id'] = null;
		$this->addPayment($deposit_data);
}

public function payDeposit1($data){
                $deposit_data = $data;
                $deposit_data['date'] =$data['dateenrolled'];
                $deposit_data['accountno'] = 2;// 1 for all dues
                $deposit_data['accounttype'] = 'Pay';// Pay for all Payments
                // set new outstanding amount
                $deposit_data['amount']=$data['refundable_deposit'];
                $deposit_data['lastincrement'] = $deposit_data['amount'];
                $deposit_data['description'] ="Refundable deposit";
$reg_iinv_id = $this->addDueAmount($deposit_data);
$deposit_data['deposit_id']= $reg_iinv_id;

        #       $deposit_data['accountlines_id'] = null;
                $this->addPayment1($deposit_data);
}

public function createRegistrationInvoice($data){
                $data_reg = $data;
                $reg_pay_data['date'] = date('Y-m-d');
#print_r($reg_pay_data['date']);
                $data_reg['amount'] = $data['registration_fee'];
                $data_reg['description'] = "Registration fee";
                $data_reg['accountno'] = 1;// 1 for all dues
                $data_reg['accounttype'] = 'R';// R for registration
                // set new outstanding amount
#                $data_reg['amountoutstanding'] = $data['registration_fee'];
                $data_reg['lastincrement'] = $data['registration_fee'];
                $reg_inv_id = $this->addDueAmount($data_reg);
                // make payment
                $reg_pay_data = $data;
                $reg_pay_data['date'] = date('Y-m-d');
                $reg_pay_data['accountno'] = 2;// 1 for all dues
                $reg_pay_data['accounttype'] = 'Pay';// Pay for all Payments
                // set new outstanding amount
                $reg_pay_data['amount']=$data['registration_fee'];
$reg_pay_data['description']="Registration Fee";
                $reg_pay_data['lastincrement'] = $reg_pay_data['amount'];
                $reg_pay_data['accountlines_id'] = $reg_inv_id;
                $this->addPayment($reg_pay_data);
}



public function createRegistrationInvoice1($data){
		$data_reg = $data;
$data_reg['date']=$data['dateenrolled'];
		$reg_pay_data['date'] = $data_reg['dateenrolled'];
		$data_reg['amount'] = $data['registration_fee'];
		$data_reg['description'] = "Registration fee";
		$data_reg['accountno'] = 1;// 1 for all dues
		$data_reg['accounttype'] = 'R';// R for registration
		// set new outstanding amount
	#	$data_reg['amountoutstanding'] = $data['registration_fee'];
		$data_reg['lastincrement'] = $data['registration_fee'];
		$reg_inv_id = $this->addDueAmount($data_reg);
		// make payment
		$reg_pay_data = $data;
	        $reg_pay_data['date'] =$data['dateenrolled'];
       		$reg_pay_data['accountno'] = 2;// 1 for all dues
        	$reg_pay_data['accounttype'] = 'Pay';// Pay for all Payments
        	// set new outstanding amount
$reg_pay_data['amount']=$data['registration_fee'];
        	$reg_pay_data['amount']=$data['registration_fee'];
        	$reg_pay_data['lastincrement'] = $reg_pay_data['amount'];
        	$reg_pay_data['accountlines_id'] = $reg_inv_id;
		$this->addPayment1($reg_pay_data);
}

public function createDDInvoice($data){
		$data_dd = $data;
		$data_dd['amount'] = $data['dd_fee'];
		$data_dd['description'] = "Door Delivery fee";
		$data_dd['accountno'] = 1;// 1 for all dues
		$data_dd['accounttype'] = 'DD';// R for registration
		// set new outstanding amount
		$data_dd['amountoutstanding'] = $data['dd_fee'];
		$data_dd['lastincrement'] = $data_dd['amount'];
		$dd_inv_id = $this->addDueAmount($data_dd);
		// make payment
		$dd_pay_data = $data;
	        $dd_pay_data['date'] = date('Y-m-d');
       		$dd_pay_data['accountno'] = 2;// 1 for all dues
        	$dd_pay_data['accounttype'] = 'Pay';// Pay for all Payments
        	// set new outstanding amount
        	$dd_pay_data['amount']=$data_dd['amount'];
        	$dd_pay_data['lastincrement'] = $data_dd['amount'];
        	$dd_pay_data['accountlines_id'] = $dd_inv_id;

		$this->addPayment($dd_pay_data);
}


public function createDDInvoice1($data){
                $data_dd = $data;
                $data_dd['amount'] = $data['dd_fee'];
                $data_dd['description'] = "Door Delivery fee";
                $data_dd['accountno'] = 1;// 1 for all dues
                $data_dd['accounttype'] = 'DD';// R for registration
                // set new outstanding amount
                $data_dd['amountoutstanding'] = $data['dd_fee'];
                $data_dd['lastincrement'] = $data_dd['amount'];
                $dd_inv_id = $this->addDueAmount($data_dd);
                // make payment
                $dd_pay_data = $data;
                $dd_pay_data['date'] =$data['dateenrolled'];
                $dd_pay_data['accountno'] = 2;// 1 for all dues
                $dd_pay_data['accounttype'] = 'Pay';// Pay for all Payments
                // set new outstanding amount
                $dd_pay_data['amount']=$data_dd['amount'];
                $dd_pay_data['lastincrement'] = $data_dd['amount'];
                $dd_pay_data['accountlines_id'] = $dd_inv_id;

                $this->addPayment1($dd_pay_data);
}





public function createRenewalInvoice($data){
		$data_dd = $data;
		
		$data_dd['description'] = "Renewal Charges";
		$data_dd['accountno'] = 1;// 1 for all dues
		$data_dd['accounttype'] = 'R';// R for registration
		$data_dd['date'] = date('Y-m-d');
		// set new outstanding amount
		$data_dd['amountoutstanding'] = $data['dd_fee'];
		$data_dd['lastincrement'] = $data_dd['amount'];
		$dd_inv_id = $this->addDueAmount($data_dd);
		
		// make payment
		$dd_pay_data = $data;
	        $dd_pay_data['date'] = date('Y-m-d');
       		$dd_pay_data['accountno'] = 2;// 1 for all dues
        	$dd_pay_data['accounttype'] = 'Pay';// Pay for all Payments
        	// set new outstanding amount
        	$dd_pay_data['amount']=$data_dd['amount'];
        	$dd_pay_data['lastincrement'] = $data_dd['amount'];
        	$dd_pay_data['accountlines_id'] = $dd_inv_id;
		$dd_pay_data['note'] = $data['payment_mode'] .' - '.$data['particulars'];
#print_r($data);
		$this->addPayment($dd_pay_data);
		//$old_exp_date = date_create($data['dateexpiry']);
		#$dateexpiry = date_add($old_exp_date, date_interval_create_from_date_string($data['enrolment_period']." months"));
//echo $old_exp_date;
//exit;

		$this->pdo->update('borrowers', array('dateexpiry'=>$data['dateexpiry']), 'borrowernumber='.$data['borrowernumber']);
	$this->pdo->update('', array('dateexpiry'=>$data['dateexpiry']), 'borrowernumber='.$data['borrowernumber']);
}
public function createPlanChangeInvoice($data){

		$data_dd = $data;
		//exit();
		$data_dd['description'] = "Plan change";
		$data_dd['accountno'] = 1;// 1 for all dues
		$data_dd['accounttype'] = 'R';// R for registration
		$data_dd['date'] = date('Y-m-d');
		
		// set new outstanding amount
		$data_dd['amountoutstanding'] = $data['dd_fee'];
		$data_dd['lastincrement'] = $data_dd['amount'];
		$dd_inv_id = $this->addDueAmount($data_dd);
		
		// make payment
		$dd_pay_data = $data;
	        $dd_pay_data['date'] = date('Y-m-d');
       		$dd_pay_data['accountno'] = 2;// 1 for all dues
        	$dd_pay_data['accounttype'] = 'Pay';// Pay for all Payments
		
        	// set new outstanding amount
        	$dd_pay_data['amount']=$data_dd['amount'];
		$dd_pay_data['amount'];
        	$dd_pay_data['lastincrement'] = $data_dd['amount'];
        	$dd_pay_data['accountlines_id'] = $dd_inv_id;
		

		$dd_pay_data['note'] = $data['payment_mode'] .' - '.$data['particulars'];

		$this->addPayment($dd_pay_data);
		$this->pdo->update('borrowers', array('dateexpiry'=>$data['dateexpiry']), 'borrowernumber='.$data['borrowernumber']);
		$this->pdo->update('borrowers',array('categorycode'=>$data['categorycode']), 'borrowernumber= '.$data['borrowernumber']);
}

public function renewPlan($data){
	$old_exp_date = date_create($data['dateexpiry']);
	$dateexpiry = date_add($old_exp_date, date_interval_create_from_date_string($data['enrolment_period']." months"));
	$data['dateexpiry'] = date_format($dateexpiry, 'Y-m-d');
	// get monthly plan fee
	$categories = $this->getCategories('categorycode');

	if($this->pdo->update('borrowers', $data, '`borrowernumber` = '.$data['borrowernumber'])){
		$data['amount'] = $categories[$data['categorycode']]['enrolmentfee'] * $data['enrolment_period'];
		$data['date'] = date('Y-m-d');
		$data['accountno'] = 1;// 1 for all dues
		$data['accounttype'] = 'A';// A for all renewals
		// set new outstanding amount
	#	$data['amountoutstanding'] = $data['amount'];
		$data['lastincrement'] = $data['amount'];
		return $this->addDueAmount($data);
	}return false;
	}
/*function dateDifference($date('Y-m-d'),$data['dateexpiry'])
	{
	$datetime1 = date_create($date(('Y-m-d'));
	$datetime2 = date_create($data['dateexpiry']);
	$interval = date_diff($datetime1, $datetime2);
	echo $interval->format($differenceFormat);
	}
 }
/*
public function getAmountOutstanding($borrowernumber){
	$last_record = $this->pdo->select('accountlines','`borrowernumber` = '.$borrowernumber.' ORDER BY accountlines_id DESC LIMIT 1');
	return $last_record[0]['amountoutstanding'];	
}
*/
public function addDueAmount($data){
	if($this->pdo->insert('accountlines', $data))
	$addDue=$this->pdo->lastInsertId();
}
public function insertCsv($borrowernumber)
{
return $this->pdo->insert('accountlines','borrowernumber=\''.$borrowernumber.'\'');
 return $this->pdo->lastInsertId();

}




public function addPayment($data){
#$data['description'] = "Plan renewed for ".$data['enrolment_period']." months";
$data['description'] = "Plan renewed for ".$data['enrolment_period']." months (".$data['description'].")";

	$data['date'] = date('Y-m-d');
	$data['accountno'] = 2;// 1 for all dues
	$data['accounttype'] = 'Pay';// Pay for all Payments
	// set new outstanding amount

	$data['amount']=-1*$data['amount'];
	$data['lastincrement'] = $data['amount'];
	if(!empty($data['accountlines_id'])){
		$this->removeOutstandingAmount($data['accountlines_id']);
	}
	$data['accountlines_id'] = null;
	try{
		$this->pdo->insert('accountlines', $data);
		return true;
	}
	catch(PDOException $e){
		echo $e->getMessage();
		return false;
	}
}


public function addPayment1($data){
$data['description'] = "Plan renewed for ".$data['enrolment_period']." months";

        $data['date'] = $data['dateenrolled'];
        $data['accountno'] = 2;// 1 for all dues
        $data['accounttype'] = 'Pay';// Pay for all Payments
        // set new outstanding amount

        $data['amount']=-1*$data['amount'];
        $data['lastincrement'] = $data['amount'];
        if(!empty($data['accountlines_id'])){
                $this->removeOutstandingAmount($data['accountlines_id']);
        }
        $data['accountlines_id'] = null;
        try{
                $this->pdo->insert('accountlines', $data);
                return true;
        }
        catch(PDOException $e){
                echo $e->getMessage();
                return false;
        }
}



public function makePayment($data){
                $data['description'] = "Plan renewed for ".$data['enrolment_period']." months";
echo "<br />";
	  $data['date'] = date('Y-m-d');
        $data['accountno'] = 2;// 1 for all dues
        $data['accounttype'] = 'Pay';// Pay for all Payments
	
	 $data['lastincrement'] = $data['amount'];
        try{
                $this->pdo->insert('accountlines', $data);
        if($this->pdo->update('borrowers', $data, '`borrowernumber` = '.$data['borrowernumber'])){
$data['date'] = date('Y-m-d');
}

                return true;
        }
        catch(PDOException $e){
                echo $e->getMessage();
                return false;
        }
}

public function makePayment1($data){
                $data['description'] = "Plan renewed for ".$data['enrolment_period']." months";
echo "<br />";
          $data['date'] = date('Y-m-d');
        $data['accountno'] = 2;// 1 for all dues
        $data['accounttype'] = 'Pay';// Pay for all Payments

         $data['lastincrement'] = $data['amount'];
        try{
                $this->pdo->insert('accountlines', $data);
        if($this->pdo->update('borrowers', $data, '`borrowernumber` = '.$data['borrowernumber'])){
$data['date'] = date('Y-m-d');
}

                return true;
        }
        catch(PDOException $e){
                echo $e->getMessage();
                return false;
        }
}




public function cancel($accountlines_id){
return $this->pdo->delete('accountlines', '`accountlines_id` = '.$accountlines_id);
 }

public function deleteAccountlies($borro,$date)
{
$statement = $this->pdo->prepare("delete from accountlines where borrowernumber=? AND timestamp !=?");
 $statement->execute(array($borro,$date));

return $select;
//return $statement;



}






 public function getAmount($accountlines_id){
	return $this->pdo->select('accountlines', '`accountlines_id` ='.$accountlines_id);
}
#function for last 10 entries of accountlines table for particuler borrower
public function getUnpaidInvoices($borrowernumber){

	return $this->pdo->select('accountlines', '`borrowernumber` ='.$borrowernumber.' AND `accounttype` = "Pay" ORDER BY date DESC LIMIT 10 ');
}
public function getAccountlines($accountlines_id){
return $this->pdo->select('accountlines', '`accountlines_id` = '.$accountlines_id);
}

public function removeOutstandingAmount($accountlines_id){
	$this->pdo->update('accountlines', array('amountoutstanding'=>0), 'accountlines_id = '.$accountlines_id);
}

public function getCategories($key=null){

	if(empty($key)){
		return $this->pdo->select('categories');
	}
	else if($key='categorycode'){
		$cats = $this->pdo->select('categories');
		$categories = array();
		foreach ($cats as $c){
			$categories[$c['categorycode']] = $c;
		}		
		return $categories;
	}
}
###################################
public function authenticateLocal($username, $password){

	try{
		$select = $this->pdo->prepare("SELECT id FROM emp_list WHERE username = ? AND password = ? AND status = 1");
		$select->execute(array($username, md5($password)));
	}
	catch(PDOException $e){
		$this->setError($e->getMessage());
		return false;
	}
	$row = $select->fetch(PDO::FETCH_ASSOC);
	if($row['id']){
		$this->user_id = $row['id'];
		$user_profile = $this->getUserDetails($row['id']);
		$this->user_type = $user_profile['user_type'];
		$action_details=array();
/*
		$action_details['table_name']='users';
		$action_details['row_id']=$this->user_id;
		$action_details['operation']='Logged In';
		$this->createActionLog($action_details);
*/
		return true;
	}
	$this->setError("Invalid username or password.");
	return false;
}

/* 
    Create New User Single Sign On
*/

public function getUserIdFromUsername($username){
	
	try{
	$select = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
	$select->execute(array($username));
	}
	catch(PDOException $e){
	$this->setError($e->getMessage());
	return false;
	}
	$row = $select->fetch(PDO::FETCH_ASSOC);
	return $row['id'];
}

/* function logout
	added for activity logging purpose
*/

function logout(){
	$_SESSION['user_id'] = null;
	return true;
}

/*
function isAdmin() 
	return true if logged in user type is Admin other wise return false with error message.
*/
	function isAdmin(){

		if($this->user_type == 'Admin'){
			return true;
		}
		return false;
	}

public	function isManager(){

		if($this->user_type == 'Manager'){
			return true;
		}
		return false;
	}
	function isEmployee(){

		if($this->user_type == 'Employee'){
			return true;
		}
		return false;
	}
public	function isTravelDesk(){
		if($this->user_type == 'Travel Desk'){
			return true;
		}
		return false;
	}
/*
function getUserType() 
	return user type of logged in user.
*/
function getUserType(){
	//return $this->user_profile['user_type'];
	$select = $this->pdo->prepare("SELECT user_types.type FROM user_types, emp_list
	WHERE user_types.id = emp_list.user_type
	AND emp_list.id = ?");
	$select->execute(array($this->user_id));
	$row = $select->fetch(PDO::FETCH_ASSOC);
//echo $row['type'];
	return $row['type'];	
}

    function _loginRedirect(){
        	// send user to the login page
        	header("Location:index.php");
    }
    
public function getUserDetails($userid){
	$records = $this->pdo->select('borrowers', '`borrowernumber`='.$this->user_id);
	return $records[0];
}
	
function aasort (&$array, $key){
	$sorter=array();
	$ret=array();
	reset($array);
	foreach ($array as $ii => $va){
		$sorter[$ii]=$va[$key];
	}
	asort($sorter);
	foreach ($sorter as $ii => $va){
	$ret[$ii]=$array[$ii];
	}
	$array=$ret;
}
function categoryDesc($categorycode)
{
$data=$this->pdo->select('categories', '`categorycode`="'.$categorycode.'"');
return $data;
}

function getAllUsers(){
	$per_page=10;
	$p =1;
	$offset = ($p - 1) * $per_page;
	$select = "SELECT * ,users.id as user_id, user_types.type AS user_type FROM users 
		LEFT JOIN user_types ON user_types.id=users.type WHERE username !='cron'";
		//LIMIT $offset, $per_page";
		
		$res = $this->pdo->query($select);
		return $res->fetchAll(PDO::FETCH_ASSOC);
}

function getUserTypes(){
	return $this->pdo->select('user_types');
}


public function sendSMTPEmail($to, $email_subject, $email_body){
if(empty($to)){return false;}
############ Send mail by SMTP
$app_config = $this->app_config;
$mail = new PHPMailer(true);
$mail->IsSMTP(); // set mailer to use SMTP
//$mail->SMTPDebug = 1;  // debugging: 1 = errors and messages, 2 = messages only
$mail->SMTPAuth = true;
$mail->SMTPKeepAlive = true;
$mail->SMTPSecure = $app_config['smtp_protocol'];
$mail->Port = $app_config['smtp_port'];
//echo $app_config['smtp_host'];
$mail->Host = $app_config['smtp_host']; // specify main and backup server
$mail->Username = $app_config['smtp_username'];
$mail->Password = $app_config['smtp_password'];
$mail->From = $app_config['smtp_from'];
$mail->FromName = $app_config['smtp_fromname'];
$mail->AddAddress($to);   // name is optional
$mail->IsHTML(false);    // set email format to HTML
$mail->Subject = $email_subject;
$mail->Body = $email_body;
try{
    $mail->Send();
}
catch(Exception $e){
	logMessage($to.' | '.$email_subject. ' | '.$e->getMessage());
	return false;
}
return true;
}

public function getMembers($data){
#print_r($data);
	return $this->pdo->select('borrowers',"cardnumber LIKE '%$data[search]%' OR surname LIKE '%$data[search]%'");
}
public function getBorrower($cardnumber){
$record = $this->pdo->select('borrowers','cardnumber=\''.$cardnumber.'\'');
return $record;

}




function getInUser(){
	$username = $_SESSION['user_id'];
	
		$q1 = 'SELECT b.userid
			   FROM borrowers b
			   WHERE b.borrowernumber = :username';
	
		$stmt = $this->pdo->prepare($q1);
		$stmt->bindParam(':username', $username, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
	
		// Now, $result['userid'] contains the userid for the given username
		$userid = $result['userid'];
		return $userid;
		}


public function insertCardnumber($cardnumber,$purpose){
	//echo $purpose;
	$loggedinuser = $this->getInUser();
// $query='select cardnumber from facility_attendance where out_time IS NULL and loggedinuser !="'.$loggedinuser.'" and cardnumber="'.$cardnumber.'"'; 
	
	//var_dump($purpose);
$record = $this->pdo->select('borrowers','cardnumber=\''.$cardnumber.'\'');
$in_time= date("Y-m-d H:i:s");  
$record_inmembers=$this->pdo->select('facility_attendance','cardnumber=\''.$cardnumber.'\'');
//print_r($record_inmembers);

if($record[0]['cardnumber']==$cardnumber){
	$this->pdo->insert('facility_attendance',array('cardnumber'=>$record[0]['cardnumber'],'borrowernumber'=>$record[0]['borrowernumber'],'surname'=>$record[0]['surname'],'firstname'=>$record[0]['firstname'],'purpose'=>$purpose,'in_time'=>$in_time,'loggedinuser'=>$loggedinuser));
	//return $record;
	$msg = "IN";
	return $msg;

}else{
	$msg = "NOT";
	return $msg;
echo '<script type="text/javascript">alert("Cardnumber Not Valid");</script>';
}

 }

 
/*
public function insertCardnumber($cardnumber){
$record = $this->pdo->select('borrowers','cardnumber=\''.$cardnumber.'\'');
$in_time= date("Y-m-d H:i:s");
$record_inmembers=$this->pdo->select('facility_attendance','cardnumber=\''.$cardnumber.'\'');
print_r($record_inmembers);

if($record[0]['cardnumber']==$cardnumber && $record_inmembers[0]['cardnumber']!=$cardnumber){
$this->pdo->insert('facility_attendance',array('cardnumber'=>$record[0]['cardnumber'],'borrowernumber'=>$record[0]['borrowernumber'],'in_time'=>$in_time));
//return $record;
}elseif($record[0]['cardnumber']!=$cardnumber ){
echo '<script type="text/javascript">alert("Cardnumber Not Valid");</script>';
}elseif($record_inmembers[0]['cardnumber']==$cardnumber && empty($record_inmembers[0]['out_time'])){
echo '<script type="text/javascript">alert("Cardnumber Already In Library");</script>';

}

 }

*/


/*public function getInMembers(){
#print_r($data) i
       return  $this->pdo->select('facility_attendance');


}*/

public function getOutMembers($cardnumber,$purpose){
	$userid = $this->getInUser();


	$q1 = 'SELECT loggedinuser
			   FROM facility_attendance
			   WHERE cardnumber = :cardnumber
			   AND out_time IS NULL';
	
		$stmt = $this->pdo->prepare($q1);
		$stmt->bindParam(':cardnumber', $cardnumber, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
	
		// Now, $result['userid'] contains the userid for the given username
		$loggedinuser1 = $result['loggedinuser'];	

	$out_time= date("Y-m-d H:i:s");
	$record = $this->pdo->select('borrowers','cardnumber=\''.$cardnumber.'\'');
	if($record[0]['cardnumber']==$cardnumber){
		if($loggedinuser1!=$userid){
		$query='update facility_attendance set out_time="'.$out_time.'" where cardnumber="'.$cardnumber.'" and out_time IS NULL';
		$statment = $this->pdo->prepare($query);
		$final_result=$statment->execute();
		$msg='OUT1';
		
			$this->insertCardnumber($cardnumber, $purpose);
			return $msg;
			
		}else{
			$query1='update facility_attendance set out_time="'.$out_time.'" where cardnumber="'.$cardnumber.'" and out_time IS NULL';
		$statment1 = $this->pdo->prepare($query1);
		$final_result=$statment1->execute();
		$msg='OUT';
		return $msg;
			
		}
	}
	else{
		$msg='NOT';
	return $msg;
		echo '<script type="text/javascript">alert("Cardnumber Not Valid");</script>';
	}
}


// public function getOutMembers($cardnumber){
// 	$userid = $this->getInUser();
// 	$out_time= date("Y-m-d H:i:s");
// 	$record = $this->pdo->select('borrowers','cardnumber=\''.$cardnumber.'\'');
// 	if($record[0]['cardnumber']==$cardnumber){
// 	$query='update facility_attendance set out_time="'.$out_time.'" where cardnumber="'.$cardnumber.'" and out_time IS NULL';
// 	$statment = $this->pdo->prepare($query);
// 	$final_result=$statment->execute();
// 		$msg='OUT';
// 	return $msg;
// 	}
// 	else{
// 		$msg='NOT';
// 	return $msg;
// 		echo '<script type="text/javascript">alert("Cardnumber Not Valid");</script>';
// 	}
// }



function checkIn($cardnumber)
{
	$query='select cardnumber from facility_attendance where cardnumber="'.$cardnumber.'" and out_time IS NULL';
	$statment = $this->pdo->prepare($query);
	$statment->execute();
        while($result = $statment->fetch(PDO::FETCH_ASSOC)){
                $final_result[]=$result;
        }
	//print_r($final_result);
	return $final_result;
}	


function checkOut($cardnumber)
{
	$query='select cardnumber from facility_attendance where out_time IS NULL  ORDER BY id DESC  ';
	$statment = $this->pdo->prepare($query);
	$statment->execute();
        while($result = $statment->fetch(PDO::FETCH_ASSOC)){
                $final_result[]=$result;
        }
	return $final_result;
}



function getBranchcode()
{
	$username1 = $_SESSION['user_id'];

    $q2 = 'SELECT bs.branchname
	FROM borrowers b
	JOIN branches bs ON b.branchcode = bs.branchcode
	WHERE b.borrowernumber = :username1';

    $stmt = $this->pdo->prepare($q2);
    $stmt->bindParam(':username1', $username1, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the query executed successfully
    if ($result) {
        // Now, $result['branchcode'] contains the branchcode for the given username
        $branchcode1 = $result['branchname'];
        echo "<script>console.log('Currently logged in as a: $branchcode1');</script>";
        return $branchcode1;
    } else {
        // Handle the case where no result is found
        echo "Error: Unable to retrieve branchcode.";
        return false;
    }
}


function getInMembers() {
    $userid = $this->getInUser();
    echo "<script>console.log('Currently logged in as: $userid');</script>";

    $query = 'SELECT fa.*
              FROM facility_attendance fa
              WHERE fa.loggedinuser = :userid
                AND fa.out_time IS NULL
              ORDER BY fa.id DESC';

    $statement = $this->pdo->prepare($query);
    $statement->bindParam(':userid', $userid, PDO::PARAM_STR);
    $statement->execute();

    $final_result = array();
    while ($result = $statement->fetch(PDO::FETCH_ASSOC)) {
        $final_result[] = $result;
    }

    // print_r($final_result);
    return $final_result;
}


public function autoLogout(){

	//$out_time= date("Y-m-d H:i:s");
	$query='update facility_attendance set out_time=now() where out_time is null';
	$statment = $this->pdo->prepare($query);
	$final_result=$statment->execute();
	//echo "date updated";
	return $final_result;
}

function  getInMembersCounts($where,$cardnumber){
	// $query='select count(*) from facility_attendance where '.$where.'';
	$userid = $this->getInUser();

	if(empty($cardnumber)){
		$query='select count(*) from facility_attendance where '.$where.' AND loggedinuser = :userid';
		error_log($query.'if');
		$statment = $this->pdo->prepare($query);
		$statment->bindParam(':userid', $userid, PDO::PARAM_STR);
		$statment->execute();
				while($result = $statment->fetch(PDO::FETCH_ASSOC)){
						$final_result[]=$result;
		
				}
		//print_r($final_result);
		$count = $final_result[0]['count(*)'];
	}else{
		$query='select firstname,surname from borrowers where cardnumber="'.$cardnumber.'" limit 1';
		error_log($query.'else' );
		$statment = $this->pdo->prepare($query);
		$statment->execute();
				while($result = $statment->fetch(PDO::FETCH_ASSOC)){
						$final_result[]=$result;
		
				}
		$data = $final_result[0];
		 $count = $data['firstname'] . ' ' . $data['surname'];

		// if (empty($data['firstname'])) {
		// 	$surnameWords = explode(' ', $data['surname']);
		// 	$data['firstname'] = $surnameWords[0];
		// }
		// else{
		// 	$surnameWords = explode(' ', $data['firstname']);
		// 	$data['firstname'] = $surnameWords[0];
		// }
		
		// $count=$data['firstname'];
		
	}
	return $count;
	}


	public function checkPatronPresent($cardnumber){
		$record = $this->pdo->select('borrowers','cardnumber=\''.$cardnumber.'\'');
		//  print_r(count($record));
		if (count($record) > 0) {
			return 1;
		} else {
			return 0;
		}
		
	}





// function  out(){
// 	$query='select out_time from facility_attendance where out_time IS NULL  ORDER BY id DESC';
// 	$statment = $this->pdo->prepare($query);
// 	$statment->execute();
//         while($result = $statment->fetch(PDO::FETCH_ASSOC)){
//                 $final_result[]=$result;

//         }
// //print_r($final_result);
// return $final_result;

// }


################################################
}//User class ends here

function logError($error){
#	echo "$error";
}

// function  getLoginMembers(){
// 	$query='SELECT userid,password FROM borrowers WHERE categorycode="STF"';
// 	$statment = $this->pdo->prepare($query);
// 	$statment->execute();
// 			while($result = $statment->fetch(PDO::FETCH_ASSOC)){
// 					$final_result[]=$result;
	
// 			}
// 	//print_r($final_result);
// 	return $final_result;
// 	}



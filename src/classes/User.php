<?php

namespace classes;
use PDO;

class User extends reSlimHandlers {

	protected $db;

	var $Username,$Fullname,$Address,$Phone,$Email,$Aboutme,$Avatar,$Role,$Status;

	function __construct($db=null) {
		if (!empty($db)) 
        {
            $this->db = $db;
        }
	}
	
	// Get all data from database mysql
	public function getAll() {
		$r = array();		

		$sql = "SELECT a.Username, a.Fullname, a.Address, a.Phone, a.Email, b.Role , c.Status
			FROM user_data a 
			INNER JOIN user_role b ON a.RoleID = b.RoleID
			INNER JOIN core_status c ON a.StatusID = c.StatusID
			ORDER BY a.Fullname ASC;";
		$stmt = $this->db->prepare($sql);		

		if ($stmt->execute()) {	
            if ($stmt->rowCount() > 0){
                $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            else{
                $r = 0;
            }          	   	
		} else {
			$r = 0;
		}		
        
		return $r;
        $stmt->Close();
	}

	// Regiter new user
	public function register(){

		if (strtolower($this->Role) == 'admin'){
			$newRole = '1';
		}else{
			$newRole = '2';
		}

		$sql = "INSERT INTO user_data (Username,Fullname,Address,Phone,Email,Aboutme,Avatar,RoleID,StatusID) 
		VALUES (:username,:fullname,:address,:phone,:email,:aboutme,:avatar,:role,'1');";
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':username', $this->Username, PDO::PARAM_STR);
		$stmt->bindParam(':fullname', $this->Fullname, PDO::PARAM_STR);
		$stmt->bindParam(':address', $this->Address, PDO::PARAM_STR);
		$stmt->bindParam(':phone', $this->Phone, PDO::PARAM_STR);
		$stmt->bindParam(':email', $this->Email, PDO::PARAM_STR);
		$stmt->bindParam(':aboutme', $this->Aboutme, PDO::PARAM_STR);
		$stmt->bindParam(':avatar', $this->Avatar, PDO::PARAM_STR);
		$stmt->bindParam(':role', $newRole, PDO::PARAM_STR);
		if ($stmt->execute()) {
			$data = [
				'status' => 'success',
				'code' => 'RS101',
				'message' => $this->getreSlimMessage('RS101')
			];	
		} else {
			$data = [
				'status' => 'error',
				'code' => '0',
				'message' => 'Failed to register!'
			];
		}
		return json_encode($data,JSON_PRETTY_PRINT);
	}

	// Login user
	public function login(){
		
	}

	// Logout user
	public function logout(){
		
	}

	// Auth User
	private function auth(){
		
	}

	// Update User
	public function update(){
		
	}

	// Delete User
	public function delete(){
		
	}
}
<?php
class modelUtility extends CI_Model {

	var $valid = false;

	public function __construct(){
      parent::__construct();
      $this->load->helper('accesscontrol');
      LoggedSystem();
	}

	public function saveAdmin($args){

			$this->db->set("password", $args->password);
			$this->db->set("email", $args->email);

			if(!empty($args->id)) {
				$action = "update";
				$this->db->where("id", $args->id);
				$this->valid = $this->db->update("admintable");
			}
			else {
				$this->db->set("username", $args->username);
				$this->valid = $this->db->insert("admintable");
			}

		return $this->valid;
	}

	public function saveUser($args){

			$this->db->set("password", $args->password);
			$this->db->set("email", $args->email);
			$this->db->set("type", $args->type);

			if($args->type === "retailer") {
				$this->db->set("retail_id", $args->id_retailer);
			}

			if(!empty($args->id)) {
				$action = "update";
				$this->db->where("id", $args->id);
				$this->valid = $this->db->update("usertable");
			}
			else {
				$this->db->set("username", $args->username);
				$this->valid = $this->db->insert("usertable");
			}

		return $this->valid;
	}


	public function changePassword($args){
		$table = "usertable";
		if($this->session->userdata("sanitasType") === "admin") {
			$table = "admintable";
		}

		$this->db->set("password", $args->pass1);
		$this->db->where("id", $this->session->userdata("sanitasUserId"));
		$this->valid = $this->db->update($table);

		if($this->valid) {
			$session['sanitasPassword'] = $args->pass1;
			$this->session->set_userdata($session);
		}

		return $this->valid;
	}



}
?>

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

			$this->db->set("password", md5($args->pass1));
            if($args->level === "asm") {
                $this->db->set("id_distributor", $args->id_distributor);
			}
            $this->db->set("level", $args->level);

			if(!empty($args->id)) {
				$action = "update";
				$this->db->where("id_user", $args->id);
				$this->valid = $this->db->update("mst_user");
			}
			else {
				$this->db->set("username", $args->username);
				$this->valid = $this->db->insert("mst_user");
			}

		return $this->valid;
    }
    
    public function saveCode($args){
        $this->db->set("prefix_char", $args->prefix_char);
        $this->db->set("last_number", $args->last_number);
        
        $isAuto = "yes";
        if(!empty($args->is_auto)) {
            $isAuto = "no";
        }        
        
        $this->db->set("is_auto", $isAuto);
        $this->db->where("id", $args->id);
        $this->valid = $this->db->update("reff_number_series");
		return $this->valid;
	}
	
	public function saveEmailSetup($args) {
		$this->db->set("email_setup", $args->email_setup);
        $this->db->where("id", $args->id);
		$this->valid = $this->db->update("reff_app_setup");
		
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

<?php
class modelCustomer extends CI_Model {

	var $valid = false;

	public function __construct(){
        parent::__construct();
        $this->load->helper('accesscontrol');     
        $this->helper = new appHelper();
		LoggedSystem();
	}
	
    public function saveData($args)
    {           
        $cuNumber = '';
        if(empty($args->id)) { 
            if(!empty($args->customer_code)) {
                $cuNumber = $args->customer_code;
            }
            else{                
			    $cuNumber = $this->helper->getNewNumber("customer", $args->id_distributor);
            }
		}

        $this->db->set("id_distributor", $args->id_distributor);
        $this->db->set("id_sales", $args->id_sales);
        $this->db->set("customer_name", $args->customer_name);
        $this->db->set("address", $args->customer_address);
        
        if(!empty($args->id)) {			
            $this->db->where("id", $args->id);
            $this->db->set("modified_date", date("Y-m-d H:i:s"));
            $this->db->set("modified_user", $this->session->userdata("sanitasDistUserId"));
			$this->valid = $this->db->update("mst_customer");
            $cuNumber = $args->customer_code;
        }
        else{
            $this->db->set("code", $cuNumber);        
            $this->db->set("add_user", $this->session->userdata("sanitasDistUserId"));
			$this->valid = $this->db->insert("mst_customer");
        }
        
        return $cuNumber;
    }

}

<?php
class modelSalesPerson extends CI_Model {

	var $valid = false;

	public function __construct(){
        parent::__construct();
        $this->load->helper('accesscontrol');
		LoggedSystem();
	}

    public function saveData($args)
    {        
        $this->db->set("code", $args->code);
        $this->db->set("id_distributor", $args->id_distributor);
        $this->db->set("sales_name", $args->sales_name);

        if(!empty($args->id)) {
            $this->db->set("modified_date", date("Y-m-d H:i:s"));
            $this->db->set("modified_user", $this->session->userdata("sanitasDistUserId"));
            $this->db->where("id", $args->id);
            $this->valid = $this->db->update("mst_sales_person");
        }
        else{
            $this->db->set("add_user", $this->session->userdata("sanitasDistUserId"));
            $this->valid = $this->db->insert("mst_sales_person");
        }

        return $this->valid;
    }

}

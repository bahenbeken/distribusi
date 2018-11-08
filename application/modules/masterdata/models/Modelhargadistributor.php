<?php
class modelHargaDistributor extends CI_Model {

	var $valid = false;

	public function __construct(){
        parent::__construct();
        $this->load->helper('accesscontrol');
		LoggedSystem();
	}

    public function saveData($args)
    {        
        $this->db->set("id_distributor", $args->id_distributor);
        $this->db->set("id_item", $args->id_item);
        $this->db->set("buying_price", $args->buying_price);
        
        $isFixed = 0;
        if(isset($args->is_fixed_price)) {
            $isFixed = 1;
        }

        $this->db->set("is_fixed_price", $isFixed);
        $this->db->set("point", $args->point);  

        if(!empty($args->id)) {
            $this->db->set("modified_date", date("Y-m-d H:i:s"));
            $this->db->set("modified_user", $this->session->userdata("sanitasDistUserId"));
            $this->db->where("id", $args->id);
            $this->valid = $this->db->update("mst_harga_distributor");
        }
        else{
            $this->db->set("add_user", $this->session->userdata("sanitasDistUserId"));
            $this->valid = $this->db->insert("mst_harga_distributor");
        }

        return $this->valid;
    }

}

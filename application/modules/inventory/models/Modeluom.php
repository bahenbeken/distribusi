<?php
class modelUom extends CI_Model {

	var $valid = false;
    var $helper;

	public function __construct(){
        parent::__construct();
        $this->load->helper('accesscontrol');        
        $this->helper = new appHelper();
		LoggedSystem();
	}	
    
    
    public function saveData($args)
    {
        
        $this->db->set("uom", $args->uom);
        $this->db->set("description", $args->description);

        if(!empty($args->id)) {
            $this->db->where("id", $args->id);
            $this->db->set("modified_date", date("Y-m-d H:i:s"));
            $this->db->set("modified_user", $this->session->userdata("sanitasDistUserId"));
            $this->valid = $this->db->update("mst_uom");
        }
        else{
            $this->db->set("add_user", $this->session->userdata("sanitasDistUserId"));
            $this->valid = $this->db->insert("mst_uom");
        }
        return $this->valid;
    }
	
}
?>

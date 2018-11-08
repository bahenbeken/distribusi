<?php
class modelItem extends CI_Model {

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
        $itemNumber = 0;
        if(empty($args->id)) { 
            if(!empty($args->item_number)) {
                $itemNumber = $args->item_number;
            }
            else{                
			    $itemNumber = $this->helper->getNewNumber("item_number");
            }
            
        }

        $this->db->set("item_name", $args->item_name);
        $this->db->set("id_uom", $args->id_uom);
        $this->db->set("description", $args->description);

        if(!empty($args->id)) {
            $this->db->where("id", $args->id);
            $this->db->set("modified_date", date("Y-m-d H:i:s"));
            $this->db->set("modified_user", $this->session->userdata("sanitasDistUserId"));
            $this->valid = $this->db->update("mst_item");
        }
        else{
            $this->db->set("item_number", $itemNumber);        
            $this->db->set("add_user", $this->session->userdata("sanitasDistUserId"));
            $this->valid = $this->db->insert("mst_item");
        }

        return $this->valid;
    }
	
}
?>

<?php
class modelPurchaseOrder extends CI_Model {

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
        $poNumber = '';
        if(empty($args->id)) { 

            if(!empty($args->po_number)) {
                $poNumber = $args->po_number;
            }
            else{                
			    $poNumber = $this->helper->getNewNumber("purchase_order", $args->id_distributor);
            }			
			
        }

        $this->db->set("id_distributor", $args->id_distributor);
        $this->db->set("tanggal_po", $args->tanggal_po);
        $this->db->set("total_po", $args->total_po);
        $this->db->set("note", $args->note);
        $this->db->set("status", "draft");
        $this->db->set("total_po", $args->total_po);

        if(!empty($args->id)) {			
            $this->db->where("id", $args->id);
            $this->db->set("modified_date", date("Y-m-d H:i:s"));
            $this->db->set("modified_user", $this->session->userdata("sanitasDistUserId"));
			$this->valid = $this->db->update("trans_po");
            $poID = $args->id;
            $poNumber = $args->po_number;
        }
        else{
            $this->db->set("po_number", $poNumber);        
            $this->db->set("add_user", $this->session->userdata("sanitasDistUserId"));
			$this->valid = $this->db->insert("trans_po");
			$poID = $this->db->insert_id();
        }
        
       
		
		$this->db->where("id_po", $poID);
		$this->db->delete("trans_po_detail");

		$jumlah = $this->input->post("last_id");
		
		for($x = 1; $x <= $jumlah; $x++) {
			$id_item = "id_item".$x;
			$qty = "qty".$x;
			$price = "price".$x;
			$sub_total = "sub_total".$x;

			$this->db->set("id_po", $poID);			
			$this->db->set("id_item", $args->$id_item);
			$this->db->set("unit_price", $args->$price);
			$this->db->set("qty", $args->$qty);
			$this->db->set("sub_total", $args->$sub_total);
			$this->db->set("add_user", $this->session->userdata("sanitasDistUserId"));
			$valid = $this->db->insert("trans_po_detail");
			
		}
        
        return $poNumber;
    }

    public function getHeader($id) {
        $sql = "SELECT a.*, b.`distributor_name`, b.`code` AS disti_code, b.`address`, COUNT(c.`id_item`) AS total_item FROM `trans_po` a 
            JOIN `mst_distributor` b ON b.`id`=a.`id_distributor` JOIN `trans_po_detail` c ON c.`id_po`=a.`id`
            WHERE a.`id`='".$id."' GROUP BY a.`id`";
        $data = $this->db->query($sql)->row();
        return $data;

    }

    public function getDetail($id) {
        $sql = "SELECT e.`is_fixed_price`, b.`item_name`, b.`item_number`, c.`uom`, a.* FROM trans_po_detail a
            JOIN mst_item b ON a.`id_item`=b.`id`
            JOIN mst_uom c ON c.`id`=b.`id_uom`
            JOIN `trans_po` d ON d.`id`=a.`id_po`
            JOIN `mst_harga_distributor` e ON e.`id_distributor`=d.`id_distributor` AND e.`id_item`=a.`id_item`
            WHERE a.`id_po`='".$id."'";
        $data = $this->db->query($sql)->result();
        return $data;
    }

}

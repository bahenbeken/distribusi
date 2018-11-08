<?php
class modelDistributor extends CI_Model {

	var $valid = false;

	public function __construct(){
        parent::__construct();
        $this->load->helper('accesscontrol');
		LoggedSystem();
	}

	private function getNewIdentity()
	{
		$insertDate = date("Y-m-d");

		$today = $this->db->get_where("temp_identity", array("insert_date" => $insertDate));
		$num = $today->num_rows();
		$number = 0;

		if($num > 0) {
			$data = $today->row();
			$number = $data->inserted_number + 1;

			$this->db->set("inserted_number", $number);
			$this->db->where("insert_date", $insertDate);
			$this->db->update("temp_identity");
		}
		else{
			$data = $today->row();
			$number = 1;

			$this->db->set("inserted_number", $number);
			$this->db->set("insert_date", $insertDate);
			$this->db->insert("temp_identity");
		}

		$numChar = "";
		for ($x = 1; $x <= 4 - strlen($number); $x++) {
			$numChar.="0";
		}
		$numChar.= $number;

		$newId = date("dmy").$numChar;

		return $newId;
	}

	public function setSerialNumber($idDistributor) {
		$arrCode = array("item_number", "purchase_order", "penerimaan_barang", "customer", "invoice", "invoice_payment");
		$arrDesc = array("Item Number Code", "Purchase Order Code", "Penerimaaan Code", "Customer Code", "Invoice Number", "Invoice Payment Number");
		$arrPrefix = array("MB", "PO", "PB", "CU", "IV", "UM");
		$arrLast = array("000000", "000000", "000000", "000000", "000000", "000000");

		
		for($x = 0; $x < count($arrCode); $x++) {
			$this->db->set("id_distributor", $idDistributor);
			$this->db->set("code_name", $arrCode[$x]);
			$this->db->set("description", $arrDesc[$x]);
			$this->db->set("prefix_char", $arrPrefix[$x]);
			$this->db->set("last_number", $arrLast[$x]);
			$this->valid = $this->db->insert("reff_number_series");
		}

		return $this->valid;
	}

    
    public function saveData($args)
    {        
		$type = "";
		$idDist = 0;
		$this->db->set("code", $args->code);
        $this->db->set("distributor_name", $args->distributor_name);
        $this->db->set("asm_name", $args->asm_name);
		$this->db->set("address", $args->address);
		
		

        if(!empty($args->id)) {
            $this->db->set("modified_date", date("Y-m-d H:i:s"));
            $this->db->set("modified_user", $this->session->userdata("sanitasDistUserId"));
            $this->db->where("id", $args->id);
			$this->valid = $this->db->update("mst_distributor");
			$idDist = $args->id;
			$type = "edit";
		}
        else{
            $this->db->set("add_user", $this->session->userdata("sanitasDistUserId"));
            $this->valid = $this->db->insert("mst_distributor");
			$type = "new";
			$idDist = $this->db->insert_id();
		}

		if($type === "new") {
			$this->valid = $this->setSerialNumber($idDist);
		}

        return $this->valid;
    }

}

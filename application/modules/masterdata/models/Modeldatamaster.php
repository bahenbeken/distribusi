<?php
class modelDataMaster extends CI_Model {

	var $valid = false;
	var $db_ref;

	public function __construct(){
        parent::__construct();
        $this->load->helper('accesscontrol');
				$this->db_ref = $this->load->database('ref', TRUE);
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

  public function saveProductItem($args)
  {
      $this->db_ref->set("name", $args->name);
      $this->db_ref->set("price", $args->price);
      $this->db_ref->set("pricekrt", $args->pricekrt);

      if(!empty($args->id)) {
          $this->db_ref->where("id", $args->id);
          $this->valid = $this->db_ref->update("itemtable");
      }
      else{
          $this->valid = $this->db_ref->insert("itemtable");
      }

      return $this->valid;
  }

	public function saveDistributor($args)
  {
      $this->db->set("name", $args->name);
      $this->db->set("telephone", $args->telephone);
      $this->db->set("address", $args->address);

      if(!empty($args->id)) {
          $this->db->where("id", $args->id);
          $this->valid = $this->db->update("distributortable");
      }
      else{
          $this->valid = $this->db->insert("distributortable");
      }

      return $this->valid;
  }

	public function saveRetailer($args)
  {
			$identityNumber = 0;
			if(empty($args->id)) {
				$identityNumber = $this->getNewIdentity();
			}

      $this->db->set("name", $args->name);
      $this->db->set("telephone", $args->telephone);
      $this->db->set("email", $args->email);
      $this->db->set("address", $args->address);
      $this->db->set("region", $args->region);
      $this->db->set("place_birth", $args->place_birth);
      $this->db->set("date_birth", $args->date_birth);

      if(!empty($args->id)) {
          $this->db->where("id", $args->id);
          $this->valid = $this->db->update("retailtable");
      }
      else{
					$this->db->set("identity_number", $identityNumber);
          $this->valid = $this->db->insert("retailtable");
      }

      return $this->valid;
  }

	public function saveCustomer($args)
  {
			$identityNumber = 0;
			if(empty($args->id)) {
				$identityNumber = $this->getNewIdentity();
			}

      $this->db->set("name", $args->name);
      $this->db->set("telephone", $args->telephone);
      $this->db->set("email", $args->email);
      $this->db->set("address", $args->address);
      $this->db->set("region", $args->region);
      $this->db->set("place_birth", $args->place_birth);
      $this->db->set("date_birth", $args->date_birth);

      if(!empty($args->id)) {
          $this->db->where("id", $args->id);
          $this->valid = $this->db->update("custtable");
      }
      else{
					$this->db->set("identity_number", $identityNumber);
          $this->valid = $this->db->insert("custtable");
      }

      return $this->valid;
  }

}
?>

<?php
class modelAuthentication extends CI_Model {

        
    function __construct()
    {
        parent::__construct();
    }

	public function loginAuth($userName, $password)
	{
		$valid = false;
		$password = md5($password);

		$query = $this->db->get_where("mst_user", array("username" => $userName,"password" => $password));
		if($query->num_rows() > 0)
		{
		   $data = $query->row();
		   if($data->username == $userName and $data->password == $password)
		   {	
				$distCode = "";
				$distName = "";		
				$distAddress = "";		
				if($data->level === "asm" and (int)$data->id_distributor > 0) {
					$dist = $this->db->get_where("mst_distributor", array("id" => $data->id_distributor))->row();
					$distCode = $dist->code;
					$distName = $dist->distributor_name;	
					$distAddress = $dist->address;					
				}

		   		$session = array(
  					'sanitasDistUserId' => $data->id_user,
  					'sanitasDistUsername' => $data->username,
  					'sanitasDistPassword' => $data->password,
  					'sanitasDistLevel' => $data->level,
  					'sanitasDistDistributorID' => $data->id_distributor,
  					'sanitasDistDistributorName' => $distName,
  					'sanitasDistDistributorCode' => $distCode,
  					'sanitasDistDistributorAddress' => $distAddress,
  					'sanitasDistLogged' => TRUE
  				);

				$this->session->set_userdata($session);
				$valid = true;
		   }
		}    
		return $valid;
    }
    
    public function gantiPassword($password)
	{
		$log = $this->session->all_userdata();
		$userId = $this->session->userdata('userId');
		$valid = false;

		$this->db->set("password", $password);
		$this->db->where("id_user", $userId);
		$valid = $this->db->update("mst_user");

		$session = array(
			 'userPassword' => $password,
		);

		$this->session->set_userdata($session);
		return $valid;
	}
}
?>

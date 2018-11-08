<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Utility extends MX_Controller {

	private $container;
	private $valid = false;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('accesscontrol');
		$this->load->helper('app');
		$this->load->model('modelUtility');
		$this->load->helper('url');
		$this->container["data"] = NULL;
		LoggedSystem();
	}

	public function emailSetup()
	{
		if($_POST){
			$args = (object) $this->input->post();
			$valid = $this->modelUtility->saveEmailSetup($args);
			if($valid) {
					$this->session->set_flashdata(array("type" => "success", "notify" => "Data submition success!"));
			}
			else{
					$this->session->set_flashdata(array("type" => "warn", "notify" => "Data submition failed to save! please check the paramaters"));
			}
			redirect("/masterdata/utility/emailsetup");
		}
		$this->container["edit"] = $this->db->get_where("reff_app_setup", array("id" => 1))->row();
		$this->twig->display("form/formEmailSetup.html", $this->container);
	}

	public function deleteAdmin($id){
		$this->db->where("id", $id);
		$this->valid = $this->db->delete("admintable");
		if($this->valid)
		{
			$this->session->set_flashdata(array("type" => "success", "notify" => "Data deleted from the system!"));
		}
		redirect("/main/utility/admin");
    }
    
    public function formuser($id = NULL)
    {
        if($_POST){
			$args = (object) $this->input->post();
			$safeToSave = true;

			if(empty($args->id)) {
				$exist = $this->db->get_where("mst_user", array("username" => $args->username))->num_rows();
				if($exist > 0)  $safeToSave = false;
			}

			if($safeToSave) {
				$valid = $this->modelUtility->saveUser($args);
				if($valid) {
						$this->session->set_flashdata(array("type" => "success", "notify" => "Data submition success!"));
				}
				else{
						$this->session->set_flashdata(array("type" => "warn", "notify" => "Data submition failed to save! please check the paramaters"));
				}
			}
			else{
				$this->session->set_flashdata(array("type" => "warn", "notify" => "Data submition failed to save! username has been used, please change it."));
			}
			redirect("/masterdata/utility/formUser");
		}

        if(!empty($id)) {
            
            $edit = $this->db->query("select a.*, b.code as distributor_code, b.distributor_name from mst_user a left join mst_distributor b on b.id=a.id_distributor where a.id_user='".$id."'")->row();
            $this->container["edit"] = $edit;
        }

        $this->twig->display("form/formUser.html", $this->container);
    }

	public function listDataUser() {
        $sql = "select a.*, b.code, b.distributor_name from mst_user a left join mst_distributor b on b.id=a.id_distributor";

        $data = $this->db->query($sql)->result();
        $x = 0;
    	foreach($data as $row) { 
			$x++;
            $responce->data[] = array(
                $row->username,
                $row->level, 
                $row->code." - ".$row->distributor_name,
				$row->id_user
			);
		}
		
		echo json_encode($responce);
	}

	public function user($id = NULL)
	{
		$this->twig->display("grid/gridUser.html", $this->container);
    }

    public function userAkses()
	{
		$this->twig->display("grid/gridAkses.html", $this->container);
    }
    
    public function listDataCode($idDist) {
        $sql = "SELECT a.*, b.code AS distributor_code, b.distributor_name FROM reff_number_series a LEFT JOIN mst_distributor b ON b.id=a.id_distributor WHERE a.id_distributor='".$idDist."'";
		
		$data = $this->db->query($sql)->result();
        $x = 0;
    	foreach($data as $row) { 
			$x++;
            $responce->data[] = array(
                $row->distributor_code." - ".$row->distributor_name,
                $row->code_name,
                $row->description, 
                $row->prefix_char,
                $row->last_number,
				$row->id
			);
		}
		
		echo json_encode($responce);
	}

	public function serialCode($id = NULL)
	{
        $this->container["id_distributor"] = "all"; 
		if($_POST) {
            $args = (object) $this->input->post();
            $this->container["id_distributor"] = $args->id_distributor; 
		}
        
        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
            $this->container["distributor"] = $this->db->get_where("mst_distributor", array("id" => $idDistributor))->result();
        }
        else{
            $this->container["distributor"] = $this->db->get("mst_distributor")->result();
        }
        
        $this->twig->display("grid/gridSerialCode.html", $this->container);
    }
    
    public function formCode($id = NULL)
    {
        if($_POST){
			$args = (object) $this->input->post();
			$valid = $this->modelUtility->saveCode($args);
            if($valid) {
                    $this->session->set_flashdata(array("type" => "success", "notify" => "Data submition success!"));
            }
            else{
                    $this->session->set_flashdata(array("type" => "warn", "notify" => "Data submition failed to save! please check the paramaters"));
            }
			redirect("/masterdata/utility/formcode");
		}

        if(!empty($id)) {
            
            $edit = $this->db->query("select * from `reff_number_series` where id='".$id."'")->row();
            $this->container["edit"] = $edit;
        }

        $this->twig->display("form/formCode.html", $this->container);
    }

	public function deleteUser($id){
		$this->db->where("id_user", $id);
		$this->valid = $this->db->delete("mst_user");
		if($this->valid)
		{
			$this->session->set_flashdata(array("type" => "success", "notify" => "Data deleted from the system!"));
		}
		redirect("/masterdata/utility/user");
	}


	public function changePassword()
	{
		if($_POST){
			$args = (object) $this->input->post();
			if($args->passold === $this->session->userdata("sanitasPassword")) {
				if($args->pass1 === $args->pass2) {
					$this->valid = $this->modelUtility->changePassword($args);
				}
				else{
					$this->session->set_flashdata(array("type" => "warn", "notify" => "Update failed! your new password is not same"));
				}
			}
			else{
				$this->session->set_flashdata(array("type" => "warn", "notify" => "Update failed! old password is incorrect"));
			}


			if($this->valid) {
				$this->session->set_flashdata(array("type" => "success", "notify" => "Password updated!"));
			}
			else {
				$this->session->set_flashdata(array("type" => "warn", "notify" => "Password gagal di ganti, pastikan password anda sama!"));
			}
			redirect("/main/utility/changepassword");
		}
		$this->twig->display("form/formGanpass.html", $this->container);
	}



}

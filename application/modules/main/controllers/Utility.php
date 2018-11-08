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

	public function admin($id = NULL)
	{
		if($_POST){
			$args = (object) $this->input->post();
			$safeToSave = true;

			if(empty($args->id)) {
				$exist = $this->db->get_where("admintable", array("username" => $args->username))->num_rows();
				if($exist > 0)  $safeToSave = false;
			}

			if($safeToSave) {
				$valid = $this->modelUtility->saveAdmin($args);
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
			redirect("/main/utility/admin");
		}

		if(!empty($id))
		{
			$this->container["edit"] = $this->db->get_where("admintable", array("id" => $id))->row();
		}

		$this->container["data"] = $this->db->get("admintable")->result();
		$this->twig->display("form/formAdmin.html", $this->container);
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

	public function user($id = NULL)
	{
		if($_POST){
			$args = (object) $this->input->post();
			$safeToSave = true;

			if(empty($args->id)) {
				$exist = $this->db->get_where("usertable", array("username" => $args->username))->num_rows();
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
			redirect("/main/utility/user");
		}

		if(!empty($id))
		{
			$sql = "select a.*, b.name as retailer_name from usertable a left join retailtable b on b.id=a.retail_id where a.id='".$id."'";
			$this->container["edit"] = $this->db->query($sql)->row();
		}

		$sqlData = "select a.*, b.name as retailer_name from usertable a left join retailtable b on b.id=a.retail_id";
		$this->container["data"] = $this->db->query($sqlData)->result();
		$this->twig->display("form/formUser.html", $this->container);
	}

	public function deleteUser($id){
		$this->db->where("id", $id);
		$this->valid = $this->db->delete("usertable");
		if($this->valid)
		{
			$this->session->set_flashdata(array("type" => "success", "notify" => "Data deleted from the system!"));
		}
		redirect("/main/utility/user");
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

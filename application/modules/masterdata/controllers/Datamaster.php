<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Datamaster extends MX_Controller {

	private $container;
	private $valid = false;
	var $db_ref;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('accesscontrol');
		$this->load->helper('app');
		$this->load->model('modelDataMaster');
		$this->load->helper('url');
		$this->container["data"] = null;
		$this->db_ref = $this->load->database('ref', TRUE);
		LoggedSystem();
	}

	public function productItem($id = NULL)
  {
      if($_POST) {
          $args = (object) $this->input->post();
          $valid = $this->modelDataMaster->saveProductItem($args);

					if($valid) {
              $this->session->set_flashdata(array("type" => "success", "notify" => "Data submition success!"));
          }
          else{
              $this->session->set_flashdata(array("type" => "warn", "notify" => "Data submition failed to save! please check the paramaters"));
          }
          redirect("/main/datamaster/productitem");
      }

      if(!empty($id)) {
          $edit = $this->db_ref->get_where("itemtable", array("id" => $id))->row();
          $this->container["edit"] = $edit;
      }

      $this->container["data"] = $this->db_ref->get("itemtable")->result();
      $this->twig->display("form/formProductItem.html", $this->container);
  }

  public function deleteProductItem($id)
  {
        $this->db_ref->where("id", $id);
        $valid = $this->db_ref->delete("itemtable");

        if($valid) {
            $this->session->set_flashdata(array("type" => "success", "notify" => "Data deleted from the system!"));
        }
        else{
            $this->session->set_flashdata(array("type" => "warn", "notify" => "Data unabled to delete, please check the paramaters or contact the administrator!"));
        }
        redirect("/main/datamaster/productitem");
  }


	public function distributor($id = NULL)
  {
        if($_POST) {
            $args = (object) $this->input->post();
            $valid = $this->modelDataMaster->saveDistributor($args);

                        if($valid) {
                $this->session->set_flashdata(array("type" => "success", "notify" => "Data submition success!"));
            }
            else{
                $this->session->set_flashdata(array("type" => "warn", "notify" => "Data submition failed to save! please check the paramaters"));
            }
            redirect("/main/datamaster/distributor");
        }

        if(!empty($id)) {
            $edit = $this->db->get_where("distributortable", array("id" => $id))->row();
            $this->container["edit"] = $edit;
        }

      $this->container["data"] = $this->db->get("distributortable")->result();
      $this->twig->display("form/formDistributor.html", $this->container);
  }

  public function deleteDistributor($id)
  {
        $this->db->where("id", $id);
        $valid = $this->db->delete("distributortable");

        if($valid) {
            $this->session->set_flashdata(array("type" => "success", "notify" => "Data deleted from the system!"));
        }
        else{
            $this->session->set_flashdata(array("type" => "warn", "notify" => "Data unabled to delete, please check the paramaters or contact the administrator!"));
        }
        redirect("/main/datamaster/distributor");
  }

	public function retailer($id = NULL)
  {
      if($_POST) {
          $args = (object) $this->input->post();
          $valid = $this->modelDataMaster->saveRetailer($args);

					if($valid) {
              $this->session->set_flashdata(array("type" => "success", "notify" => "Data submition success!"));
          }
          else{
              $this->session->set_flashdata(array("type" => "warn", "notify" => "Data submition failed to save! please check the paramaters"));
          }
          redirect("/main/datamaster/retailer");
      }

      if(!empty($id)) {
          $edit = $this->db->get_where("retailtable", array("id" => $id))->row();
          $this->container["edit"] = $edit;
      }

      $this->container["data"] = $this->db->get("retailtable")->result();
      $this->twig->display("form/formRetail.html", $this->container);
  }

  public function deleteRetailer($id)
  {
			$this->db->where("id", $id);
			$valid = $this->db->delete("retailtable");

      if($valid) {
          $this->session->set_flashdata(array("type" => "success", "notify" => "Data deleted from the system!"));
      }
      else{
          $this->session->set_flashdata(array("type" => "warn", "notify" => "Data unabled to delete, please check the paramaters or contact the administrator!"));
      }
      redirect("/main/datamaster/retailer");
  }


	public function customer($id = NULL)
  {
      if($_POST) {
          $args = (object) $this->input->post();
          $valid = $this->modelDataMaster->saveCustomer($args);

					if($valid) {
              $this->session->set_flashdata(array("type" => "success", "notify" => "Data submition success!"));
          }
          else{
              $this->session->set_flashdata(array("type" => "warn", "notify" => "Data submition failed to save! please check the paramaters"));
          }
          redirect("/main/datamaster/customer");
      }

      if(!empty($id)) {
          $edit = $this->db->get_where("custtable", array("id" => $id))->row();
          $this->container["edit"] = $edit;
      }

      $this->container["data"] = $this->db->get("custtable")->result();
      $this->twig->display("form/formCustomer.html", $this->container);
  }

  public function deleteCustomer($id)
  {
			$this->db->where("id", $id);
			$valid = $this->db->delete("custtable");

      if($valid) {
          $this->session->set_flashdata(array("type" => "success", "notify" => "Data deleted from the system!"));
      }
      else{
          $this->session->set_flashdata(array("type" => "warn", "notify" => "Data unabled to delete, please check the paramaters or contact the administrator!"));
      }
      redirect("/main/datamaster/customer");
  }



}

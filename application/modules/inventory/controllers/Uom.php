<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Uom extends MX_Controller {

	private $container;
	private $valid = false;
    var $db_ref;
    var $helper;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('accesscontrol');
		$this->load->helper('app');
		$this->load->model('modelUom');
        $this->load->helper('url');
        $this->helper = new appHelper();
		$this->container["data"] = null;
        LoggedSystem();
	}

	public function index()
    {
        $this->twig->display("grid/gridUom.html", $this->container);
    }

    public function form($id = null)
    {
        if($_POST) {
            $args = (object) $this->input->post();
            $valid = $this->modelUom->saveData($args);
            
            if($valid) {
                $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil tersimpan!"));
            }
            else{
                $this->session->set_flashdata(array("type" => "warning", "notify" => "Data gagal tersimpan"));
            }
        
            redirect("/inventory/uom/form");
        }

        if(!empty($id)) {
            $this->container["edit"] = $this->db->get_where("mst_uom", array("id" => $id))->row();
        }

        $this->twig->display("form/formUom.html", $this->container);
    }

    public function listData()
    {
        $this->db->order_by("id", "desc");
        $data = $this->db->get("mst_uom")->result();
        $x = 0;
		foreach($data as $row) { 
			$x++;
            $responce->data[] = array(
                $x,
				$row->uom, 
				$row->description,
				$row->id
			);
		}
		
		echo json_encode($responce);
    }
    
    public function deleteData($id) 
    {
        $this->db->where("id", $id);
        $valid = $this->db->delete("mst_uom");
        if($this->db->error()["code"] === 1451) {
            $valid = false;
        }
        if($valid) {
            $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil dihapus!"));
        }
        else{
            $this->session->set_flashdata(array("type" => "warning", "notify" => "Data gagal terhapus! data tersebut di gunakan di transaksi lain"));
        }
        redirect("/inventory/uom/index");
    }

}

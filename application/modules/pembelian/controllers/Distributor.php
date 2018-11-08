<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Distributor extends MX_Controller {

	private $container;
	private $valid = false;
    var $db_ref;
    var $helper;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('accesscontrol');
		$this->load->helper('app');
		$this->load->model('modelDistributor');
        $this->load->helper('url');
        $this->helper = new appHelper();
		$this->container["data"] = null;
        LoggedSystem();
	}

	public function index()
    {
        $this->twig->display("grid/gridDistributor.html", $this->container);
    }

    public function form($id = null)
    {
        if($_POST) {
            $args = (object) $this->input->post();            
            $isEmpty = $this->helper->emptyData($args->id, "mst_distributor", array("code" => $args->code));

            if($isEmpty) {
                $valid = $this->modelDistributor->saveData($args);
                
                if($valid) {
                    $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil tersimpan!"));
                }
                else{
                    $this->session->set_flashdata(array("type" => "warning", "notify" => "Data gagal tersimpan"));
                }
            }
            else {
                $this->session->set_flashdata(array("type" => "warning", "notify" => "Data gagal tersimpan, data dengan kode tersebut telah ada"));  
            }
            redirect("/masterdata/distributor/form");
        }

        if(!empty($id)) {
            $this->container["edit"] = $this->db->get_where("mst_distributor", array("id" => $id))->row();
        }

        $this->twig->display("form/formDistributor.html", $this->container);
    }

    public function listData()
    {

        $this->db->order_by("id", "desc");
        $data = $this->db->get("mst_distributor")->result();
        $x = 0;
		foreach($data as $row) { 
			$x++;
            $responce->data[] = array(
                $x,
				$row->code, 
				$row->asm_name, 
				$row->distributor_name, 
				$row->address,
				$row->id
			);
		}
		
		echo json_encode($responce);
    }
    
    public function deleteData($id) 
    {
        $this->db->where("id", $id);
        $valid = $this->db->delete("mst_distributor");
        if($this->db->error()["code"] === 1451) {
            $valid = false;
        }
        if($valid) {
            $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil dihapus!"));
        }
        else{
            $this->session->set_flashdata(array("type" => "warning", "notify" => "Data gagal terhapus! data tersebut di gunakan di transaksi lain"));
        }
        redirect("/masterdata/distributor/index");
    }

}

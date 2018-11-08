<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Salesperson extends MX_Controller {

	private $container;
	private $valid = false;
    var $db_ref;
    var $helper;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('accesscontrol');
		$this->load->helper('app');
		$this->load->model('modelSalesPerson');
        $this->load->helper('url');
        $this->helper = new appHelper();
		$this->container["data"] = null;
        LoggedSystem();
	}

	public function index()
    {
       
        $this->twig->display("grid/gridSalesPerson.html", $this->container);
    }

    public function listData($idDistributor = null)
    {

        $sql = "SELECT a.*, b.code as distributor_code, b.distributor_name 
        from mst_sales_person a join mst_distributor b on b.id=a.id_distributor";

        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
        }
        
        if(!empty($idDistributor)) {
            $sql.= " WHERE a.id_distributor='".$idDistributor."'";
        }

        $sql.=" order by a.id desc"; 

        $data = $this->db->query($sql)->result();
        $x = 0;
    	foreach($data as $row) { 
			$x++;
            $responce->data[] = array(
                $x,
				$row->code, 
				$row->distributor_code." - ".$row->distributor_name, 
				$row->sales_name,
				$row->id
			);
		}
		
		echo json_encode($responce);
    }

    public function form($id = null)
    {
        if($_POST) {
            $args = (object) $this->input->post();            
            $isEmpty = $this->helper->emptyData($args->id, "mst_sales_person", array("code" => $args->code));

            if($isEmpty) {
                $valid = $this->modelSalesPerson->saveData($args);
                
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
            redirect("/masterdata/salesperson/form");
        }

        if(!empty($id)) {
            $this->container["edit"] = $this->db->get_where("mst_sales_person", array("id" => $id))->row();
        }

        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
            $this->container["distributor"] = $this->db->get_where("mst_distributor", array("id" => $idDistributor))->result();
        }
        else{
            $this->container["distributor"] = $this->db->get("mst_distributor")->result();
        }

        $this->twig->display("form/formSalesPerson.html", $this->container);
    }

    
    
    public function deleteData($id) 
    {
        $this->db->where("id", $id);
        $valid = $this->db->delete("mst_sales_person");
        if($this->db->error()["code"] === 1451) {
            $valid = false;
        }
        
        if($valid) {
            $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil dihapus!"));
        }
        else{
            $this->session->set_flashdata(array("type" => "warning", "notify" => "Data gagal terhapus! data tersebut di gunakan di transaksi lain"));
        }
        redirect("/masterdata/salesperson/index");
    }

    public function popUpBrowse($idDistributor = null) {
        $this->container["id_distributor"] = $idDistributor; 
        $this->twig->display("popup/popUpSales.html", $this->container);
    }

}

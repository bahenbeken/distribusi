<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer extends MX_Controller {

	private $container;
	private $valid = false;
    var $db_ref;
    var $helper;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('accesscontrol');
		$this->load->helper('app');
		$this->load->model('modelCustomer');
        $this->load->helper('url');
        $this->helper = new appHelper();
		$this->container["data"] = null;
        LoggedSystem();
	}

	public function index()
    {
        $this->twig->display("grid/gridCustomer.html", $this->container);
    }

    public function form($id = null)
    {
        if($_POST) {
            $args = (object) $this->input->post();            
            $isEmpty = $this->helper->emptyData($args->id, "mst_customer", array("code" => $args->code));

            if($isEmpty) {
                $cuNumber = $this->modelCustomer->saveData($args);
                
                if(!empty($cuNumber)) {
                    $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil tersimpan! dengan kode ".$cuNumber));
                }
                else{
                    $this->session->set_flashdata(array("type" => "warning", "notify" => "Data gagal tersimpan"));
                }
            }
            else {
                $this->session->set_flashdata(array("type" => "warning", "notify" => "Data gagal tersimpan, data dengan kode tersebut telah ada"));  
            }
            redirect("/penjualan/customer/form");
        }

        if(!empty($id)) {
            $sql = "SELECT a.*, b.`id` AS id_sales, b.`code` AS sales_code, b.`sales_name`, c.`id` AS id_distrobutor, c.`code`, c.`distributor_name` FROM mst_customer a JOIN mst_sales_person b ON b.id=a.id_sales JOIN mst_distributor c ON c.id=a.id_distributor where a.id='".$id."'";
            $this->container["edit"] = $this->db->query($sql)->row();
            //$this->container["edit"] = $this->db->get_where("mst_customer", array("id" => $id))->row();
        }

        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");            
            $this->container["distributor"] = $this->db->get_where("mst_distributor", array("id" => $idDistributor))->result();
        }
        else{
            $this->container["distributor"] = $this->db->get("mst_distributor")->result();
        }
        
        $this->container["sales"] = $this->db->get("mst_sales_person")->result();

        $isAuto = $this->helper->isAuto("customer", $this->session->userdata("sanitasDistDistributorID"));
        $this->container["is_auto"] = $isAuto;

        $this->twig->display("form/formCustomer.html", $this->container);
    }

    public function listData($idDistributor = null)
    {
        $sql = "select a.*, b.code as disti_code, b.distributor_name, c.code as sales_code, c.sales_name from mst_customer a 
                join mst_distributor b on b.id=a.id_distributor
                join mst_sales_person c on c.id=a.id_sales";

        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
        }

        if(!empty($idDistributor)) {
            $sql.= " WHERE a.id_distributor='".$idDistributor."'";
        }
        
        $sql.= " order by a.id desc";

        

        $data = $this->db->query($sql)->result();
        $x = 0;
		foreach($data as $row) { 
			$x++;
            $responce->data[] = array(
                $x,
				$row->code, 
				$row->customer_name, 
                $row->disti_code,
                $row->distributor_name,
                $row->sales_code,
                $row->sales_name, 
				$row->address,
				$row->id_distributor,
				$row->id_sales,
				$row->id
			);
		}
		
		echo json_encode($responce);
    }
    
    public function deleteData($id) 
    {
        $this->db->where("id", $id);
        $valid = $this->db->delete("mst_customer");
        if($this->db->error()["code"] === 1451) {
            $valid = false;
        }
        if($valid) {
            $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil dihapus!"));
        }
        else{
            $this->session->set_flashdata(array("type" => "warning", "notify" => "Data gagal terhapus! data tersebut di gunakan di transaksi lain"));
        }
        redirect("/penjualan/customer/index");
    }

    public function popUpDistroBrowse() {
        $this->twig->display("popup/popUpDistributor.html", $this->container);
    }

    public function popUpCustomer()
    {
        $this->twig->display("popup/popupCustomer.html", $this->container);
    }

    public function popUpCustomerOnly($idDistributor = null)
    {
        $this->container["id_distributor"] = $idDistributor;
        $this->twig->display("popup/popupCustomerOnly.html", $this->container);
    }

}

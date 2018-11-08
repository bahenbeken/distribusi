<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Item extends MX_Controller {

	private $container;
	private $valid = false;
    var $db_ref;
    var $helper;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('accesscontrol');
		$this->load->helper('app');
		$this->load->model('modelItem');
		$this->load->model('modelReport');
        $this->load->helper('url');
        $this->helper = new appHelper();
		$this->container["data"] = null;
        LoggedSystem();
	}

	public function index()
    {
       
        $this->twig->display("grid/gridItem.html", $this->container);
    }

    public function listData()
    {

        $sql = "select a.*, b.uom from mst_item a join mst_uom b on b.id=a.id_uom order by id desc";

        $data = $this->db->query($sql)->result();
        $x = 0;
    	foreach($data as $row) { 
			$responce->data[] = array(
                $row->item_number, 
				$row->item_name, 
				$row->uom, 
				$row->description,
				$row->id
			);
		}
		
		echo json_encode($responce);
    }

    public function form($id = null)
    {
        if($_POST) {
            $args = (object) $this->input->post();            
            $valid = $this->modelItem->saveData($args);                
            if($valid) {
                $this->session->set_flashdata(array("type" => "success", "notify" => "Data saved!"));
            }
            else{
                $this->session->set_flashdata(array("type" => "warning", "notify" => "Data failed to save, please check the parameters"));
            }
            redirect("/inventory/item/form");
        }

        if(!empty($id)) {
            $this->container["edit"] = $this->db->get_where("mst_item", array("id" => $id))->row();
        }

        $isAuto = $this->helper->isAuto("item_number", $this->session->userdata("sanitasDistDistributorID"));
        $this->container["is_auto"] = $isAuto;

        $this->container["uom"] = $this->db->get("mst_uom")->result();
        $this->twig->display("form/formItem.html", $this->container);
    }

    
    
    public function deleteData($id) 
    {
        $this->db->where("id", $id);
        $valid = $this->db->delete("mst_item");
        if($this->db->error()["code"] === 1451) {
            $valid = false;
        }
        
        if($valid) {
            $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil dihapus!"));
        }
        else{
            $this->session->set_flashdata(array("type" => "warning", "notify" => "Data gagal terhapus! data tersebut di gunakan di transaksi lain"));
        }
        redirect("/inventory/item/index");
    }

    public function distributorItem($idDistributir)
    {
       
        $this->twig->display("grid/gridItem.html", $this->container);
    }

    public function itemMasterPopup()
    {
       
        $this->twig->display("popup/popUpMasteritem.html", $this->container);
    }

    public function stockOnHand()
    {
        $this->twig->display("report/reportStockOnHandFilter.html", $this->container);
    }

    public function stockOnHandDisplay($fromDate, $toDate, $idDistributor = "0", $idItem = "0")
    {   
        $this->container["from_date"] = $fromDate;
        $this->container["to_date"] = $toDate;
        $this->container["distributor"] = $this->db->get_where("mst_distributor", array("id" => $idDistributor))->row();
        
        $data = $this->modelReport->stockOnHand($fromDate, $toDate, $idDistributor, $idItem);
        $this->container["data"] = $data;
        $this->twig->display("report/reportStockOnHandDisplay.html", $this->container);
             
    }

}

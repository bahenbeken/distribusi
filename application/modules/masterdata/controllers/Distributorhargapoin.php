<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Distributorhargapoin extends MX_Controller {

	private $container;
	private $valid = false;
    var $db_ref;
    var $helper;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('accesscontrol');
		$this->load->helper('app');
		$this->load->model('modelHargaDistributor');
        $this->load->helper('url');
        $this->helper = new appHelper();
		$this->container["data"] = null;
        LoggedSystem();
	}

	public function index()
    {
       
        $this->twig->display("grid/gridDistributorHarga.html", $this->container);
    }

    public function listData()
    {
        $sql = "SELECT a.*, b.code as distributor_code, b.distributor_name, c.item_name, c.item_number,
        if(a.is_fixed_price='1','Yes','No') as fixed_name
        from mst_harga_distributor a join mst_distributor b on b.id=a.id_distributor 
        join mst_item c on c.id=a.id_item ";

        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
            $sql.= " WHERE  a.id_distributor='".$idDistributor."'";
        }

        $sql.= "order by a.id desc";

        $data = $this->db->query($sql)->result();
        $x = 0;
    	foreach($data as $row) { 
			$x++;
            $responce->data[] = array(
                $x,
                $row->item_number,
                $row->item_name, 
                $row->distributor_code,
                $row->distributor_name, 
				$row->buying_price,
				$row->fixed_name,
				$row->point,
				$row->id
			);
		}
		
		echo json_encode($responce);
    }

    public function form($id = null)
    {
        if($_POST) {
            $args = (object) $this->input->post();  
            $isEmpty = $this->helper->emptyData($args->id, "mst_harga_distributor", array("id_distributor" => $args->id_distributor, "id_item" => $args->id_item));

            if($isEmpty) {
                
                $valid = $this->modelHargaDistributor->saveData($args);
                
                if($valid) {
                    $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil tersimpan!"));
                }
                else{
                    $this->session->set_flashdata(array("type" => "warning", "notify" => "Data gagal tersimpan"));
                }
            }
            else {
                $this->session->set_flashdata(array("type" => "warning", "notify" => "Data gagal tersimpan, data item untuk distibutor tersebut telah ada"));  
            }
            redirect("/masterdata/distributorhargapoin/form");
        }

        if(!empty($id)) {
            $this->container["edit"] = $this->db->get_where("mst_harga_distributor", array("id" => $id))->row();
        }

        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
            $this->container["distributor"] = $this->db->get_where("mst_distributor", array("id" => $idDistributor))->result();
        }
        else{
            $this->container["distributor"] = $this->db->get("mst_distributor")->result();
        }

        $this->container["item"] = $this->db->get("mst_item")->result();
        $this->twig->display("form/formHargaDistributor.html", $this->container);
    }

    public function deleteData($id) 
    {
        $this->db->where("id", $id);
        $valid = $this->db->delete("mst_harga_distributor");
        if($this->db->error()["code"] === 1451) {
            $valid = false;
        }
        
        if($valid) {
            $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil dihapus!"));
        }
        else{
            $this->session->set_flashdata(array("type" => "warning", "notify" => "Data gagal terhapus! data tersebut di gunakan di transaksi lain"));
        }
        redirect("/masterdata/distributorhargapoin/index");
    }

    public function listDataByDistro($idDistributor)
    {
        $sql = "SELECT a.*, c.id as id_item, b.code as distributor_code, b.distributor_name, c.item_name, c.item_number, d.uom,
        if(a.is_fixed_price='1','Yes','No') as fixed_name
        from mst_harga_distributor a join mst_distributor b on b.id=a.id_distributor 
        join mst_item c on c.id=a.id_item 
        join mst_uom d on d.id=c.id_uom
        where a.id_distributor='".$idDistributor."' order by a.id desc";
        
        $data = $this->db->query($sql)->result();
        $x = 0;
    	foreach($data as $row) { 
			$x++;
            $responce->data[] = array(
                $x,
                $row->item_number,
                $row->item_name, 
                $row->distributor_code,
                $row->distributor_name, 
				$row->buying_price,
				$row->fixed_name,
				$row->point,
				$row->uom,
				$row->id_item
			);
		}
		
		echo json_encode($responce);
    }

    public function popupItemDistributor($idDistributor, $num)
    {       
        $this->container["num"] = $num;
        $this->container["id_distributor"] = $idDistributor;
        $this->twig->display("popup/popUpDistributorHarga.html", $this->container);
    }

}

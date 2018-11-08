<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Penerimaan extends MX_Controller {

	private $container;
	private $valid = false;
    var $db_ref;
    var $helper;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('accesscontrol');
		$this->load->helper('app');
		$this->load->model('modelPenerimaan');
        $this->load->helper('url');
        $this->helper = new appHelper();
		$this->container["data"] = null;
        LoggedSystem();
	}

	public function index()
    {
        $this->twig->display("grid/gridPenerimaanBarang.html", $this->container);
    }

    public function form($type = "PO", $id = null)
    {
        if($_POST) {
            $args = (object) $this->input->post();         
            $pbNumber = $this->modelPenerimaan->saveData($args);
                
            if(!empty($pbNumber)) {
                $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil tersimpan dengan nomor Penerimaan ".$pbNumber));
            }
            else{
                $this->session->set_flashdata(array("type" => "warning", "notify" => "Data gagal tersimpan"));
            }

            redirect("/pembelian/penerimaan/form");
        }

        if(!empty($id)) {
            $this->container["header"] = $this->modelPenerimaan->getHeader($type, $id);
            $this->container["detail"] = $this->modelPenerimaan->getDetail($type, $id);            
        }
        
        $isAuto = $this->helper->isAuto("penerimaan_barang", $this->session->userdata("sanitasDistDistributorID"));
        $this->container["is_auto"] = $isAuto;

        $this->twig->display("form/formPenerimaan.html", $this->container);
    } 
    
    public function popUpPO()
    {
        $this->twig->display("grid/popUpPurchaseOrder.html", $this->container);
    }

    public function listDataPO()
    {
        $sql = "SELECT a.*, b.`distributor_name`, b.`code` AS disti_code, b.`address`, COUNT(c.`id_item`) AS total_item FROM `trans_po` a 
        JOIN `mst_distributor` b ON b.`id`=a.`id_distributor` JOIN `trans_po_detail` c ON c.`id_po`=a.`id`
        WHERE a.`status`='confirm' and a.`id` not in (select id_po from trans_penerimaan) ";

        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
            $sql.= " AND a.`id_distributor`='".$idDistributor."'";
        }

        $sql.= "  GROUP BY a.`id` ORDER BY a.`tanggal_po` DESC";

        $data = $this->db->query($sql)->result();
        
        
        $x = 0;
		foreach($data as $row) { 
			$x++;
            $responce->data[] = array(
                $x,
				$row->disti_code, 
				$row->distributor_name, 
				$row->po_number, 
				$row->total_po,  
				$row->total_item, 
				$row->status,
				$row->id
			);
		}
		
		echo json_encode($responce);
    }

    public function listDataPOUnfinish()
    {

        $sql = "SELECT a.*, b.`distributor_name`, b.`code` AS disti_code, b.`address`, COUNT(c.`id_item`) AS total_item FROM `trans_po` a 
        JOIN `mst_distributor` b ON b.`id`=a.`id_distributor` JOIN `trans_po_detail` c ON c.`id_po`=a.`id`
        WHERE a.`status`='confirm' AND (a.`id` NOT IN (SELECT id_po FROM trans_penerimaan) OR a.`id` IN  (SELECT id_po FROM trans_penerimaan WHERE `status`='draft') ) 
        ";

        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
            $sql.= " AND a.`id_distributor`='".$idDistributor."'";
        }

        $sql.= "  GROUP BY a.`id` ORDER BY a.`tanggal_po` DESC";
        
        
        $data = $this->db->query($sql)->result();
        
        $x = 0;
		foreach($data as $row) { 
			$x++;
            $responce->data[] = array(
                $x,
				$row->disti_code, 
				$row->distributor_name, 
				$row->po_number, 
				$row->total_po,  
				$row->total_item, 
				$row->status,
				$row->id
			);
		}
		
		echo json_encode($responce);
    }

    public function listData()
    {

        $sql = "SELECT b.`po_number`, e.`item_number`, e.`item_name`, f.`uom`, b.`tanggal_po`, c.`distributor_name`, c.`code` AS disti_code, 
        c.`address`, SUM(d.`qty_po`) AS total_qty_po, SUM(d.`qty_pb`) AS total_qty_pb, SUM(d.`qty_sisa`) AS total_qty_sisa, a.* FROM trans_penerimaan a
        JOIN trans_po b ON b.`id`=a.`id_po`
        JOIN `mst_distributor` c ON c.`id`=a.`id_distributor`
        JOIN trans_penerimaan_detail d ON d.`id_pb`=a.`id`  
        JOIN `mst_item` e ON e.`id`=d.`id_item`
        JOIN `mst_uom` f ON f.`id`=e.`id_uom`";

        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
            $sql.= " WHERE a.`id_distributor`='".$idDistributor."'";
        }

        $sql.= "  GROUP BY a.`id`, d.`id_item` ORDER BY a.`tanggal_pb` DESC";

        
        $data = $this->db->query($sql)->result();
        
        $x = 0;
		foreach($data as $row) { 
            $x++;
            $date = date_create($row->tanggal_pb);
            $responce->data[] = array(
                $x,
				$row->disti_code, 
				$row->distributor_name, 
                $row->po_number, 
                $row->item_number." - ".$row->item_name, 
				date_format(date_create($row->tanggal_po),"d/m/Y"),  
                $row->pb_number, 
                date_format($date, "d/m/Y"), 
				number_format($row->total_qty_po,2,",","."),
				number_format($row->total_qty_pb,2,",","."),
                number_format($row->total_qty_sisa,2,",","."),
                $row->uom, 
				$row->status,
				$row->id
			);
		}
		
		echo json_encode($responce);
    }
    
    public function viewDetail($type = "PB", $id = null)
    {
        $this->container["header"] = $this->modelPenerimaan->getHeader($type, $id);
        $this->container["detail"] = $this->modelPenerimaan->getDetail($type, $id);        
        $this->twig->display("report/viewPenerimaan.html", $this->container);
    }

    public function updateStatusPB($idPB, $statusName) {
        $this->db->set("status", $statusName);
        $this->db->where("id", $idPB);
        $updateStatus = $this->db->update("trans_penerimaan");    
        if($updateStatus) {
            $this->session->set_flashdata(array("type" => "success", "notify" => "Penerimaan barang telah berganti status menjadi ".$statusName));
        }
        else{
            $this->session->set_flashdata(array("type" => "error", "notify" => "Penerimaan barang tidak bisa di ganti status, mohon cek paramater!"));
        }
        redirect("/pembelian/penerimaan/viewdetail/".$idPB);
    }

    public function deleteData($id) 
    {
        $this->db->where("id", $id);
        $valid = $this->db->delete("trans_penerimaan");

        $this->db->where("id_pb", $id);
        $valid = $this->db->delete("trans_penerimaan_detail");

        
        if($valid) {
            $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil dihapus!"));
        }
        else{
            $this->session->set_flashdata(array("type" => "error", "notify" => "Data gagal terhapus!"));
        }
        redirect("/pembelian/penerimaan/index");
    }
}

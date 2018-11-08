<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoice extends MX_Controller {

	private $container;
	private $valid = false;
    var $db_ref;
    var $helper;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('accesscontrol');
		$this->load->helper('app');
		$this->load->model('modelInvoice');
        $this->load->helper('url');
        $this->helper = new appHelper();
		$this->container["data"] = null;
        LoggedSystem();
	}

	public function index()
    {
        $this->twig->display("grid/gridInvoice.html", $this->container);
    }

    public function form($id = null)
    {
        if($_POST) {
            $args = (object) $this->input->post();         
            $poNumber = $this->modelInvoice->saveData($args);
                
            if(!empty($poNumber)) {
                $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil tersimpan dengan nomor Invoice ".$poNumber));
            }
            else{
                $this->session->set_flashdata(array("type" => "error", "notify" => "Data gagal tersimpan"));
            }

            redirect("/penjualan/invoice/form");
        }

        if(!empty($id)) {
            $this->container["header"] = $this->modelInvoice->getHeader($id);
            $this->container["detail"] = $this->modelInvoice->getDetail($id);  
        }

        $isAuto = $this->helper->isAuto("invoice", $this->session->userdata("sanitasDistDistributorID"));
        $this->container["is_auto"] = $isAuto;
                
        $this->container["sales"] = $this->db->get("mst_sales_person")->result();
        $this->twig->display("form/formInvoice.html", $this->container);
    }    

    public function listData()
    {   
        $sql = "SELECT b.`customer_name`, c.`code` AS disti_code, c.`distributor_name`, 
        d.`code` AS sales_code, d.`sales_name`, COUNT(e.`id_item`) AS total_item, a.* FROM `trans_invoice` a 
        JOIN `mst_customer` b ON b.`id`=a.`id_customer`
        JOIN `mst_distributor` c ON c.`id`=a.`id_distributor`
        JOIN `mst_sales_person` d ON d.`id`=a.`id_sales`
        LEFT JOIN `trans_invoice_detail` e ON e.`id_invoice`=a.`id`";
        
        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
            $sql.=" WHERE a.`id_distributor`='".$idDistributor."'";
        }

        $sql.= "  GROUP BY a.`id` ORDER BY a.`id` DESC";

        $data = $this->db->query($sql)->result();
        
        $x = 0;
		foreach($data as $row) { 
			$x++;
            $responce->data[] = array(
                $x,
				$row->invoice_number, 
				$row->customer_name, 
				$row->disti_code." - ".$row->distributor_name, 
				$row->sales_code." - ".$row->sales_name,  
				date_format(date_create($row->invoice_date), "d/m/Y"), 
				date_format(date_create($row->due_date), "d/m/Y"), 
				$row->total_item, 
				$row->amount, 
				$row->status,
				$row->id
			);
        }
        
		
		echo json_encode($responce);
    }
    
    public function viewDetail($id = null)
    {
        $this->container["header"] = $this->modelInvoice->getHeader($id);
        $this->container["detail"] = $this->modelInvoice->getDetail($id);        
        $this->twig->display("report/viewInvoice.html", $this->container);
    }

    public function updateStatus($idPO, $statusName) {
        $this->db->set("status", $statusName);
        $this->db->where("id", $idPO);
        $updateStatus = $this->db->update("trans_invoice");    
        if($updateStatus) {
            $this->session->set_flashdata(array("type" => "success", "notify" => "Invoice telah berganti status menjadi ".$statusName));
        }
        else{
            $this->session->set_flashdata(array("type" => "error", "notify" => "Invoice tidak bisa di ganti status, mohon cek paramater!"));
        }
        redirect("/penjualan/invoice/viewdetail/".$idPO);
    }

    public function deleteData($id) 
    {
        $this->db->where("id", $id);
        $valid = $this->db->delete("trans_invoice");

        $this->db->where("id_invoice", $id);
        $valid = $this->db->delete("trans_invoice_detail");

        
        if($valid) {
            $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil dihapus!"));
        }
        else{
            $this->session->set_flashdata(array("type" => "error", "notify" => "Data gagal terhapus!"));
        }
        redirect("/pembelpenjualanian/purchaseorder/index");
    }
}

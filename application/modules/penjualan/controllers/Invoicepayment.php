<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoicepayment extends MX_Controller {

	private $container;
	private $valid = false;
    var $db_ref;
    var $helper;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('accesscontrol');
		$this->load->helper('app');
		$this->load->model('modelInvoicePayment');
        $this->load->helper('url');
        $this->helper = new appHelper();
		$this->container["data"] = null;
        LoggedSystem();
	}

	public function index()
    {
        $this->twig->display("grid/gridInvoicePayment.html", $this->container);
    }

    public function form($type = "INV", $id = null)
    {
        if($_POST) {
            $args = (object) $this->input->post();         
            $umNumber = $this->modelInvoicePayment->saveData($args);
                
            if(!empty($umNumber)) {
                $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil tersimpan dengan nomor UM ".$umNumber));
            }
            else{
                $this->session->set_flashdata(array("type" => "error", "notify" => "Data gagal tersimpan"));
            }

            redirect("/penjualan/invoicepayment/form");
        }

        if(!empty($id)) {
            $this->container["header"] = $this->modelInvoicePayment->getHeader($type, $id);
            $this->container["detail"] = $this->modelInvoicePayment->getDetail($type, $id);  
        }

        $isAuto = $this->helper->isAuto("invoice_payment", $this->session->userdata("sanitasDistDistributorID"));
        $this->container["is_auto"] = $isAuto;
        
        $this->twig->display("form/formInvoicePayment.html", $this->container);
    }    

    public function listData()
    {   
        $sql = "SELECT SUM(e.`amount`) AS rekap_amount, SUM(e.`payment`) AS rekap_payment, SUM(e.`remain`) AS rekap_remain,
        COUNT(e.`id_invoice`) AS total_invoice, GROUP_CONCAT(f.`invoice_number`) AS invoice_numbers,
        b.`customer_name`, b.`code` AS customer_code, c.`code` AS disti_code, c.`distributor_name`, d.`code` AS sales_code,
        d.`sales_name`, a.* FROM `trans_um_invoice` a
        JOIN `mst_customer` b ON b.`id`=a.`id_customer`
        JOIN `mst_distributor` c ON c.`id`=a.`id_distributor`
        JOIN `mst_sales_person` d ON d.`id`=a.`id_sales`
        LEFT JOIN `trans_um_invoice_detail` e ON e.`id_um`=a.`id`
        LEFT JOIN `trans_invoice` f ON f.`id`=e.`id_invoice`";
        
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
				$row->um_number, 
				$row->customer_name, 
				$row->disti_code." - ".$row->distributor_name, 
				$row->sales_code." - ".$row->sales_name,  
				date_format(date_create($row->payment_date), "d/m/Y"), 
				$row->rekap_amount, 
				$row->rekap_payment, 
				$row->rekap_remain,
				$row->status,
				$row->id
			);
        }
        
		
		echo json_encode($responce);
    }
    
    public function viewDetail($id = null)
    {
        $this->container["header"] = $this->modelInvoicePayment->getHeader("PAY", $id);
        $this->container["detail"] = $this->modelInvoicePayment->getDetail("PAY", $id);        
        $this->twig->display("report/viewInvoicePayment.html", $this->container);
    }

    public function updateStatus($idUM, $statusName) {
        $this->db->set("status", $statusName);
        $this->db->where("id", $idUM);
        $updateStatus = $this->db->update("trans_um_invoice");    
        if($updateStatus) {
            $this->session->set_flashdata(array("type" => "success", "notify" => "Invoice telah berganti status menjadi ".$statusName));
        }
        else{
            $this->session->set_flashdata(array("type" => "error", "notify" => "Invoice tidak bisa di ganti status, mohon cek paramater!"));
        }
        redirect("/penjualan/invoicepayment/viewdetail/".$idUM);
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

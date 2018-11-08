<?php
class modelInvoicePayment extends CI_Model {

	var $valid = false;
    var $helper;

	public function __construct(){
        parent::__construct();
        $this->load->helper('accesscontrol');     
        $this->helper = new appHelper();
		LoggedSystem();
	}	
    
    public function saveData($args)
    {
        
       
        $umNumber = '';
        if(empty($args->umNumber)) { 

            if(!empty($args->um_number)) {
                $umNumber = $args->um_number;
            }
            else{                
			    $umNumber = $this->helper->getNewNumber("invoice_payment", $args->id_distributor);		
            }
            		
        }

        $this->db->set("id_customer", $args->id_customer);
        $this->db->set("id_distributor", $args->id_distributor);
        $this->db->set("id_sales", $args->id_sales);
        $this->db->set("total_um", $args->total_um);
        $this->db->set("payment_date", $args->payment_date);
        
        if(!empty($args->id_um)) {			
            $this->db->where("id", $args->id_um);
            $this->db->set("modified_date", date("Y-m-d H:i:s"));
            $this->db->set("modified_user", $this->session->userdata("sanitasDistUserId"));
			$this->valid = $this->db->update("trans_um_invoice");
            $umID = $args->id_um;
            $umNumber = $args->um_number;            
        }
        else{
            $this->db->set("um_number", $umNumber);        
            $this->db->set("add_user", $this->session->userdata("sanitasDistUserId"));
			$this->valid = $this->db->insert("trans_um_invoice");
			$umID = $this->db->insert_id();
        }

        /*
        if($umID > 0) {
            $detailExist = $this->db->query("select * from trans_um_invoice_detail where id_um='".$umID."'")->result();
            foreach ($detailExist as $exist) {
                $dataBefore = $this->db->get_where("trans_invoice", array("id" => $exist->id_invoice))->row();
                $paymentBefore = $dataBefore->paid - $exist->payment;
                $remainBefore = $dataBefore->remain + $exist->payment;

                $this->db->set("paid", $paymentBefore);
                $this->db->set("remain", $remainBefore);

                if($remainBefore <= 0 ) {
                    $paymentstatus = "fullpaid";
                }
                else{
                    $paymentstatus = "process";
                }

                $this->db->set("payment_status", $paymentstatus);
                $this->db->where("id", $exist->id_invoice);
                $this->db->update("trans_invoice");
            }
        }
        */
        
		$this->db->where("id_um", $umID);
		$this->db->delete("trans_um_invoice_detail");

        $jumlah = $this->input->post("last_id");       
        
		
		for($x = 1; $x <= $jumlah; $x++) {
			$id_invoice = "id_invoice".$x;
			$amount = "amount".$x;
			$payment = "payment".$x;
			$remain = "remain".$x;
            
            $this->db->set("id_um", $umID);
            $this->db->set("id_invoice", $args->$id_invoice);			
            $this->db->set("amount", $args->$amount);
            $this->db->set("payment", $args->$payment);
            $this->db->set("remain", $args->$remain);
            $this->db->set("add_user", $this->session->userdata("sanitasDistUserId"));			
            $valid = $this->db->insert("trans_um_invoice_detail");
            

            $dataNew = $this->db->get_where("trans_invoice", array("id" => $args->$id_invoice))->row();
            $paymentNew = $dataNew->paid + $args->$payment;
            $remainNew = $dataNew->remain - $args->$payment;

            $this->db->set("paid", $paymentNew);
            $this->db->set("remain", $remainNew);

                if($remainNew <= 0 ) {
                    $paymentstatus = "fullpaid";
                }
                else{
                    $paymentstatus = "process";
                }

            $this->db->set("payment_status", $paymentstatus);
            $this->db->where("id", $args->$id_invoice);
            $this->db->update("trans_invoice");
		}
        return $umNumber;
    }


    
    public function getHeader($type, $id) {

        if(strtoupper($type) === "INV") {
            $sql = "SELECT '' AS um_number, 0 as id_um, 0 as total_um, a.id_distributor, a.id_sales, a.id_customer, a.address, a.id,
            b.`code`  AS customer_code, b.`customer_name`,
            c.`code` AS disti_code, c.`code` AS distributor_code, c.`distributor_name`,
            d.`code` AS sales_code, d.`sales_name`
            FROM `trans_invoice` a 
            JOIN `mst_customer` b ON b.id=a.`id_customer`
            JOIN `mst_distributor` c ON c.`id`=a.`id_distributor`
            JOIN `mst_sales_person` d ON d.`id`=a.`id_sales`
            WHERE a.`id_customer`='".$id."'
            GROUP BY a.`id_customer`";
            
        }
        else{
            $sql = "SELECT a.* , a.id as id_um,
            b.`code`  AS customer_code, b.`customer_name`,
            c.`code` AS disti_code, c.`code` AS distributor_code, c.`distributor_name`,
            d.`code` AS sales_code, d.`sales_name`
            FROM `trans_um_invoice` a 
            JOIN `mst_customer` b ON b.id=a.`id_customer`
            JOIN `mst_distributor` c ON c.`id`=a.`id_distributor`
            JOIN `mst_sales_person` d ON d.`id`=a.`id_sales`
            WHERE a.`id`='".$id."'";
        }
       
        
        $data = $this->db->query($sql)->row();        
        return $data;

    }

    public function getDetail($type, $id) {
        if(strtoupper($type) === "INV") {
            $sql = "SELECT a.*, a.id as id_invoice, 0 AS payment FROM `trans_invoice` a
            WHERE a.`id_customer`='".$id."' and a.status='approve' and a.remain>0
            ORDER BY a.`id` DESC";
        }
        else{
            $sql = "SELECT b.`invoice_number`, a.*, a.remain AS real_remain  FROM `trans_um_invoice_detail` a
            JOIN `trans_invoice` b ON b.`id`=a.`id_invoice`
            WHERE a.id_um='".$id."'";
        }
        $data = $this->db->query($sql)->result();
        
        return $data;
    }

}

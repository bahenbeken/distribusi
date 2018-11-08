<?php
class modelInvoice extends CI_Model {

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
        $invNumber = '';
        if(empty($args->id)) { 

            if(!empty($args->invoice_number)) {
                $invNumber = $args->invoice_number;
            }
            else{                
			    $invNumber = $this->helper->getNewNumber("invoice", $args->id_distributor);		
            }
	
        }

        $this->db->set("id_customer", $args->id_customer);
        $this->db->set("id_distributor", $args->id_distributor);
        $this->db->set("id_sales", $args->id_sales);
        $this->db->set("address", $args->customer_address);
        $this->db->set("invoice_date", $args->invoice_date);
        $this->db->set("due_date", $args->due_date);
        $this->db->set("amount", $args->amount);
        $this->db->set("paid", 0);
        $this->db->set("remain", $args->amount);
        $this->db->set("status", "draft");
        $this->db->set("payment_status", "unpaid");
        
        if(!empty($args->id)) {			
            $this->db->where("id", $args->id);
            $this->db->set("modified_date", date("Y-m-d H:i:s"));
            $this->db->set("modified_user", $this->session->userdata("sanitasDistUserId"));
			$this->valid = $this->db->update("trans_invoice");
            $inID = $args->id;
            $invNumber = $args->invoice_number;
        }
        else{
            $this->db->set("invoice_number", $invNumber);        
            $this->db->set("add_user", $this->session->userdata("sanitasDistUserId"));
			$this->valid = $this->db->insert("trans_invoice");
			$inID = $this->db->insert_id();
		}
		
		$this->db->where("id_invoice", $inID);
		$this->db->delete("trans_invoice_detail");

        $jumlah = $this->input->post("last_id");     
        
		
		for($x = 1; $x <= $jumlah; $x++) {
			$id_item = "id_item".$x;
			$qty = "qty".$x;
			$price = "price".$x;
			$sub_total = "sub_total".$x;
            
            if(!empty($args->$id_item)) {
               
                $this->db->set("id_invoice", $inID);			
                $this->db->set("id_item", $args->$id_item);
                $this->db->set("unit_price", $args->$price);
                $this->db->set("qty", $args->$qty);
                $this->db->set("sub_total", $args->$sub_total);
                $this->db->set("add_user", $this->session->userdata("sanitasDistUserId"));			
                $valid = $this->db->insert("trans_invoice_detail");
                
			}
			
		}

        return $invNumber;
    }

    public function getHeader($id) {
        $sql = "SELECT b.`customer_name`, b.`code` AS customer_code, c.`code` AS distributor_code, c.`code` AS disti_code, c.`distributor_name`, 
        d.`code` AS sales_code, d.`sales_name`, COUNT(e.`id_item`) AS total_item, a.* FROM `trans_invoice` a 
        JOIN `mst_customer` b ON b.`id`=a.`id_customer`
        JOIN `mst_distributor` c ON c.`id`=a.`id_distributor`
        JOIN `mst_sales_person` d ON d.`id`=a.`id_sales`
        LEFT JOIN `trans_invoice_detail` e ON e.`id_invoice`=a.`id`
        WHERE a.`id`='".$id."'
        GROUP BY a.`id` ORDER BY a.`id` DESC;";
        $data = $this->db->query($sql)->row();
        return $data;

    }

    public function getDetail($id) {
        $sql = "SELECT e.`is_fixed_price`, b.`item_name`, b.`item_number`, c.`uom`, a.* FROM trans_invoice_detail a
            JOIN mst_item b ON a.`id_item`=b.`id`
            JOIN mst_uom c ON c.`id`=b.`id_uom`
            JOIN `trans_invoice` d ON d.`id`=a.`id_invoice`
            JOIN `mst_harga_retailer` e ON e.`id_distributor`=d.`id_distributor` AND e.`id_item`=a.`id_item`
            WHERE a.`id_invoice`='".$id."'";
        $data = $this->db->query($sql)->result();
        return $data;
    }

}

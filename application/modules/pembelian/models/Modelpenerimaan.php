<?php
class modelPenerimaan extends CI_Model {

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
        $pbNumber = '';
        if(empty($args->id)) { 

            if(!empty($args->pb_number)) {
                $pbNumber = $args->pb_number;
            }
            else{                
			    $pbNumber = $this->helper->getNewNumber("penerimaan_barang", $args->id_distributor);	
            }	
		
        }

        $this->db->set("id_distributor", $args->id_distributor);
        $this->db->set("id_po", $args->id_po);
        $this->db->set("tanggal_pb", $args->tanggal_pb);
        $this->db->set("status", "draft");

        if(!empty($args->id)) {			
            $this->db->where("id", $args->id);
            $this->db->set("modified_date", date("Y-m-d H:i:s"));
            $this->db->set("modified_user", $this->session->userdata("sanitasDistUserId"));
			$this->valid = $this->db->update("trans_penerimaan");
            $pbID = $args->id;
            $pbNumber = $args->pb_number;
        }
        else{
            $this->db->set("pb_number", $pbNumber);        
            $this->db->set("add_user", $this->session->userdata("sanitasDistUserId"));
			$this->valid = $this->db->insert("trans_penerimaan");
			$pbID = $this->db->insert_id();
        }
        
		$this->db->where("id_pb", $pbID);
		$this->db->delete("trans_penerimaan_detail");

        $jumlah = $this->input->post("last_id");
        $finish = true;
		
		for($x = 1; $x <= $jumlah; $x++) {
			$id_item = "id_item".$x;
			$qty_pb = "qty_pb".$x;
			$qty_po = "qty_po".$x;
            $qty_sisa = "qty_sisa".$x;
            

            if($args->$qty_pb > 0) {
                if($args->$qty_sisa > 0) {
                    $finish = false;
                }
    
                $this->db->set("id_pb", $pbID);			
                $this->db->set("id_item", $args->$id_item);
                $this->db->set("qty_po", $args->$qty_po);
                $this->db->set("qty_pb", $args->$qty_pb);
                $this->db->set("qty_sisa", $args->$qty_sisa);
                $this->db->set("add_user", $this->session->userdata("sanitasDistUserId"));
                $this->valid = $this->db->insert("trans_penerimaan_detail");
            }
            
			
        }
        
        //if($finish) {
            //var_dump()
            //$this->db->set("status", "receive");
            //$this->db->where("id_po", $args->id_po);
            //$this->valid = $this->db->update("trans_penerimaan");
        //}
        
        if($this->valid) {
            return $pbNumber;
        }
        else{
            return false;
        }
        
    }

    public function getHeader($type, $id) {

        if(strtoupper($type) === "PO") {
            $sql = "SELECT '' as pb_number, a.id as id_po, a.po_number, a.id_distributor, a.tanggal_po, b.`distributor_name`, b.`code` AS disti_code, b.`address`, COUNT(c.`id_item`) AS total_item FROM `trans_po` a 
                JOIN `mst_distributor` b ON b.`id`=a.`id_distributor` JOIN `trans_po_detail` c ON c.`id_po`=a.`id`
                WHERE a.`id`='".$id."' GROUP BY a.`id`";
        }
        else{
            $sql = "SELECT b.`po_number`, b.`tanggal_po`, c.`distributor_name`, c.`code` AS disti_code, 
            c.`address`, SUM(d.`qty_po`) AS total_qty_po, SUM(d.`qty_pb`) AS total_qty_pb, SUM(d.`qty_sisa`) AS total_qty_sisa, 
            a.* FROM trans_penerimaan a
            JOIN trans_po b ON b.`id`=a.`id_po`
            JOIN `mst_distributor` c ON c.`id`=a.`id_distributor`
            JOIN trans_penerimaan_detail d ON d.`id_pb`=a.`id`
            WHERE a.`id`='".$id."'
            GROUP BY a.`id`ORDER BY a.`tanggal_pb` DESC";
        }
        
        
        $data = $this->db->query($sql)->row();

        
        return $data;

    }

    public function getDetail($type, $id) {
        if(strtoupper($type) === "PO") {
            $querySisa = "SELECT * FROM `trans_penerimaan` WHERE id_po='".$id."'";
            $queryS = $this->db->query($querySisa);
            $lineS = $queryS->num_rows();

            if($lineS > 0) {
                $sql = "SELECT e.`is_fixed_price`, b.`item_name`, b.`item_number`, c.`uom`, a.`qty` AS qty_po, 0 as qty_pb, 
                    IFNULL(z.qty_pb,0) as qty_pb_udah, a.`qty` - IFNULL(z.qty_pb,0) AS qty_sisa, 
                    a.* FROM trans_po_detail a
                    JOIN mst_item b ON a.`id_item`=b.`id`
                    JOIN mst_uom c ON c.`id`=b.`id_uom`
                    JOIN `trans_po` d ON d.`id`=a.`id_po`
                    JOIN `mst_harga_distributor` e ON e.`id_distributor`=d.`id_distributor` AND e.`id_item`=a.`id_item`
                    LEFT JOIN (
                        SELECT b.`id_po`, a.id_item, SUM(a.qty_pb) AS qty_pb FROM `trans_penerimaan_detail` a
                        JOIN trans_penerimaan b ON b.`id`=a.`id_pb`
                        WHERE a.`id_pb` IN (SELECT id FROM `trans_penerimaan` WHERE `id_po`='".$id."')
                        GROUP BY id_item
                    ) z ON z.id_item=a.id_item AND z.id_po=a.id_po
                    WHERE a.`id_po`='".$id."'";

              
            }
            else{
                $sql = "SELECT e.`is_fixed_price`, b.`item_name`, b.`item_number`, c.`uom`, a.`qty` as qty_po, 0 as qty_pb, 0 as qty_pb_udah, a.`qty` - 0 as qty_sisa, a.* FROM trans_po_detail a
                    JOIN mst_item b ON a.`id_item`=b.`id`
                    JOIN mst_uom c ON c.`id`=b.`id_uom`
                    JOIN `trans_po` d ON d.`id`=a.`id_po`
                    JOIN `mst_harga_distributor` e ON e.`id_distributor`=d.`id_distributor` AND e.`id_item`=a.`id_item`
                    WHERE a.`id_po`='".$id."'";
                 
            }

            
        }
        else{
            $sql = "SELECT a.qty_pb as qty_pb_udah, b.`item_number`, b.`item_name`, c.`uom`, a.* FROM `trans_penerimaan_detail` a
            JOIN mst_item b ON b.`id`=a.`id_item`
            JOIN mst_uom c ON c.id=b.`id_uom`
            WHERE a.`id_pb`='".$id."'";

        }
        $data = $this->db->query($sql)->result();
        
        return $data;
    }

}

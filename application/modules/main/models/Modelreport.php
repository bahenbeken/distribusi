<?php
class modelReport extends CI_Model {

	var $valid = false;
    var $helper;

	public function __construct(){
        parent::__construct();
        $this->load->helper('accesscontrol');     
        $this->helper = new appHelper();
		LoggedSystem();
	}	
    
    
    public function penjualanRekap($fromDate, $toDate)
    {
        $sql = "SELECT f.`item_number`, f.`item_name`, g.`uom`, SUM(b.`qty`) AS total_qty, c.`code` AS `distributor_code`, 
        c.`distributor_name`, d.`code`, d.`customer_name`, c.`asm_name`,  e.`sales_name`
        
        FROM `trans_invoice` a 
        JOIN `trans_invoice_detail` b ON b.`id_invoice`=a.`id`
        JOIN `mst_item` f ON f.`id`=b.`id_item`
        JOIN `mst_uom` g ON g.`id`=f.`id_uom`
        JOIN `mst_distributor` c ON c.`id`=a.`id_distributor`
        JOIN `mst_customer` d ON d.`id`=a.`id_customer`
        JOIN `mst_sales_person` e ON e.`id`=a.`id_sales`
        
        WHERE a.`invoice_date` BETWEEN '".$fromDate."' AND '".$toDate."' AND a.`status`='approve'";

        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
            $sql.=" AND a.`id_distributor`='".$idDistributor."'";
        }


        $sql.= " GROUP BY f.`id` ORDER BY f.`item_number`";

        
              
        $query = $this->db->query($sql)->result();
        return $query;
    }

    public function penjualanDetail($fromDate, $toDate)
    {
        $sql = "SELECT f.`item_number`, f.`item_name`, g.`uom`, SUM(b.`qty`) AS total_qty, c.`code` AS `distributor_code`, 
        c.`distributor_name`, d.`code`, d.`customer_name`, c.`asm_name`,  e.`sales_name`
        
        FROM `trans_invoice` a 
        JOIN `trans_invoice_detail` b ON b.`id_invoice`=a.`id`
        JOIN `mst_item` f ON f.`id`=b.`id_item`
        JOIN `mst_uom` g ON g.`id`=f.`id_uom`
        JOIN `mst_distributor` c ON c.`id`=a.`id_distributor`
        JOIN `mst_customer` d ON d.`id`=a.`id_customer`
        JOIN `mst_sales_person` e ON e.`id`=a.`id_sales`
        
        WHERE a.`invoice_date` BETWEEN '".$fromDate."' AND '".$toDate."' AND a.`status`='approve'
        
        ";

        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
            $sql.=" AND a.`id_distributor`='".$idDistributor."'";
        }


        $sql.= " GROUP BY f.`id` ORDER BY f.`item_number`";

        $query = $this->db->query($sql)->result();
        return $query;
    }

    public function dataMargin($idDistributor, $fromDate, $toDate)
    {   
        
        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
        }

        $sql = "SELECT 
                e.code AS disti_code, e.distributor_name, c.item_number, c.item_name, d.uom, a.*, b.total_qty_pb, 
                IFNULL(b.price_non_hpp * a.total_qty_penjualan,0) AS price_hpp, 
                a.total_penjualan - ( IFNULL(b.price_non_hpp * a.total_qty_penjualan, 0) ) AS margin 
                FROM (
                    SELECT b.`id_distributor`, a.`id_item`, SUM(a.`qty`) AS total_qty_penjualan, SUM(a.`sub_total`) AS total_penjualan
                    FROM `trans_invoice_detail` a JOIN `trans_invoice` b ON b.`id`=a.`id_invoice`
                    WHERE b.`status`='approve' AND b.invoice_date BETWEEN '".$fromDate."' AND '".$toDate."' 
                    GROUP BY b.`id_distributor`, a.`id_item`
                ) a LEFT JOIN(
                    SELECT b.`id_distributor`, a.`id_item`, SUM(a.`qty_pb`) AS total_qty_pb, SUM(a.`qty_pb`) * c.`buying_price`  AS total_amount_receive, 
                    (SUM(a.`qty_pb`) * c.`buying_price`) / SUM(a.`qty_pb`) AS price_non_hpp
                    FROM `trans_penerimaan_detail` a JOIN  `trans_penerimaan` b ON b.`id`=a.`id_pb`
                    JOIN `mst_harga_distributor` c ON c.`id_item`=a.`id_item` AND c.`id_distributor`=b.`id_distributor`
                    WHERE b.`status`='receive' AND b.tanggal_pb BETWEEN '".$fromDate."' AND '".$toDate."' 
                    GROUP BY b.`id_distributor`, a.`id_item`
                ) b  ON a.id_distributor=b.id_distributor AND a.id_item=b.id_item
                JOIN mst_item c ON c.id=a.id_item
                JOIN mst_uom d ON d.id=c.id_uom
                JOIN mst_distributor e ON e.id=a.id_distributor
                WHERE a.id_distributor='".$idDistributor."'
                GROUP BY a.id_distributor, a.id_item";    
    
        
        $query = $this->db->query($sql);
        return $query->result();
    }

    /*
    public function stockOnHand($fromDate, $toDate, $idDistributor = "0", $idItem = "0") {
        $sql = "SELECT p.`code` AS distributor_code, p.`distributor_name`, a.*, IFNULL(b.saldo_awal,0) AS saldo_awal, IFNULL(d.saldo_jual,0) AS saldo_jual, (IFNULL(b.saldo_awal,0) - IFNULL(d.saldo_jual,0)) AS saldo_akhir, c.last_selling_date FROM (
                    SELECT w.id_item, w.id_distributor, mst_item.`item_number`, mst_item.`item_name`, `mst_uom`.`uom`, SUM(w.qty_beli) AS qty_beli, IFNULL(SUM(z.qty_jual),0) AS qty_jual
                    FROM mst_item JOIN `mst_uom` ON `mst_uom`.`id`=mst_item.`id_uom`
                    JOIN (
                        SELECT  b.`id_distributor`, a.`id_item`, a.`qty_pb`  AS qty_beli FROM `trans_penerimaan_detail` a 
                        JOIN `trans_penerimaan` b ON b.`id`=a.`id_pb`
                        WHERE b.`tanggal_pb` BETWEEN '".$fromDate."' AND '".$toDate."' AND b.`status`='receive'
                        
                    ) w ON w.id_item = mst_item.`id`
                    LEFT JOIN (
                        SELECT b.`id_distributor`, a.`id_item`, a.`qty` AS qty_jual FROM `trans_invoice_detail` a 
                        JOIN `trans_invoice` b ON  b.`id`=a.`id_invoice`
                        WHERE b.`invoice_date` BETWEEN '".$fromDate."' AND '".$toDate."' AND b.`status`='approve'
                    ) z ON z.id_item = mst_item.`id`
                    GROUP BY mst_item.`id`
                ) a  
                LEFT JOIN (
                    
                    SELECT w.id_item, w.id_distributor, mst_item.`item_number`, mst_item.`item_name`, `mst_uom`.`uom`, IFNULL(SUM(w.qty_beli),0) AS saldo_awal
                    FROM mst_item JOIN `mst_uom` ON `mst_uom`.`id`=mst_item.`id_uom`
                    JOIN (
                        SELECT  b.`id_distributor`, a.`id_item`, a.`qty_pb`  AS qty_beli FROM `trans_penerimaan_detail` a 
                        JOIN `trans_penerimaan` b ON b.`id`=a.`id_pb`
                        WHERE b.`tanggal_pb` < '".$fromDate."' AND b.`status`='receive'
                        
                    ) w ON w.id_item = mst_item.`id`
                    
                    GROUP BY mst_item.`id`
                
                ) b ON a.id_item=b.id_item AND a.id_distributor=b.id_distributor
                
                LEFT JOIN (
                    SELECT w.id_item, w.id_distributor, mst_item.`item_number`, mst_item.`item_name`, `mst_uom`.`uom`, IFNULL(SUM(w.qty_jual),0) AS saldo_jual
                    FROM mst_item JOIN `mst_uom` ON `mst_uom`.`id`=mst_item.`id_uom`
                    JOIN (
                        SELECT b.`id_distributor`, a.`id_item`, a.`qty` AS qty_jual FROM `trans_invoice_detail` a 
                        JOIN `trans_invoice` b ON  b.`id`=a.`id_invoice`
                        WHERE b.`invoice_date`  BETWEEN '".$fromDate."' AND '".$toDate."' AND b.`status`='approve'
                        
                    ) w ON w.id_item = mst_item.`id` GROUP BY mst_item.`id`
                ) d ON a.id_item=d.id_item AND a.id_distributor=d.id_distributor                
                
                LEFT JOIN (
                    
                    SELECT b.invoice_date AS last_selling_date, b.`id_distributor`, a.`id_item` FROM `trans_invoice_detail` a 
                    JOIN `trans_invoice` b ON  b.`id`=a.`id_invoice`
                    WHERE b.`invoice_date` BETWEEN '".$fromDate."' AND '".$toDate."' AND b.`status`='approve'
                    AND b.id IN (
                        SELECT MAX(`trans_invoice`.id) AS id FROM `trans_invoice` JOIN trans_invoice_detail 
                        ON `trans_invoice_detail`.`id_invoice`=`trans_invoice`.`id`
                        WHERE `trans_invoice`.`invoice_date` BETWEEN '".$fromDate."' AND '".$toDate."' AND `trans_invoice`.`status`='approve'			
                        AND `trans_invoice_detail`.id_item=a.`id_item` AND `trans_invoice`.`id_distributor`=b.`id_distributor`		
                    )
                    GROUP BY b.id, a.id_item
                
                ) c ON a.id_item=c.id_item AND a.id_distributor=c.id_distributor
                LEFT JOIN `mst_distributor` p ON p.`id`=a.id_distributor WHERE 1=1";
                
            if($idDistributor !=="0") {
                $sql.=" AND a.`id_distributor`='".$idDistributor."'";
            }
            
            if($idItem !=="0") {
                $sql.=" AND a.`id_item`='".$idItem."'";
            }
            
            
            $query = $this->db->query($sql);
            return $query->result();
    }
    */

    public function stockOnHand($fromDate, $toDate, $idDistributor = "0", $idItem = "0") {
        $sql = "SELECT p.`code` AS distributor_code, p.`distributor_name`, a.*, IFNULL(b.saldo_awal,0) AS saldo_awal, IFNULL(d.saldo_jual,0) AS saldo_jual, (IFNULL(b.saldo_awal,0) - IFNULL(d.saldo_jual,0)) AS saldo_akhir, c.last_selling_date FROM (
            SELECT w.tanggal, w.id_item, w.id_distributor, mst_item.`item_number`, mst_item.`item_name`, `mst_uom`.`uom`, SUM(w.qty_beli) AS qty_beli, IFNULL(SUM(z.qty_jual),0) AS qty_jual
            FROM mst_item JOIN `mst_uom` ON `mst_uom`.`id`=mst_item.`id_uom`
            JOIN (
                SELECT b.`tanggal_pb` as tanggal, b.`id_distributor`, a.`id_item`, a.`qty_pb`  AS qty_beli FROM `trans_penerimaan_detail` a 
                JOIN `trans_penerimaan` b ON b.`id`=a.`id_pb`
                WHERE b.`tanggal_pb` BETWEEN '".$fromDate."' AND '".$toDate."' AND b.`status`='receive'
                
            ) w ON w.id_item = mst_item.`id`
            LEFT JOIN (
                SELECT b.`invoice_date` as tanggal, b.`id_distributor`, a.`id_item`, a.`qty` AS qty_jual FROM `trans_invoice_detail` a 
                JOIN `trans_invoice` b ON  b.`id`=a.`id_invoice`
                WHERE b.`invoice_date` BETWEEN '".$fromDate."' AND '".$toDate."' AND b.`status`='approve'
            ) z ON z.id_item = mst_item.`id` and z.tanggal=w.tanggal
            GROUP BY mst_item.`id`
        ) a  
        LEFT JOIN (
            
            SELECT w.tanggal, w.id_item, w.id_distributor, mst_item.`item_number`, mst_item.`item_name`, `mst_uom`.`uom`, IFNULL(SUM(w.qty_beli),0) AS saldo_awal
            FROM mst_item JOIN `mst_uom` ON `mst_uom`.`id`=mst_item.`id_uom`
            JOIN (
                SELECT b.`tanggal_pb` as tanggal, b.`id_distributor`, a.`id_item`, a.`qty_pb`  AS qty_beli FROM `trans_penerimaan_detail` a 
                JOIN `trans_penerimaan` b ON b.`id`=a.`id_pb`
                WHERE b.`tanggal_pb` < '".$fromDate."' AND b.`status`='receive'
                
            ) w ON w.id_item = mst_item.`id`
            
            GROUP BY mst_item.`id`
        
        ) b ON a.id_item=b.id_item AND a.id_distributor=b.id_distributor and a.tanggal=b.tanggal
        
        LEFT JOIN (
            SELECT w.tanggal, w.id_item, w.id_distributor, mst_item.`item_number`, mst_item.`item_name`, `mst_uom`.`uom`, IFNULL(SUM(w.qty_jual),0) AS saldo_jual
            FROM mst_item JOIN `mst_uom` ON `mst_uom`.`id`=mst_item.`id_uom`
            JOIN (
                SELECT b.`invoice_date` as tanggal, b.`id_distributor`, a.`id_item`, a.`qty` AS qty_jual FROM `trans_invoice_detail` a 
                JOIN `trans_invoice` b ON  b.`id`=a.`id_invoice`
                WHERE b.`invoice_date`  BETWEEN '".$fromDate."' AND '".$toDate."' AND b.`status`='approve'
                
            ) w ON w.id_item = mst_item.`id` GROUP BY mst_item.`id`
        ) d ON a.id_item=d.id_item AND a.id_distributor=d.id_distributor and a.tanggal=d.tanggal               
        
        LEFT JOIN (
            
            SELECT b.invoice_date as tanggal, b.invoice_date AS last_selling_date, b.`id_distributor`, a.`id_item` FROM `trans_invoice_detail` a 
            JOIN `trans_invoice` b ON  b.`id`=a.`id_invoice`
            WHERE b.`invoice_date` BETWEEN '".$fromDate."' AND '".$toDate."' AND b.`status`='approve'
            AND b.id IN (
                SELECT MAX(`trans_invoice`.id) AS id FROM `trans_invoice` JOIN trans_invoice_detail 
                ON `trans_invoice_detail`.`id_invoice`=`trans_invoice`.`id`
                WHERE `trans_invoice`.`invoice_date` BETWEEN '".$fromDate."' AND '".$toDate."' AND `trans_invoice`.`status`='approve'			
                AND `trans_invoice_detail`.id_item=a.`id_item` AND `trans_invoice`.`id_distributor`=b.`id_distributor`		
            )
            GROUP BY b.id, a.id_item
        
        ) c ON a.id_item=c.id_item AND a.id_distributor=c.id_distributor AND a.tanggal=c.tanggal
        LEFT JOIN `mst_distributor` p ON p.`id`=a.id_distributor WHERE 1=1";

                
            if($idDistributor !=="0") {
                $sql.=" AND a.`id_distributor`='".$idDistributor."'";
            }
            
            if($idItem !=="0") {
                $sql.=" AND a.`id_item`='".$idItem."'";
            }
            
            
            $query = $this->db->query($sql);
            return $query->result();
    }

    public function dashBoard() {
        $sql1 = "SELECT b.`item_number`, b.`item_name`, SUM(a.qty) AS total FROM trans_invoice_detail a
                JOIN mst_item b ON b.`id`=a.`id_item`
                JOIN `trans_invoice` c ON c.`id`=a.`id_invoice` AND c.`invoice_date` >= NOW()-INTERVAL 3 MONTH AND c.`status`='approve'
                GROUP BY a.id_item
                ORDER BY SUM(a.qty) DESC LIMIT 5";
        $terlaris = $this->db->query($sql1)->result();

        $sql2 = "SELECT b.`item_number`, b.`item_name`, a.stock FROM (
                SELECT a.id_item, SUM(a.qty_pb) - b.qty_keluar AS stock FROM `trans_penerimaan_detail` a
                JOIN(
                    SELECT a.id_item, SUM(a.qty) AS qty_keluar FROM `trans_invoice_detail` a GROUP BY a.`id_item`
                ) b ON b.id_item=a.id_item
                GROUP BY a.`id_item`) a 
                JOIN mst_item b ON b.id=a.id_item
                ORDER BY a.stock DESC LIMIT 5";
        $stock = $this->db->query($sql2)->result();

        $sql3 = "SELECT b.`item_number`, b.`item_name`, SUM(a.qty) AS total FROM trans_invoice_detail a
                JOIN mst_item b ON b.`id`=a.`id_item`
                JOIN `trans_invoice` c ON c.`id`=a.`id_invoice` AND c.`invoice_date` = '".date("Y-m-d")."'
                GROUP BY a.id_item
                ORDER BY SUM(a.qty) DESC LIMIT 5";
        $terjual = $this->db->query($sql3)->result();

        $output = (object) array("terlaris" => $terlaris, "stock" => $stock, "terjual" => $terjual);
        return $output;
    }
    
}

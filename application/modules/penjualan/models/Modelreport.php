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
    
    public function arAgingData($fromDate, $toDate, $calculateDate, $custIdGroup = "0", $idDistributor = "0", $idCustomer = "0", $idSales = "0", $dueDate = "0")
    {
        $sql = "SELECT
    
        a.`id_customer`, c.`code` AS `distributor_code`, c.`distributor_name`, d.`code`, d.`customer_name`, c.`asm_name`, 
        e.`sales_name`, a.`invoice_number`, a.`invoice_date`, a.`due_date`, 
        '".$calculateDate."' AS skr, DATEDIFF('".$calculateDate."', a.`due_date`) AS dif,
        SUM(a.`amount`) AS total_amount,
        SUM(b.`payment`) AS total_payment, 
	    IFNULL(SUM(a.`amount`) - SUM(b.`payment`), SUM(a.`amount`)) AS total_remain,
        m.belum_due_date, z.kolom_satu, w.kolom_dua, u.kolom_tiga, v.kolom_empat
        
        FROM `trans_invoice` a 
        LEFT JOIN `trans_um_invoice_detail` b ON b.`id_invoice`=a.`id`
        LEFT JOIN `mst_distributor` c ON c.`id`=a.`id_distributor`
        LEFT JOIN `mst_customer` d ON d.`id`=a.`id_customer`
        LEFT JOIN `mst_sales_person` e ON e.`id`=a.`id_sales`
        LEFT JOIN (
            SELECT a.`id` AS id_invoice, IFNULL(SUM(b.`amount`) - SUM(b.`payment`), SUM(a.`amount`))  AS belum_due_date 
            FROM `trans_invoice` a 
            LEFT JOIN `trans_um_invoice_detail` b ON a.`id`=b.`id_invoice`
            WHERE DATEDIFF('".$calculateDate."', a.`due_date`) <= 0
            GROUP BY a.`id`
        ) m ON m.`id_invoice` = a.`id`
        LEFT JOIN (
            SELECT a.`id_invoice`, SUM(b.`amount`) - SUM(a.`payment`) AS kolom_satu 
            FROM `trans_um_invoice_detail` a 
            JOIN `trans_invoice` b ON b.`id`=a.`id_invoice`
            WHERE DATEDIFF('".$calculateDate."', b.`due_date`) >= 0 AND DATEDIFF('".$calculateDate."', b.`due_date`) <=30
            GROUP BY a.`id_invoice`
        ) z ON z.`id_invoice` = a.`id`
        LEFT JOIN (
            SELECT a.`id_invoice`, SUM(b.`amount`) - SUM(a.`payment`) AS kolom_dua 
            FROM `trans_um_invoice_detail` a 
            JOIN `trans_invoice` b ON b.`id`=a.`id_invoice`
            WHERE DATEDIFF('".$calculateDate."', b.`due_date`) >= 31 AND DATEDIFF('".$calculateDate."', b.`due_date`) <= 60
            GROUP BY a.`id_invoice`
        ) w ON w.`id_invoice` = a.`id`
        LEFT JOIN (
            SELECT a.`id_invoice`, SUM(b.`amount`) - SUM(a.`payment`) AS kolom_tiga 
            FROM `trans_um_invoice_detail` a 
            JOIN `trans_invoice` b ON b.`id`=a.`id_invoice`
            WHERE DATEDIFF('".$calculateDate."', b.`due_date`) >= 61 AND DATEDIFF('".$calculateDate."', b.`due_date`) <= 90
            GROUP BY a.`id_invoice`
        ) u ON u.`id_invoice` = a.`id`
        LEFT JOIN (
            SELECT a.`id_invoice`, SUM(b.`amount`) - SUM(a.`payment`) AS kolom_empat 
            FROM `trans_um_invoice_detail` a 
            JOIN `trans_invoice` b ON b.`id`=a.`id_invoice`
            WHERE DATEDIFF('".$calculateDate."', b.`due_date`) >= 91
            GROUP BY a.`id_invoice`
        ) v ON v.`id_invoice` = a.`id`";
        
        $sql.=" WHERE a.`invoice_date` BETWEEN '".$fromDate."' AND '".$toDate."' AND a.`status`='approve'";

        
        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
        }

        if($idDistributor !=="0") {
            $sql.=" AND a.`id_distributor`='".$idDistributor."'";
        }
        
        if($idCustomer !=="0") {
            $sql.=" AND a.`id_customer`='".$idCustomer."'";
        }
        
        if($idSales !=="0") {
            $sql.=" AND a.`id_sales`='".$idSales."'";
        }

        $sql.= "GROUP BY a.`id`";

        
        $query = $this->db->query($sql);
        return $query->result();
    }

    public function penjualanRekap($fromDate, $toDate, $custIdGroup = "0", $idDistributor = "0", $idCustomer = "0", $idSales = "0")
    {
        $sql = "	SELECT SUM(a.`amount`) AS total_amount, c.`code` AS `distributor_code`, c.`distributor_name`, d.`code`, d.`customer_name`, c.`asm_name`, 
        e.`sales_name`, a.* 
        FROM `trans_invoice` a 
        JOIN `mst_distributor` c ON c.`id`=a.`id_distributor`
        JOIN `mst_customer` d ON d.`id`=a.`id_customer`
        JOIN `mst_sales_person` e ON e.`id`=a.`id_sales`        
        WHERE a.`invoice_date` BETWEEN '".$fromDate."' AND '".$toDate."' AND a.`status`='approve'";

        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
        }
        
        if($idDistributor !=="0") {
            $sql.=" AND a.`id_distributor`='".$idDistributor."'";
        }
        
        if($idCustomer !=="0") {
            " AND a.`id_customer`='".$idCustomer."'";
        }
        
        if($idSales !=="0") {
            " AND a.`id_sales`='".$idSales."'";
        }

        
        $sql.= " GROUP BY a.`id_customer`, a.`id_sales`";
              
        $query = $this->db->query($sql);
        return $query->result();
    }

    public function penjualanDetail($fromDate, $toDate, $custIdGroup = "0", $idDistributor = "0", $idCustomer = "0", $idSales = "0")
    {
        $sql = "SELECT SUM(a.`amount`) AS total_amount, c.`code` AS `distributor_code`, c.`distributor_name`, d.`code`, d.`customer_name`, c.`asm_name`, 
        e.`sales_name`, a.* 
        FROM `trans_invoice` a 
        JOIN `mst_distributor` c ON c.`id`=a.`id_distributor`
        JOIN `mst_customer` d ON d.`id`=a.`id_customer`
        JOIN `mst_sales_person` e ON e.`id`=a.`id_sales`        
        WHERE a.`invoice_date` BETWEEN '".$fromDate."' AND '".$toDate."' AND a.`status`='approve'";

        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
        }

        if($idDistributor !=="0") {
            $sql.=" AND a.`id_distributor`='".$idDistributor."'";
        }
        
        if($idCustomer !=="0") {
            $sql.=" AND a.`id_customer`='".$idCustomer."'";
        }
        
        if($idSales !=="0") {
            $sql.=" AND a.`id_sales`='".$idSales."'";
        }

        
        $sql.= " GROUP BY a.`id`";

        
        $query = $this->db->query($sql);
        return $query->result();
    }

    public function bonusRetailer($fromDate, $toDate, $idDistributor = "0", $idCustomer = "0", $idSales = "0")
    {
        $sql = "SELECT IF(a.paid>0, 'Paid', 'Invoice') AS status_invoice, a.* FROM (
                    SELECT  f.`code` AS customer_code, f.`customer_name`, 
                    d.code AS distributor_code, d.distributor_name, d.`asm_name`,
                    e.`sales_name`, g.`item_number`, g.`item_name`, h.`uom`, 
                    b.`invoice_number`, b.status, IFNULL(i.id, 0) AS paid, SUM(a.`qty`) AS qty_penjualan, 
                    c.`point`, c.`point` * SUM(a.`qty`)  AS bonus, i.`id`
                    FROM trans_invoice_detail a
                    JOIN `trans_invoice` b ON b.`id`=a.`id_invoice`
                    JOIN `mst_harga_retailer` c ON c.`id_distributor`=b.`id_distributor` AND c.`id_item`=a.`id_item`
                    JOIN `mst_distributor` d ON d.`id`=b.`id_distributor`
                    JOIN `mst_sales_person` e ON e.`id`=b.`id_sales`
                    JOIN `mst_customer` f ON f.`id`=b.`id_customer`
                    JOIN `mst_item` g ON g.`id`=a.`id_item`
                    JOIN `mst_uom` h ON h.`id`=g.`id_uom`
                    LEFT JOIN `trans_um_invoice_detail` i ON i.`id_invoice`=b.`id`        
                    WHERE b.`status`<>'draft' AND b.invoice_date BETWEEN '".$fromDate."' AND '".$toDate."'";


                    if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
                        $idDistributor = $this->session->userdata("sanitasDistDistributorID");
                    }

                    if($idDistributor !=="0") {
                        $sql.=" AND b.`id_distributor`='".$idDistributor."'";
                    }

                    if($idCustomer !=="0") {
                        $sql.=" AND b.`id_customer`='".$idCustomer."'";
                    }

                    if($idSales !=="0") {
                        $sql.=" AND b.`id_sales`='".$idSales."'";
                    }

                    $sql.= "GROUP BY b.`id`, a.`id_item`
            ) a";
        
        $query = $this->db->query($sql);
        return $query->result();
    }


    public function bonusDistributor($fromDate, $toDate, $idDistributor = "0")
    {
        $sql = "SELECT  d.code AS distributor_code, d.distributor_name, d.`asm_name`, g.`item_number`, g.`item_name`, h.`uom`, 
                c.`point`, SUM(a.`qty_pb`)  AS qty_terima, c.`point` * SUM(a.`qty_pb`)  AS bonus
                FROM trans_penerimaan_detail a
                JOIN `trans_penerimaan` b ON b.`id`=a.id_pb
                JOIN `mst_harga_distributor` c ON c.`id_distributor`=b.`id_distributor` AND c.`id_item`=a.`id_item`
                JOIN `mst_distributor` d ON d.`id`=b.`id_distributor`
                JOIN `mst_item` g ON g.`id`=a.`id_item`
                JOIN `mst_uom` h ON h.`id`=g.`id_uom`

                WHERE b.`status`<>'draft' AND b.tanggal_pb BETWEEN '".$fromDate."' AND '".$toDate."'";

                if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
                    $idDistributor = $this->session->userdata("sanitasDistDistributorID");
                }

                if($idDistributor !=="0") {
                    $sql.=" AND b.`id_distributor`='".$idDistributor."'";
                }

                $sql.= "GROUP BY a.`id_item`";
        
        $query = $this->db->query($sql);
        return $query->result();
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Purchaseorder extends MX_Controller {

	private $container;
	private $valid = false;
    var $db_ref;
    var $helper;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('accesscontrol');
		$this->load->helper('app');
		$this->load->model('modelPurchaseOrder');
        $this->load->helper('url');
        $this->helper = new appHelper();
		$this->container["data"] = null;
        LoggedSystem();
	}

	public function index()
    {
        $this->twig->display("grid/gridPurchaseOrder.html", $this->container);
    }

    public function form($id = null)
    {
        if($_POST) {
            $args = (object) $this->input->post();         
            $poNumber = $this->modelPurchaseOrder->saveData($args);
                
            if(!empty($poNumber)) {
                $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil tersimpan dengan nomor PO ".$poNumber));
            }
            else{
                $this->session->set_flashdata(array("type" => "error", "notify" => "Data gagal tersimpan"));
            }

            redirect("/pembelian/purchaseorder/form");
        }

        if(!empty($id)) {
            $this->container["header"] = $this->modelPurchaseOrder->getHeader($id);
            $this->container["detail"] = $this->modelPurchaseOrder->getDetail($id);  
        }
        
        $isAuto = $this->helper->isAuto("purchase_order", $this->session->userdata("sanitasDistDistributorID"));
        $this->container["is_auto"] = $isAuto;
        
        $this->twig->display("form/formPurchaseOrder.html", $this->container);
    }    

    public function listData()
    {
        $sql= "SELECT a.*, b.`distributor_name`, b.`code` AS disti_code, b.`address`, COUNT(c.`id_item`) AS total_item FROM `trans_po` a 
        JOIN `mst_distributor` b ON b.`id`=a.`id_distributor` JOIN `trans_po_detail` c ON c.`id_po`=a.`id`";
        
        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
            $sql.= " WHERE a.`id_distributor`='".$idDistributor."'";
        }

        $sql.= " GROUP BY a.`id` ORDER BY a.`tanggal_po` DESC";

        $data = $this->db->query($sql)->result();
        
        $x = 0;
		foreach($data as $row) { 
            $x++;
            $date = date_create($row->tanggal_po);
            $responce->data[] = array(
                $x,
				$row->disti_code, 
				$row->distributor_name, 
				$row->po_number, 
				$row->total_po,  
				date_format($date, "d/m/Y"),  
				$row->status,
				$row->id
			);
		}
		
		echo json_encode($responce);
    }
    
    public function viewDetail($id = null, $statusUpdate = "")
    {
        $this->container["header"] = $this->modelPurchaseOrder->getHeader($id);
        $this->container["detail"] = $this->modelPurchaseOrder->getDetail($id);    
        $this->container["status_update"] = $statusUpdate;        
        $this->twig->display("report/viewPurchaseOrder.html", $this->container);
    }

    public function generateEmailPurchase($id) {
        $header = $this->modelPurchaseOrder->getHeader($id);
        $detail = $this->modelPurchaseOrder->getDetail($id);  

        $stringEmail = "";
        $stringEmail.= "
        <div style='padding:20px; margin:20px; font-size:14px; font-family:arial'>
        <table width='100%' style='font-size:14px; font-family:arial'>
            <tbody>
                <tr>
                    <td style='width:200px'>PO Number</td>
                    <td style='width:5px'>:</td>
                    <td><strong>".$header->po_number."</strong></td>
                </tr>
        
                <tr>
                    <td>Tanggal PO</td>
                    <td>:</td>
                    <td><strong>".date_format(date_create($header->tanggal_po),"m/d/Y")."</strong></td>
                </tr>
            </tbody>
        </table>
        <br/>
        <strong>PURCHASE ORDER</strong>
        <br/><br/>
        <table width='100%' style='font-size:14px; font-family:arial'>
            <tbody>
                <tr>
                    <td style='width:200px'>Kepada</td>
                    <td style='width:5px'>:</td>
                    <td><strong>PT. Sanitas</strong></td>
                </tr>
        
                <tr>
                    <td>Nama Distributor</td>
                    <td>:</td>
                    <td><strong>".$header->distributor_name."</strong></td>
                </tr>
        
                <tr>
                    <td>Alamat Distributor</td>
                    <td>:</td>
                    <td><strong>".$header->address."</strong></td>
                </tr>
            </tbody>
        </table>
        <br/><br/>
        <table width='100%' cellpadding='0' cellspacing='0' style='font-size:14px; font-family:arial; border-top:1px solid #333; border-right:1px solid #333;'>
            <thead>
                <tr>
                    <th  width='20px'  style='background-color:#ccc; padding:5px; border-left:1px solid #333; border-bottom:1px solid #333;'>No.</th>
                    <th style='background-color:#ccc; padding:5px; border-left:1px solid #333; border-bottom:1px solid #333;'>Description</th>
                    <th style='background-color:#ccc; padding:5px; border-left:1px solid #333; border-bottom:1px solid #333;'>Qty</th>
                    <th style='background-color:#ccc; padding:5px; border-left:1px solid #333; border-bottom:1px solid #333;'>Unit Price</th>
                    <th style='background-color:#ccc; padding:5px; border-left:1px solid #333; border-bottom:1px solid #333;'>Total Price</th>
                </tr>    
            </thead>
            <tbody>
        ";

        $x = 0;
        foreach ($detail as $data) {
            $x++;
            $stringEmail.="<tr>
                        <td style='padding:5px; border-left:1px solid #333; border-bottom:1px solid #333;'><span>".$x.".</span></td>
                        <td style='padding:5px; border-left:1px solid #333; border-bottom:1px solid #333;'>".$data->item_name."</td>
                        <td style='padding:5px; border-left:1px solid #333; border-bottom:1px solid #333;'>".$data->qty." ".$data->uom."</td>
                        <td align='right' style='padding:5px; border-left:1px solid #333; border-bottom:1px solid #333;'>".number_format($data->unit_price, 2, ".", ",")."</td>
                        <td align='right' style='padding:5px; border-left:1px solid #333; border-bottom:1px solid #333;'>".number_format($data->sub_total, 2, ".", ",")."</td>
                    </tr>"; 
        }
        $stringEmail.="</tbody></table>";
        $stringEmail.="<p><strong>NOTE :</strong><br/>".$header->note."</p>";
        $stringEmail.="</div>";

        return $stringEmail;
    }

    public function sendEmailInfo($bodyEmail, $subject = "PO") {
        $appSetup = $this->db->get_where("reff_app_setup", array("id" => 1))->row();

        $configEmail = array(
			'protocol' => 'smtp',
			'smtp_host' => 'ssl://smtp.gmail.com',
			'smtp_port' => 465,
			'validation '=> TRUE,
            'smtp_timeout'=>30,
			'smtp_user' => 'technoone263@gmail.com',
			'smtp_pass' => 'indonesia1234',
			'mailtype' => 'html',
			'charset' => 'iso-8859-1',
  		    'wordwrap' => TRUE
		);
		$this->load->library('email', $configEmail);
        
		$output = false;
        $emailTo = $appSetup->email_setup;        
        //$emailTo = "blurify@gmail.com";        
        $from = "technoone263@gmail.com";
		$this->email->set_newline("\r\n");
        $this->email->from($from, 'No Reply - DELTAGRO');
        $this->email->to($emailTo);
        $this->email->subject($subject);
        $this->email->message($bodyEmail);

	    //Send mail
	    if($this->email->send()) $output = true;

		return $output;
    }
    
    public function updateStatusPO($idPO, $statusName) {        
        $this->db->set("status", $statusName);
        $this->db->where("id", $idPO);
        $updateStatus = $this->db->update("trans_po");    
        if($updateStatus) {
            if($statusName === "approved") {
                $sql = "SELECT a.*, b.`code`, b.`distributor_name` FROM trans_po a 
                    JOIN `mst_distributor` b ON b.`id`=a.`id_distributor`
                    WHERE a.id='".$idPO."'";
                
                $dt = $this->db->query($sql)->row();
                $subject = $dt->po_number." # ".$dt->distributor_name; 

                $stringEmail = $this->generateEmailPurchase($idPO) ;                
                $sendEmail = $this->sendEmailInfo($stringEmail, $subject);
            }
            $this->session->set_flashdata(array("type" => "success", "notify" => "Purchase Order telah berganti status menjadi ".$statusName));
        }
        else{
            $this->session->set_flashdata(array("type" => "error", "notify" => "Purchase Order tidak bisa di ganti status, mohon cek paramater!"));
        }
        redirect("/pembelian/purchaseorder/viewdetail/".$idPO."/".$statusName);
    }

    public function deleteData($id) 
    {
        $this->db->where("id", $id);
        $valid = $this->db->delete("trans_po");

        $this->db->where("id_po", $id);
        $valid = $this->db->delete("trans_po_detail");

        
        if($valid) {
            $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil dihapus!"));
        }
        else{
            $this->session->set_flashdata(array("type" => "error", "notify" => "Data gagal terhapus!"));
        }
        redirect("/pembelian/purchaseorder/index");
    }
}

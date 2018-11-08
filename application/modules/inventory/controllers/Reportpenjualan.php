<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reportpenjualan extends MX_Controller {

	private $container;
	private $valid = false;
    var $db_ref;
    var $helper;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('accesscontrol');
		$this->load->helper('app');
		$this->load->model('modelReport');
        $this->load->helper('url');
        $this->helper = new appHelper();
		$this->container["data"] = null;
        LoggedSystem();
	}

	public function index()
    {
        $this->container["calculate_date"] = date("Y-m-d");
        $this->twig->display("report/reportPenjualanFilter.html", $this->container);
    }

    public function penjualanDisplay($fromDate, $toDate, $isDetail = "0")
    {   
        $this->container["from_date"] = $fromDate;
        $this->container["to_date"] = $toDate;
        $this->container["is_detail"] = $isDetail;

        
        if($isDetail == "1") {
            $data = $this->modelReport->penjualanDetail($fromDate, $toDate, $isDetail);
            $this->container["data"] = $data;
            $this->twig->display("report/reportPenjualanDisplayDetail.html", $this->container);
        }
        else{
            $data = $this->modelReport->penjualanRekap($fromDate, $toDate, $isDetail);
            $this->container["data"] = $data;
            $this->twig->display("report/reportPenjualanDisplayRekap.html", $this->container);
        }        
    }    

    public function marginReport()
    {
        $this->container["calculate_date"] = date("Y-m-d");
        $this->twig->display("report/reportMarginFilter.html", $this->container);
    }

    public function marginDisplay($idDistributor, $fromDate, $toDate)
    {   
        $this->container["from_date"] = $fromDate;
        $this->container["to_date"] = $toDate;
        $this->container["distributor"] = $this->db->get_where("mst_distributor", array("id" => $idDistributor))->row();
        
        $data = $this->modelReport->dataMargin($idDistributor, $fromDate, $toDate);
        $this->container["data"] = $data;
        $this->twig->display("report/reportMarginDisplayRekap.html", $this->container);
             
    }

}

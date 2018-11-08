<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Araging extends MX_Controller {

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
		$this->load->model('modelReport');
        $this->load->helper('url');
        $this->helper = new appHelper();
		$this->container["data"] = null;
        LoggedSystem();
	}

	public function index()
    {
        $this->container["calculate_date"] = date("Y-m-d");
        $this->twig->display("report/reportAragingFilter.html", $this->container);
    }

    public function arAgingDisplay($fromDate, $toDate, $calculateDate, $custIdGroup = "0", $idDistributor = NULL, $idCustomer = NULL, $idSales = NULL, $dueDate = NULL)
    {   
        $this->container["from_date"] = $fromDate;
        $this->container["to_date"] = $toDate;
        $this->container["calculate_date"] = $calculateDate;
        $this->container["group_by"] = $custIdGroup;

        $data = $this->modelReport->arAgingData($fromDate, $toDate, $calculateDate, $custIdGroup, $idDistributor, $idCustomer, $idSales, $dueDate);
        $this->container["data"] = $data;
        $this->twig->display("report/reportAragingDisplay.html", $this->container);
    }    

}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends MX_Controller {

	private $container;
	private $valid = false;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('accesscontrol');
		$this->load->model('modelReport');
		$this->load->helper('url');
		$this->load->library('zip');
		$this->load->helper('download');
		$this->load->helper('email');
		$this->container["content"] = NULL;
		LoggedSystem();
	}

	public function index()
	{   
        $idDistributor = 0;
        $dashboard = $this->modelReport->dashBoard();
        
        $this->container["terlaris"] = $dashboard->terlaris;
        $this->container["stock"] = $dashboard->stock;
        $this->container["terjual"] = $dashboard->terjual;
        $this->container["content"] = NULL;
        $this->container["today"] = date("Y-m-d");
		$this->twig->display("home.html", $this->container);
    }
    
    public function listDataStockOnHand()
    {   
        $idDistributor = "0";     
        if(!empty($this->session->userdata("sanitasDistDistributorID"))) {
            $idDistributor = $this->session->userdata("sanitasDistDistributorID");
        }

        $yesterday = date('Y-m-d',strtotime("-1 days"));
        $liveDate = "2018-04-01";
        $data = $this->modelReport->stockOnHand($liveDate, $yesterday, $idDistributor, "0");
        $x = 0;
		foreach($data as $row) { 
            $date = date_create($row->last_selling_date);
            $x++;
            $saldo_akhir = ($row->saldo_awal + $row->qty_beli) -  $row->qty_jual;
            $responce->data[] = array(
               $row->item_number, 
				$row->item_name, 
				$row->uom, 
				$row->distributor_code." - ".$row->distributor_name,
				date_format($date, "Y/m/d"),
				$row->saldo_awal,
				$row->qty_beli,
				$row->qty_jual,
				$saldo_akhir
			);
		}
		
		echo json_encode($responce);
    }

	public function logout(){
		$this->session->sess_destroy();
		redirect(base_url());
	}

	

}

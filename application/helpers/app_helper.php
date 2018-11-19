<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class appHelper {
    var $db;
    var $ci;

    function __construct() {
        $this->ci =& get_instance();
        $this->db = $this->ci->db;    
    }

    public function emptyData($id, $table, $field) {
        $exist = true;
        if(empty($id)) {
            $totalData = $this->db->get_where($table, $field)->num_rows();        
            if($totalData > 0)
                $exist = false;    
        }
        
        return $exist;
    }

    function getFK()
    {
        $c = uniqid(rand(), true);
        $md5c = md5($c);
        return $md5c;		
    }
    
    function uploadSurat($input){
        $CI =& get_instance();
        $temp = $input['tmp_name'];
        $arr = explode(".", $input['name']);
        $fileTypes = $arr[count($arr) - 1];
        $fileName = getFK().".".$fileTypes;
        $targetPath = getcwd()."/assets/media/doc/";
        $valid = true;
        $namaFile = "";
    
        if(strtolower($fileTypes) == "pdf" || strtolower($fileTypes) == "doc" || strtolower($fileTypes) == "docx")
        {
            move_uploaded_file($temp, $targetPath.$fileName);
        }
        else{
            $valid = false;
            $CI->session->set_flashdata('alert', 'File harus format pdf/doc/docx');
        }
    
        $obj = (object) array("fileName" => $fileName, "valid" => $valid);
        return $obj;
    
    }
    
    function uploadPeraturan($input){
        $CI =& get_instance();
        $temp = $input['tmp_name'];
        $arr = explode(".", $input['name']);
        $fileTypes = $arr[count($arr) - 1];
        $fileName = getFK().".".$fileTypes;
        $targetPath = getcwd()."/assets/media/doc/";
        $valid = true;
        $namaFile = "";
    
        if(strtolower($fileTypes) == "pdf" || strtolower($fileTypes) == "doc" || strtolower($fileTypes) == "docx")
        {
            move_uploaded_file($temp, $targetPath.$fileName);
        }
        else{
            $valid = false;
            $CI->session->set_flashdata('alert', 'File harus format pdf/doc/docx');
        }
    
        $obj = (object) array("fileName" => $fileName, "valid" => $valid);
        return $obj;
    
    }
    
    function uploadSlider($input){
        $CI =& get_instance();
        $temp = $input['tmp_name'];
        $arr = explode(".", $input['name']);
        $fileTypes = $arr[count($arr) - 1];
        $fileName = getFK().".".$fileTypes;
        $targetPath = getcwd()."/assets/imgslider/";
        $valid = true;
        $namaFile = "";
    
        if(strtolower($fileTypes) == "jpg" || strtolower($fileTypes) == "png")
        {
            move_uploaded_file($temp, $targetPath.$fileName);
        }
        else{
            $valid = false;
            $CI->session->set_flashdata('alert', 'Gambar harus format jpg atau png');
        }
    
        $obj = (object) array("fileName" => $fileName, "valid" => $valid);
        return $obj;
    
    }
    
    function dtInit($selectedField, $fromTable, $attribute = ""){
        $table = $fromTable;
        $column_search = $selectedField; //set column field database for datatable searchable 
        $column_order = array_merge(array(NULL), $selectedField); //set column field database for datatable orderable
       
        $order = array($selectedField[0] => 'asc'); // default order 
        $field = str_replace(" ", ", ", implode(" ",$selectedField));
        $sqlQuery = "SELECT ".$field." FROM ".$fromTable." ".$attribute;
    
        $result = array("columnSearch" => $selectedField, "field" => $selectedField, "tableName" => $fromTable, "ColumnOrder" => $column_order, "orderBy" => $order, "sqlQuery" => $sqlQuery);
        return (object) $result;
    }
    
    public function getNewNumber($itemCode, $idDistributor = NULL)
	{
        $newCode = 0 + 1;
        $action = "insert";

        if(!empty($idDistributor)) {
            $get = $this->db->get_where("reff_number_series", array("code_name" => $itemCode, "id_distributor" => $idDistributor));        
        }
        else{
            $get = $this->db->get_where("reff_number_series", array("code_name" => $itemCode));
        }
        
        $line = $get->num_rows();
        if($line > 0) {
            $data = $get->row();
            $newCode = (float) $data->last_number + 1;              
            if(strlen($newCode) < strlen($data->last_number)) {
                $sisa = strlen($data->last_number) - strlen($newCode);

                $strCode = "";
                for($x = 1; $x <= $sisa; $x++) {
                    $strCode.= "0";
                }
                $strCode.= $newCode;
                $newCode = $strCode;
            }            
            $action = "update";      
        }
        
        $this->db->trans_start();
        $this->db->set("last_number", $newCode);
        if($action === "insert") {
            $this->db->set("code_name", $itemCode);    
            if(!empty($idDistributor)) {
                $this->db->set("id_distributor", $idDistributor);   
            }      
            $this->db->set("add_user", $this->ci->session->userdata("sanitasDistUserId"));
			$this->db->insert("reff_number_series");
        }
        else{
            $this->db->set("modified_date", date("Y-m-d H:i:s"));
            $this->db->set("modified_user", $this->ci->session->userdata("sanitasDistUserId"));
            $this->db->where("code_name", $itemCode);
            if(!empty($idDistributor)) {
                $this->db->where("id_distributor", $idDistributor);   
            }      
			$this->db->update("reff_number_series");            
        }
        $this->db->trans_complete();

        $charCode = $data->prefix_char.$newCode;
        return $charCode;
    }

    public function isAuto($itemCode, $idDistributor = NULL){
        $get = $this->db->get_where("reff_number_series", array("code_name" => $itemCode));
        
        if(!empty($idDistributor)) {
            $get = $this->db->get_where("reff_number_series", array("code_name" => $itemCode, "id_distributor" => $idDistributor));        
        }
        $data = $get->row();
        
        return $data->is_auto;
    }

    
    public function localDate($date){
		if(!empty($date)){
			$t=explode("-",$date);
			$tanggal=$t[1];
			switch($tanggal){
				case"01";
				 $bulan="Januari";
				break;
				case"02";
				 $bulan="Februari";
				break;
				case"03";
				 $bulan="Maret";
				break;
				case"04";
				 $bulan="April";
				break;
				case"05";
				 $bulan="Mei";
				break;
				case"06";
				 $bulan="Juni";
				break;
				case"07";
				 $bulan="Juli";
				break;
				case"08";
				 $bulan="Agustus";
				break;
				case"09";
				 $bulan="September";
				break;
				case"10";
				 $bulan="Oktober";
				break;
				case"11";
				 $bulan="November";
				break;
				case"12";
				 $bulan="Desember";
				break;
			}
			if($date=="0000-00-00"){
			return '';
			}
			else{
				$output = $t[2]." ".$bulan." ".$t[0];
				$sr = explode(" ",$t[2]);

				if(count($sr) > 1)
					$output = $sr[0]." ".$bulan." ".$t[0].", Jam : ".str_replace(":",".",substr($sr[1],0,5));

				return $output;
			}
		}
		else{
			return '';
		}
	}
    
    /*
    public function updateStock($type = "IN", $idDistributor, $idItem, $qty) {
        $query = $this->db->get_where("mst_stock_item", array("id_item" => $idItem, "id_distributor" =>))
        if(strtoupper($type) === "IN") {

        }
    }
    */
}


 
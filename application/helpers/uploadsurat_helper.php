<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
function uploadSurat($input){
	if (!empty($input['name'])) {
		$temp = $input['tmp_name'];
		$arr = explode(".", $input['name']);
		$fileTypes = $arr[count($arr) - 1];
		$fileName = "SuratUsulan_".$args->nomor_usulan.".".$fileTypes;

		if(strtolower($fileTypes) == "pdf" || strtolower($fileTypes) == "doc" || strtolower($fileTypes) == "docx")
		{
			$namaFile = $nameID.".".$fileTypes;
			move_uploaded_file($temp, $this->targetPath.$fileName);
		}
		else{
			$this->valid = false;
			$this->session->set_flashdata('alert', 'File harus format pdf/doc/docx');
		}
	}
}
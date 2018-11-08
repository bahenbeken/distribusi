<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menucontrol extends MX_Controller {

	private $container;
	private $valid = false;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('accesscontrol');
		$this->load->helper('app');
		$this->load->model('modelUtility');
		$this->load->helper('url');
		$this->container["data"] = NULL;
		LoggedSystem();
    }

    public function formPriviledge($idUser = NULL) {
        $this->container["id_user"] = $idUser;
        $this->container["menu_config"] = $this->getMenuLists($idUser);
        $this->twig->display("form/formPriviledge.html", $this->container);
    }

    public function savePriviledge() {
        $obj = (object) $this->input->post();
        $header = $obj->jumlah_header;
        $idUser = $obj->id_user;

        //var_dump($obj);
        //exit();

        $this->db->where("id_user", $idUser);
        $delete = $this->db->delete("mst_user_akses");

        for($x = 1; $x <= $header; $x++) {
            $jumlah_detail = "jumlah_detail".$x;
            $parent = "parent_".$x;
            $detail = $obj->$jumlah_detail;
            $headerMenu = false;
            for($y = 1; $y <= $detail; $y++) {
                $access = "access_".$x."_".$y;
                $create = "create_".$x."_".$y;
                $update = "update_".$x."_".$y;
                $delete = "delete_".$x."_".$y;
                $submit = "submit_".$x."_".$y;
                $approve = "approve_".$x."_".$y;
                $confirm = "confirm_".$x."_".$y;
                $child = "child_".$x."_".$y;

                $is_access = (isset($obj->$access) ? "yes" : "no");
                $is_create = (isset($obj->$create) ? "yes" : "no");
                $is_update = (isset($obj->$update) ? "yes" : "no");
                $is_delete = (isset($obj->$delete) ? "yes" : "no");
                $is_submit = (isset($obj->$submit) ? "yes" : "no");
                $is_approve = (isset($obj->$approve) ? "yes" : "no");
                $is_confirm = (isset($obj->$confirm) ? "yes" : "no");

                if($is_access === "yes") {
                    
                    $headerMenu = true;
                    $this->db->set("id_user", $idUser);
                    $this->db->set("id_menu", $obj->$child);
                    $this->db->set("is_access", $is_access);
                    $this->db->set("is_create", $is_create);
                    $this->db->set("is_update", $is_update);
                    $this->db->set("is_delete", $is_delete);
                    $this->db->set("is_submit", $is_submit);
                    $this->db->set("is_approve", $is_approve);
                    $this->db->set("is_confirm", $is_confirm);
                    $valid = $this->db->insert("mst_user_akses");
                   
                }
            }
            if($headerMenu) {
                $this->db->set("id_user", $idUser);
                $this->db->set("id_menu", $obj->$parent);
                $this->db->set("is_access", "yes");
                $this->db->set("is_create", "yes");
                $this->db->set("is_update", "yes");
                $this->db->set("is_delete", "yes");
                $this->db->set("is_submit", "yes");
                $this->db->set("is_approve", "yes");
                $this->db->set("is_confirm", "yes");
                $this->db->insert("mst_user_akses");
            }

        }

        $sql = "select * from mst_menu where menu_title in ('Dashboard', 'Logout')";
        $head = $this->db->query($sql)->result();
        foreach ($head as $data) {
            $this->db->set("id_user", $idUser);
            $this->db->set("id_menu", $data->id);
            $this->db->set("is_access", "yes");
            $this->db->set("is_create", "no");
            $this->db->set("is_update", "no");
            $this->db->set("is_delete", "no");
            $this->db->set("is_submit", "no");
            $this->db->set("is_approve", "no");
            $this->db->set("is_confirm", "no");
            $this->db->insert("mst_user_akses");
        }
        
        $this->session->set_flashdata(array("type" => "success", "notify" => "Data berhasil tersimpan!"));
        redirect("/masterdata/menucontrol/formpriviledge/".$idUser);
    }
    

	public function getMenuLists($idUser = NULL){
        $str = "<div class='col-md-10' style='border-bottom:solid 1px #666; margin-bottom:8px;'>
                    <div class='col-md-5'><h5>Menu</h5></div>
                    <div class='col-md-1 text-center'><h5>Access</h5></div>
                    <div class='col-md-1 text-center'><h5>Create</h5></div>
                    <div class='col-md-1 text-center'><h5>Update</h5></div>
                    <div class='col-md-1 text-center'><h5>Delete</h5></div>
                    <div class='col-md-1 text-center'><h5>Submit</h5></div>
                    <div class='col-md-1 text-center'><h5>Approve</h5></div>
                    <div class='col-md-1 text-center'><h5>Confirm</h5></div>
                </div>";

        $sqlHeader = "select a.*, b.is_access, b.is_create, b.is_update, b.is_delete, b.is_submit, b.is_approve, b.is_confirm 
                    from mst_menu a left join mst_user_akses b on b.id_menu=a.id";
        if(!empty($idUser)) {
            $sqlHeader.= " and b.id_user='".$idUser."'"; 
        }
        $sqlHeader.= " where a.status='active' and a.parent_id=0 and a.menu_title not in ('Dashboard', 'Logout') order by a.order_number asc"; 

        

        $menu = $this->db->query($sqlHeader);
        $num = $menu->num_rows();
        if($num > 0) {
            $dataMenu = $menu->result();
            $x = 0;
            foreach ($dataMenu as $menu) {

                $str.= "<div class='col-md-10' style='border-bottom:solid 1px #999; margin-top:3px; padding-bottom:3px;'>
                            <div class='col-md-5'><strong>".$menu->menu_title."</strong></div>
                            <div class='col-md-1 text-center'>&nbsp;</div>
                            <div class='col-md-1 text-center'>&nbsp;</div>
                            <div class='col-md-1 text-center'>&nbsp;</div>
                            <div class='col-md-1 text-center'>&nbsp;</div>
                            <div class='col-md-1 text-center'>&nbsp;</div>
                            <div class='col-md-1 text-center'>&nbsp;</div>
                            <div class='col-md-1 text-center'>&nbsp;</div>
                        </div>";

                $sqlDetail = "select a.*, b.is_access, b.is_create, b.is_update, b.is_delete, b.is_submit, b.is_approve, b.is_confirm 
                            from mst_menu a left join mst_user_akses b on b.id_menu=a.id 
                            ";
                if(!empty($idUser)) {
                    $sqlDetail.= " and b.id_user='".$idUser."'"; 
                }
                $sqlDetail.= " where a.status='active' and a.parent_id='".$menu->id."' order by a.order_number asc"; 
                
                $getChild = $this->db->query($sqlDetail);
                $numChild = $getChild->num_rows();
                if($numChild > 0) {
                    $dataMenuChild = $getChild->result();
                    $x++;
                    $y = 0; 
                    $str.= "<input type='hidden' name='parent_".$x."' id='parent_".$x."' value='".$menu->id."'>";
                    foreach ($dataMenuChild as $child) {
                        $y++;
                        $padding = 15 * $child->level_number;
                        
                        $is_access = ($child->is_access === "yes" ? "checked" : "");
                        $is_create = ($child->is_create === "yes" ? "checked" : "");
                        $is_update = ($child->is_update === "yes" ? "checked" : "");
                        $is_delete = ($child->is_delete === "yes" ? "checked" : "");
                        $is_submit = ($child->is_submit === "yes" ? "checked" : "");
                        $is_approve = ($child->is_approve === "yes" ? "checked" : "");
                        $is_confirm = ($child->is_confirm === "yes" ? "checked" : "");


                        $str.= "<div class='col-md-10' style='border-bottom:solid 1px #999; margin-top:3px;'>
                                <input type='hidden' name='child_".$x."_".$y."' id='child_".$x."_".$y."' value='".$child->id."'>                            
                                <div class='col-md-5' style='padding-left:".$padding."px'>".$child->menu_title."</div>
                                <div class='col-md-1 text-center'><input type='checkbox' name='access_".$x."_".$y."' id='access_".$x."_".$y."' $is_access onclick=\"setActive(this.id)\" value='yes'></div>
                                <div class='col-md-1 text-center'><input type='checkbox' name='create_".$x."_".$y."' id='create_".$x."_".$y."' $is_create value='yes'></div>
                                <div class='col-md-1 text-center'><input type='checkbox' name='update_".$x."_".$y."' id='update_".$x."_".$y."' $is_update value='yes'></div>
                                <div class='col-md-1 text-center'><input type='checkbox' name='delete_".$x."_".$y."' id='delete_".$x."_".$y."' $is_delete value='yes'></div>
                                <div class='col-md-1 text-center'><input type='checkbox' name='submit_".$x."_".$y."' id='submit_".$x."_".$y."' $is_submit value='yes'></div>
                                <div class='col-md-1 text-center'><input type='checkbox' name='approve_".$x."_".$y."' id='approve_".$x."_".$y."' $is_approve value='yes'></div>
                                <div class='col-md-1 text-center'><input type='checkbox' name='confirm_".$x."_".$y."' id='confirm_".$x."_".$y."' $is_confirm value='yes'></div>
                            </div>";                        
                                           
                    }      
                    $str.="<input type='hidden' id='jumlah_detail".$x."' name='jumlah_detail".$x."' value='".$y."'>";              
                }                
                                
            }
            $str.="<input type='hidden' id='jumlah_header' name='jumlah_header' value='".$x."'>";
        }
        return $str;
    }

	public function aksesSetup($userId = null)
	{
		$this->twig->display("grid/gridUser.html", $this->container);
    }

    public function userAkses()
	{
		$this->twig->display("grid/gridAkses.html", $this->container);
    }
    
    public function listDataCode($idDist) {
        $sql = "SELECT a.*, b.code AS distributor_code, b.distributor_name FROM reff_number_series a LEFT JOIN mst_distributor b ON b.id=a.id_distributor WHERE a.id_distributor='".$idDist."'";
		
		$data = $this->db->query($sql)->result();
        $x = 0;
    	foreach($data as $row) { 
			$x++;
            $responce->data[] = array(
                $row->distributor_code." - ".$row->distributor_name,
                $row->code_name,
                $row->description, 
                $row->prefix_char,
                $row->last_number,
				$row->id
			);
		}
		
		echo json_encode($responce);
	}

	public function serialCode($id = NULL)
	{
        $this->container["id_distributor"] = "all"; 
		if($_POST) {
            $args = (object) $this->input->post();
            $this->container["id_distributor"] = $args->id_distributor; 
		}
		
        $this->container["distributor"] = $this->db->get("mst_distributor")->result();
        $this->twig->display("grid/gridSerialCode.html", $this->container);
    }
    
    public function formCode($id = NULL)
    {
        if($_POST){
			$args = (object) $this->input->post();
			$valid = $this->modelUtility->saveCode($args);
            if($valid) {
                    $this->session->set_flashdata(array("type" => "success", "notify" => "Data submition success!"));
            }
            else{
                    $this->session->set_flashdata(array("type" => "warn", "notify" => "Data submition failed to save! please check the paramaters"));
            }
			redirect("/masterdata/utility/formcode");
		}

        if(!empty($id)) {
            
            $edit = $this->db->query("select * from `reff_number_series` where id='".$id."'")->row();
            $this->container["edit"] = $edit;
        }

        $this->twig->display("form/formCode.html", $this->container);
    }

	public function deleteUser($id){
		$this->db->where("id_user", $id);
		$this->valid = $this->db->delete("mst_user");
		if($this->valid)
		{
			$this->session->set_flashdata(array("type" => "success", "notify" => "Data deleted from the system!"));
		}
		redirect("/masterdata/utility/user");
	}


	public function changePassword()
	{
		if($_POST){
			$args = (object) $this->input->post();
			if($args->passold === $this->session->userdata("sanitasPassword")) {
				if($args->pass1 === $args->pass2) {
					$this->valid = $this->modelUtility->changePassword($args);
				}
				else{
					$this->session->set_flashdata(array("type" => "warn", "notify" => "Update failed! your new password is not same"));
				}
			}
			else{
				$this->session->set_flashdata(array("type" => "warn", "notify" => "Update failed! old password is incorrect"));
			}


			if($this->valid) {
				$this->session->set_flashdata(array("type" => "success", "notify" => "Password updated!"));
			}
			else {
				$this->session->set_flashdata(array("type" => "warn", "notify" => "Password gagal di ganti, pastikan password anda sama!"));
			}
			redirect("/main/utility/changepassword");
		}
		$this->twig->display("form/formGanpass.html", $this->container);
	}



}

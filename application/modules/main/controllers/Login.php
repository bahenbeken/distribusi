<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MX_Controller {

	private $container;
	private $valid = false;

	public function __construct()
	{
		parent::__construct();
		$this->container["content"] = NULL;
		$this->load->model('modelAuthentication');
		$this->load->helper('url');

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
	}

	public function sendLinkReset($keyChar, $emailTo) {



		$output = false;

		$body = "
		<p align=\"center\">
		  <div style=\"width:60%; padding:20px;font-family:arial; font-size: 14px; color: #666\">
		    This message send from system, please do not reply this email, for any further information you can contact our Administrator.
		  </div>
		</p>

		<table width=\"350px\" style=\"margin-top:50px;\" align=\"center\">
		  <tr>
		    <td style=\"background-color:#ececec; border-radius:10px; border:solid 1px #ccc;\">
		        <img src=\"sanitas.dev.techno1.id/assets/img/sanitas_logo.png\" style=\"width:100%\">
		      <p align=\"center\">
		        <a href=\"http://sanitas.dev.techno1.id/main/login/index/".$keyChar."\" style=\"text-decoration:none; color:#fff; cursor:pointer\">
		          <div style=\"width:35%; padding: 10px; text-align: center;  cursor:pointer; background-color:#da251b; background-image: linear-gradient(to bottom, #a22a23 0%, #e42419 100%); font-family:arial; font-size: 12px; border-radius:5px; color:#fff; margin: auto; left:0; righ:0;\">
		            RESET PASSWORD
		          </div>
		        </a>
		        <hr style=\"width:90%; margin-top:40px;\" />
		        <div style=\"padding-left: 20px; padding-right: 20px; font-family:arial; font-size:11px; color:#666\">
		          You can change your password by clicking the button above, please do it immediately.
		        </div>
		      </p>
		    </td>
		  </tr>
		</table>";


		$from = "technoone263@gmail.com";

		$this->email->set_newline("\r\n");
        $this->email->from($from, 'No Reply - SANITAS');
        $this->email->to($emailTo);
        $this->email->subject('SANITAS Mobil Backend Reset Password');
        $this->email->message($body);

	   //Send mail
	  if($this->email->send()) $output = true;

		return $output;
	}

	public function getFK()
	{
		$c = uniqid(rand(), true);
		$md5c = md5($c);
		return $md5c;
	}

	public function index($resetKey = NULL)
	{
		if($_POST)
		{
			$userName = $this->input->post("username");
			$password = $this->input->post("password");
			$tahun = $this->input->post("tahun");

			$this->valid = $this->modelAuthentication->loginAuth($userName, $password);
			if($this->valid)
			{

                $this->session->set_flashdata(array("type" => "success", "notify" => "Login success!"));
                
				redirect("/main/index");
			}
			else{

				$this->session->set_flashdata(array("type" => "warn", "notify" => "Undefine authentication, please check your Id!"));
				redirect("/main/login");
			}
		}

		if(!empty($resetKey)) {
			$this->container["reset_password"] = "";

			$query = $this->db->get_where("admintable", array("forgot_password" => $resetKey));
			if($query->num_rows() > 0) {
				$data = $query->row();
				$table = "admintable";
				$userId = $data->id;

				$this->container["reset_password"] = "reset";
				$this->container["user_table"] = $table;
				$this->container["user_id"] = $userId;

			}
			else{
				$query = $this->db->get_where("usertable", array("forgot_password" => $resetKey));
				if($query->num_rows() > 0) {
					$data = $query->row();
					$table = "usertable";
					$userId = $data->id;

					$this->container["reset_password"] = "reset";
					$this->container["user_table"] = $table;
					$this->container["user_id"] = $userId;
				}
			}
		}


		$this->container["tahun"] = date("Y");
		$this->twig->display("form/formLogin.html", $this->container);
	}

	public function forgotPassword()
	{
		if($_POST) {
			$args = (object) $this->input->post();
			$keyChar = "";

			$query = $this->db->get_where("admintable", array("email" => $args->email));
			if($query->num_rows() > 0) {
				$data = $query->row();
				$table = "admintable";
				$userId = $data->id;
				$keyChar = $this->getFK();

				$this->db->set("forgot_password", $keyChar);
				$this->db->where("id", $userId);
				$this->valid = $this->db->update($table);

			}
			else{
				$query = $this->db->get_where("usertable", array("email" => $args->email));
				if($query->num_rows() > 0) {
					$data = $query->row();
					$table = "usertable";
					$userId = $data->id;
					$keyChar = $this->getFK();

					$this->db->set("forgot_password", $keyChar);
					$this->db->where("id", $userId);
					$this->valid = $this->db->update($table);
				}
			}

			if($this->valid) {
				$this->valid = $this->sendLinkReset($keyChar, $args->email);
			}

			if($this->valid) {
				$this->session->set_flashdata(array("type" => "success", "notify" => "Password reset link has sent to your email, please check!"));
			}
			else{
				$this->session->set_flashdata(array("type" => "warn", "notify" => "Undefine email address!"));
			}
		}

		$this->twig->display("form/formForgotPassword.html", $this->container);
	}


	public function changePassword()
	{

		if($_POST) {
            $args = (object) $this->input->post();
            
            $exist = $this->db->get_where("mst_user", array("username" => $args->username));
            
            if($exist->num_rows() > 0) {
                
                if($args->pass1 === $args->pass2) {                    

                    $this->db->set("password", md5($args->pass1));
                    $this->db->where("username", $args->username);
                    $this->valid = $this->db->update("mst_user");
                    
                    if($this->valid) {
                        $this->session->set_flashdata(array("type" => "success", "notify" => "Ganti password berhasil dilakukan!"));
                    }
                }
                else{
                    $this->session->set_flashdata(array("type" => "error", "notify" => "Ganti password tidak bisa dilakukan! password tidak sama"));
                }
            }
            else{
                $this->session->set_flashdata(array("type" => "error", "notify" => "Ganti password tidak bisa dilakukan! username tidak diketahui"));
            }
            redirect("main/login/changepassword");
		}
		$this->twig->display("form/formGanpass.html", $this->container);
	}

}

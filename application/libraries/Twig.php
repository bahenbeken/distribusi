<?php
/**
 * Part of CodeIgniter Simple and Secure Twig
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/codeigniter-ss-twig
 */

// If you don't use Composer, uncomment below

require_once APPPATH . 'third_party/Twig-1.27.0/lib/Twig/Autoloader.php';
Twig_Autoloader::register();


class Twig
{
	private $config = [];

	private $functions_asis = [
		'base_url', 'site_url'
	];
	private $functions_safe = [
		'form_open', 'form_close', 'form_error', 'set_value', 'form_hidden'
	];

	/**
	 * @var bool Whether functions are added or not
	 */
	private $functions_added = FALSE;

	/**
	 * @var Twig_Environment
	 */
	private $twig;

	/**
	 * @var Twig_Loader_Filesystem
	 */
	private $loader;
	
    private $CI;	
    private $db;
	private $template_module_dir;
	protected $_twig;
    protected $_twig_loader;

	public function __construct($params = [])
	{

		$this->CI =& get_instance();
        $this->CI->load->library('session');        
		$this->db = $this->CI->db;

		$this->template_module_dir = APPPATH.'modules/'.$this->CI->router->fetch_module().'/views/';	
		
		// default config
		$this->config = [
			'paths' => [$this->template_module_dir, VIEWPATH],
			'cache' => APPPATH . 'cache/twig',
		];
		

		$this->config = array_merge($this->config, $params);
		if (isset($params['functions']))
		{
			$this->functions_asis = 
				array_unique(
					array_merge($this->functions_asis, $params['functions'])
				);
		}
		if (isset($params['functions_safe']))
		{
			$this->functions_safe = 
				array_unique(
					array_merge($this->functions_safe, $params['functions_safe'])
				);
		}
		

		$this->addGlobal("base_url", $this->CI->config->base_url());
        $this->addGlobal("session", $this->CI->session);
        $this->addGlobal("menu", $this->getMenuList());
        $this->addGlobal("css_handle", $this->roleLimitation());

	
    }

    public function roleLimitation(){
        $url = uri_string();
        $userId = $this->CI->session->userdata('sanitasDistUserId');
        
        $sql = "SELECT b.`is_access`, b.`is_create`, b.`is_update`, b.`is_delete`, 
            b.`is_approve`, b.`is_submit`, b.`is_delete`, b.`is_confirm`, a.* 
            FROM mst_menu a JOIN mst_user_akses b ON b.`id_menu`=a.`id`
            WHERE b.`id_user`='".$userId."' AND LOWER(REPLACE(a.menu_link,'.html',''))=LOWER('".$url."')";
        
        $query = $this->CI->db->query($sql);
        $num = $query->num_rows();

        $cssHandle = "";
        if($num > 0) {
            $datas = $this->CI->db->query($sql)->row();

            $cssHandle.= "<style>";
            
                if($datas->is_create === "no") {            
                    $cssHandle.= ".addButton{display:none !important;}";
                }

                if($datas->is_update === "no") {            
                    $cssHandle.= "modify{display:none !important;}";
                }

                if($datas->is_delete === "no") {            
                    $cssHandle.= "remove{display:none !important;}";
                }

            $cssHandle.= "</style>";


            $cssHandle.= "<script>";

                if($datas->is_submit === "no") {            
                    $cssHandle.= "$('#is_submit').val('no');";
                }

                if($datas->is_approve === "no") {        
                    $cssHandle.= "$('#is_approve').val('no');";    
                }

                if($datas->is_confirm === "no") {      
                    $cssHandle.= "$('#is_confirm').val('no');";      
                }

            $cssHandle.= "</script>";
            
        }
        $cssHandle.= "<script>";
        $cssHandle.= "
        
            if($('.submitTrans').length) {                
                var is_yes = window.parent.$('#is_submit').val();
                if(is_yes != 'yes') {
                    $('.submitTrans').prop('disabled', true);
                }                
            }

            if($('.approveTrans').length) {                
                var is_yes = window.parent.$('#is_approve').val();
                if(is_yes != 'yes') {
                    $('.approveTrans').prop('disabled', true);
                }                
            }

            if($('.confirmTrans').length) {                
                var is_yes = window.parent.$('#is_confirm').val();
                if(is_yes != 'yes') {
                    $('.confirmTrans').prop('disabled', true);
                }                
            }

        ";
        $cssHandle.= "</script>";
        return $cssHandle;
    }
    
    public function getMenuList($parent = 0){
        $idUser = $this->CI->session->userdata('sanitasDistUserId');
        $str = "<ul id='menu' class='bg-blue dker'>";
        
        $sqlHeader = "select a.*, b.is_access, b.is_create, b.is_update, b.is_delete, b.is_submit, b.is_approve, b.is_confirm 
                    from mst_menu a join mst_user_akses b on b.id_menu=a.id where a.status='active' 
                    and a.parent_id='".$parent."' and b.id_user='".$idUser."' order by a.order_number asc"; 
        
        $menu = $this->db->query($sqlHeader);
        $num = $menu->num_rows();
        if($num > 0) {
            $dataMenu = $menu->result();
            foreach ($dataMenu as $menu) {
                
                $str.= "<li class=''>";
                
                $sqlChild = "select a.*, b.is_access, b.is_create, b.is_update, b.is_delete, b.is_submit, b.is_approve, b.is_confirm 
                    from mst_menu a join mst_user_akses b on b.id_menu=a.id where a.status='active' 
                    and a.parent_id='".$menu->id."' and b.id_user='".$idUser."' order by a.order_number asc"; 

                $getChild = $this->db->query($sqlChild);
                $numChild = $getChild->num_rows();
                if($numChild > 0) {
                    $dataMenuChild = $getChild->result();
                    $str.= "<a href='javascript:;'><i class='fa ".$menu->fa_class."'></i><span class='link-title'>&nbsp;".$menu->menu_title."</span><span class='fa arrow'></span></a>";       
                    $str.="<ul class='collapse'>";
                    foreach ($dataMenuChild as $child) {
                        $getGrandChild = $this->db->query("select * from mst_menu where parent_id='".$child->id."' and status='active'");
                        $numGrandChild = $getGrandChild->num_rows();
                        if($numGrandChild > 0) { 
                            $dataMenuGrandChild = $getGrandChild->result();
                            $str.= "<li><a href='".$child->menu_link."'><i class='fa ".$child->fa_class."'></i>&nbsp;".$child->menu_title."<span class='fa arrow'></span></a>";
                                $str.= "<ul class='collapse'>";
                                    foreach ($dataMenuGrandChild as $grandChild) { 
                                        $str.="<li><a href='".base_url().$grandChild->menu_link."'><i class='fa ".$grandChild->fa_class."'></i>&nbsp;".$grandChild->menu_title."</a></li>";
                                    }
                                $str.= "</ul>";
                            $str.= "</li>";
                        }
                        else{
                            $str.="<li><a href='".base_url().$child->menu_link."'><i class='fa ".$child->fa_class."'></i>&nbsp;".$child->menu_title."</a></li>";
                        }                        
                    }
                    $str.="</ul>";                    
                }
                else{
                    $str.= "<a href='".base_url().$menu->menu_link."'><i class='fa ".$menu->fa_class."'></i><span class='link-title'>&nbsp;".$menu->menu_title."</span></a>";       
                }                
                $str.= "</li>";                
            }
            $str.= "</ul>";
        }
        return $str;
    }

	protected function resetTwig()
	{
		$this->twig = null;
		$this->createTwig();
	}

	protected function createTwig()
	{
		// $this->twig is singleton
		if ($this->twig !== null)
		{
			return;
		}

		if (ENVIRONMENT === 'production')
		{
			$debug = FALSE;
		}
		else
		{
			$debug = TRUE;
		}

		if ($this->loader === null)
		{
			$this->loader = new \Twig_Loader_Filesystem($this->config['paths']);
		}

		$twig = new \Twig_Environment($this->loader, [
			'cache'      => $this->config['cache'],
			'debug'      => $debug,
			'autoescape' => TRUE,
		]);

		if ($debug)
		{
			$twig->addExtension(new \Twig_Extension_Debug());
		}

		$this->twig = $twig;
	}

	protected function setLoader($loader)
	{
		$this->loader = $loader;
	}

	/**
	 * Registers a Global
	 * 
	 * @param string $name  The global name
	 * @param mixed  $value The global value
	 */
	public function addGlobal($name, $value)
	{
		$this->createTwig();
		$this->twig->addGlobal($name, $value);
	}

	/**
	 * Renders Twig Template and Set Output
	 * 
	 * @param string $view   Template filename without `.twig`
	 * @param array  $params Array of parameters to pass to the template
	 */
	public function display($view, $params = [])
	{
		$CI =& get_instance();
		$CI->output->set_output($this->render($view, $params));
	}

	/**
	 * Renders Twig Template and Returns as String
	 * 
	 * @param string $view   Template filename without `.twig`
	 * @param array  $params Array of parameters to pass to the template
	 * @return string
	 */
	public function render($view, $params = [])
	{
		$this->createTwig();
		// We call addFunctions() here, because we must call addFunctions()
		// after loading CodeIgniter functions in a controller.
		$this->addFunctions();

		$view = $view;
		return $this->twig->render($view, $params);
	}

	protected function addFunctions()
	{
		// Runs only once
		if ($this->functions_added)
		{
			return;
		}

		// as is functions
		foreach ($this->functions_asis as $function)
		{
			if (function_exists($function))
			{
				$this->twig->addFunction(
					new \Twig_SimpleFunction(
						$function,
						$function
					)
				);
			}
		}

		// safe functions
		foreach ($this->functions_safe as $function)
		{
			if (function_exists($function))
			{
				$this->twig->addFunction(
					new \Twig_SimpleFunction(
						$function,
						$function,
						['is_safe' => ['html']]
					)
				);
			}
		}

		// customized functions
		if (function_exists('anchor'))
		{
			$this->twig->addFunction(
				new \Twig_SimpleFunction(
					'anchor',
					[$this, 'safe_anchor'],
					['is_safe' => ['html']]
				)
			);
		}

		$this->functions_added = TRUE;
	}

	/**
	 * @param string $uri
	 * @param string $title
	 * @param array  $attributes [changed] only array is acceptable
	 * @return string
	 */
	public function safe_anchor($uri = '', $title = '', $attributes = [])
	{
		$uri = html_escape($uri);
		$title = html_escape($title);
		
		$new_attr = [];
		foreach ($attributes as $key => $val)
		{
			$new_attr[html_escape($key)] = html_escape($val);
		}

		return anchor($uri, $title, $new_attr);
	}

	/**
	 * @return \Twig_Environment
	 */
	public function getTwig()
	{
		$this->createTwig();
		return $this->twig;
	}
}

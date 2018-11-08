<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$root = "http://".$_SERVER['HTTP_HOST']; 
$root .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<meta name="description" content="SISTEM PENGELOLAAN BARANG MILIK NEGARA">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SISTEM PENGELOLAAN BARANG MILIK NEGARA</title>
<link href="<?php echo $root ?>assets/css/adminstyles.css" rel="stylesheet" type="text/css">

<link rel="shortcut icon" type="image/x-icon" href="<?php echo $root ?>assets/img/favicon.png" />
<script type="text/javascript" src="<?php echo $root ?>assets/js/vendors/modernizr/modernizr.custom.js"></script>
</head>

<body>
<div class="standalone-page-wrapper"> 
  
  <!--Top Block-->
  <div class="error-top-block">
    <div class="error-top-block-image"> <img src="<?php echo $root ?>assets/images/error-robot.png" alt="Ooops!" /> </div>
  </div>
  <!--/Top Block--> 
  <!--Bottom Block-->
  <div class="error-bottom-block">
    <div class="col-md-6 col-md-offset-3 error-description">
      <div class="error-code">&nbsp;</div>
      <div class="error-meaning">Error 404 <br/> Halaman tidak ditemukan</div>
      <br/>
      <img src="<?php echo $root ?>assets/img/logosystem.png" width="200px">
      <br/>
      <br/>
      <div class="todo">
        <h4>Pastikan anda memasukan alamat domain yang benar dan sesuai</h4>
      </div>

      <div class="copyrights"> <a href="<?php echo $root ?>">Kembali Ke Depan</a></div>

    </div>
  </div>
  <!--/Bottom Block--> 
</div>
</body>
</html>

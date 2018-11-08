<?php
	
	/** Force to display error */
	ini_set("display_errors", "on");
	
	/** MySQL database */
	require_once "connection.php";
	
	/** [IMPORTANT!!] Cross-Origin is not allowed, please configure this with your app url */
	$origin_url = "http://localhost:8100";
			
	// Hide error
	error_reporting(0);
			
	// Initialize array result
	$result = array();
	
	// Initialize data result
	$data = [];
		
	// Get filter
	$filter = isset($_GET['filter']) ? urldecode($_GET['filter']) : "";
		
	// Get RAW data	
	$input = file_get_contents('php://input');
	
	
	
	/** START : Processing Server Request */
	
	// Method #1 : POST
	if($_SERVER['REQUEST_METHOD'] == "POST")
		require_once "method_post.php";
	
	// Method #2 : GET
	else if($_SERVER['REQUEST_METHOD'] == "GET")
		require_once "method_get.php";
	
	// Method #3 : PATCH (Update)
	else if($_SERVER['REQUEST_METHOD'] == "PATCH")
		require_once "method_patch.php";
	
	// Method #4 : DELETE
	else if($_SERVER['REQUEST_METHOD'] == "DELETE")
		require_once "method_delete.php";
	
	/** END : Processing Server Request */
	
	
	// Return result as json and allow cross origin
	header('Access-Control-Allow-Origin: *');
	header('Content-Type: application/json');
	
	header("Content-Type", "application/json; charset=UTF-8");
    //header('Access-Control-Allow-Origin: ' . $origin_url);
	header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE');
	header('Access-Control-Allow-Headers: Content-Type');
	
	// Encode data to json
	$result = json_encode($data, JSON_PRETTY_PRINT);
		
	echo $result;
	
?>

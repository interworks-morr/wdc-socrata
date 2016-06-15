<?php
/**
 * Receives a columns for a given Socrata site/dataset ID
 * 
 * @author Matthew Orr <matthew.orr@interworks.com>
 */

//imports
require_once("socrata.php");

//get the request variables
$site = $_REQUEST['site'];
$datasetID = $_REQUEST['dataset_id'];

//set up output
$output = array();

//if search term provided, get auto-complete entries
if(strlen($site) > 0 && strlen($datasetID) > 0)
{
	//make request to site to determine columns
	$socrata = new Socrata($site);
	$params = array("\$select" => "*", "\$limit" => 1);
	$response = $socrata->get($datasetID, $params);
	
	//output the data
	if(count($response) > 0)
	{
		$output = array_keys($response[0]);
		sort($output);
	}
}

//show the output
echo json_encode($output);
?>

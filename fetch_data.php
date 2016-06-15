<?php

/**
 * Retrieves Socrata data for a given site and dataset ID
 * and returns it (along with schema) for a Tableau Web Data Connector
 * 
 * @author Matthew Orr <matthew.orr@interworks.com>
 */

//imports
require_once("socrata.php");

//constants
define('TYPE_SCHEMA',"schema");
define('TYPE_DATA',"data");

//custom functions
function determineDataType($value)
{
	if(is_null($value))
	{
		$dataType = "string";
	}
	elseif(is_bool($value))
	{
		$dataType = "bool";
	}
	elseif(is_numeric($value))
	{
		$numericValue = $value + 0; //returns either a float or int
		if(is_float($numericValue))
		{
			$dataType = "float";
		}
		else
		{
			$dataType = "int";
		}
	}
	elseif(strtotime($value))
	{
		$dataType = "datetime";
	}
	else
	{
		$dataType = "string";
	}
	
	return $dataType;
}

//get request vars
if(empty($_GET['type']))
{
	die("ERROR: Please specify whether you need the schema or the data");
}
elseif(empty($_GET['site']))
{
	die("ERROR: Please specify the Socrata site");
}
elseif(empty($_GET['dataset_id']))
{
	die("ERROR: Please specify the Socrata dataset ID");
}
else
{
	$type = $_GET['type'];
	$site = $_GET['site'];
	$datasetID = $_GET['dataset_id'];
}

//generate the specified output
$output = "";
$socrata = new Socrata($site);
if($type == TYPE_SCHEMA)
{
	//initialize columns
	$columns = array();
	
	//make request to site to determine columns
	$params = array("\$select" => "*", "\$limit" => 1);
	$response = $socrata->get($datasetID, $params);
	
	//validate
	if(count($response) != 1)
	{
		die("ERROR: Invalid response given");
	}
	
	//loop through column names
	$columnNames = array_keys($response[0]);
	foreach($columnNames as $columnName)
	{
		$dataType = determineDataType($response[0][$columnName]);
		$column = ["id" => $columnName, "alias" => $columnName, "dataType" => $dataType];
		$columns[] = $column;
	}
	
	//build table info
	$unsafeCharacters = ['.','-','/',':','='];
	$tableauSafeSite = str_replace($unsafeCharacters,"_",$site);
	$tableauSafeDatasetID = str_replace($unsafeCharacters,"_",$datasetID);
	$tableInfo = ["id" => $tableauSafeSite . "_" . $tableauSafeDatasetID,
	              "alias" => "$site $datasetID",
	              "columns" => $columns];
	
	$output = json_encode($tableInfo);
}
elseif($type == TYPE_DATA)
{
	//get remainder of form values
	$where = $_GET['where'];
	$limit = $_GET['limit'];
	
	//make request to google
	$params = array("\$where" => $where, "\$limit" => $limit);
	$response = $socrata->get($datasetID, $params);
	$output = json_encode($response);
}
else
{
	die("ERROR: Unknown type ($type).  Please specify schema or data.");
}
echo $output;

?>

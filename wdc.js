/**
 * Retrieves Socrata data for a given site, dataset, and where clause
 * and returns it (or just the schema) in the format needed by
 * a Tableau Web Data Connector
 * 
 * @author Matthew Orr <matthew.orr@interworks.com>
 */

$(function() {
	
	//event handlers
	function submitButtonOnClick()
	{
		try
		{
			tableau.connectionName = "Socrata: " + $('#site').val();
			
			//store the form data because the submit causes it to disappear
			var formData = {"site":$('#site').val(),
			                "dataset_id":$("#dataset_id").val(),
			                "where":$("#where").val(),
			                "limit":$("#limit").val()}
			tableau.connectionData = JSON.stringify(formData);
			tableau.submit();
		}
		catch(error)
		{
			alert("There was a problem using the Tableau web data connector javascript library. " + error);
		}
	}
	
	//tableau web data connector functionality
	try
	{
		var myConnector = tableau.makeConnector();
		myConnector.getSchema = function (schemaCallback)
		{
			var formData = JSON.parse(tableau.connectionData);
			var url = "fetch_data.php?type=schema&site=" + formData["site"] + "&dataset_id=" + formData["dataset_id"];
			$.getJSON(url,function (data){ schemaCallback([data]); });
		};
		
		myConnector.getData = function(table, doneCallback)
		{
			var formData = JSON.parse(tableau.connectionData);
			var url = "fetch_data.php?type=data&site=" + formData["site"] + "&dataset_id=" + formData["dataset_id"] + "&where=" + formData["where"] + "&limit=" + formData["limit"];
			$.getJSON(url,function(data)
			{
				table.appendRows(data);
				doneCallback();
			});
		};
		tableau.registerConnector(myConnector);
	}
	catch(error)
	{
		alert("There was a problem loading the Tableau web data connector javascript library.");
	}
	
	
	//onload functionality
	function wdcInitialize()
	{
		//show/hide warning message
		$('#tableau-warning-msg').hide();
		if (typeof tableauVersionBootstrap  == 'undefined' || !tableauVersionBootstrap)
		{
			$('#tableau-warning-msg').show();
		}
		
		//set up event handler for submit button
		$("#submitButton").click(submitButtonOnClick);
	}
	$(document).ready(wdcInitialize);
});

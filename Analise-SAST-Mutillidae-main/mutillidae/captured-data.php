
<?php
	/* Known Vulnerabilities
	 * Cross Site Scripting, Cross Site Scripting via HTTP Headers, 
	 * Denial of Service via Logging
	 */

	switch ($_SESSION["security-level"]){
   		case "0": // This code is insecure
   		case "1": // This code is insecure
   			// DO NOTHING: This is insecure		
			$lEncodeOutput = FALSE;
			$lLimitOutput= FALSE;
		break;
	    		
   		case "2":
   		case "3":
			
   		case "4":
   		case "5": // This code is fairly secure
  			/* 
  			 * NOTE: Input validation is excellent but not enough. The output must be
  			 * encoded per context. For example, if output is placed in HTML,
  			 * then HTML encode it. Blacklisting is a losing proposition. You 
  			 * cannot blacklist everything. The business requirements will usually
  			 * require allowing dangerous charaters. In the example here, we can 
  			 * validate username but we have to allow special characters in passwords
  			 * least we force weak passwords. We cannot validate the signature hardly 
  			 * at all. The business requirements for text fields will demand most
  			 * characters. Output encoding is the answer. Validate what you can, encode it
  			 * all.
  			 */
   			// encode the output following OWASP standards
   			// this will be HTML encoding because we are outputting data into HTML
			$lEncodeOutput = TRUE;
			
			/*
			 *  If DOS defenses are enabled, we limit output. An attacker can easily cause 
			 *  the logs to fill . This in itself may or may not pose a problem. 
			 *  But filling logs which display is a type of ampliphication attack. 
			 *  If one attacker puts 10,000 rows in the log then 10 innocent 
			 *  users view those logs, the system will have to display 100,000 log rows (10 users * 10,000 rows). 
			 *  Amplifications attacks are also done by sending single IP packets to networks 
			 *  which will broadcast the packet thus ampliphying the packet many times.
			 */
			$lLimitOutput= TRUE;
   		break;
   	}// end switch		

   	if(isset($_GET["deleteLogs"])){
		$query = "TRUNCATE TABLE captured_data";

		$result = $conn->query($query);
		if (!$result) {
	    	throw (new Exception('Error executing query: '.$conn->error, $conn->errorno));
	    }// end if	
	}// end if isset
   	
?>

<div class="page-title">Captured Data</div>

<?php include_once './includes/back-button.inc';?>

<!-- BEGIN HTML OUTPUT  -->
<table style="margin-left:auto; margin-right:auto; width: 600px;">
	<tr>
		<td class="form-header">Captured Data Page</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td>
			This page shows the data captured by page <a href="index.php?page=capture-data.php">capture-data.php</a>.
			There should also be a file with the same data since capture-data.php tries to save the data to a table
			and a file. The table contents are being displayed on this page. On this system, the 
			file should be found in <?php print pathinfo($_SERVER["SCRIPT_FILENAME"], PATHINFO_DIRNAME); ?>.
			The database table is named captured_data.
		</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
</table>
<span title="Click to refresh captured data log" onclick="document.location.reload(true);" style="cursor: pointer;margin-right:35px;font-weight: bold;">
	<img width="32px" height="32px" src="./images/refresh-button-48px-by-48px.png" style="vertical-align:middle;" />
	Refresh
</span>
<span title="Click to delete captured data log. This deletes the database table only. The text file is not affected." onclick="document.location='./index.php?page=captured-data.php&deleteLogs=deleteLogs';" style="cursor: pointer;font-weight: bold;">
	<img width="32px" height="32px" src="./images/delete-icon-256-256.png" style="vertical-align:middle;" />
	Delete Capured Data
</span>
<br/>

<?php
	try{// to draw table
		$query = "SELECT ip_address, hostname, port, user_agent_string, referrer, data, capture_date 
				  FROM captured_data 
				  ORDER BY capture_date DESC";

		if ($lLimitOutput){
	    	$query .= " LIMIT 20";
	    }// end if
		
		$result = $conn->query($query);
		if (!$result) {
	    	throw (new Exception('Error executing query: '.$conn->error, $conn->errorno));
	    }// end if	

		// we have rows. Begin drawing output.
		echo '<table border="1px" width="100%" class="main-table-frame">';
		echo '<tr class="report-header"><td colspan="10">'.$result->num_rows.' captured records found</td></tr>';
	    echo '<tr class="report-header">
			    <td><b>Hostname</b></td>
			    <td><b>Client IP Address</b></td>
			    <td><b>Client Port</b></td>
    			<td><b>User Agent</b></td>
    			<td><b>Referrer</b></td>			    
			    <td><b>Data</b></td>
			    <td><b>Date/Time</b></td>
		    </tr>';

	    if ($lLimitOutput){
	    	echo '<tr><td class="error-header" colspan="10">Note: DOS defenses enabled. Rows limited to last 20.</td></tr>';
	    }// end if

	    $lRowNumber = 0;
	    while($row = $result->fetch_object()){
	    	$lRowNumber++;
			
			if(!$lEncodeOutput){
				$lHostname = $row->hostname;
				$lClientIPAddress = $row->ip_address;
				$lClientPort = $row->port;
				$lClientUserAgentString = $row->user_agent_string;
				$lClientReferrer = $row->referrer;				
				$lData = $row->data;
				$lCaptureDate = $row->capture_date;
			}else{
				$lHostname = $Encoder->encodeForHTML($row->hostname);
				$lClientIPAddress = $Encoder->encodeForHTML($row->ip_address);
				$lClientPort = $Encoder->encodeForHTML($row->port);
				$lClientUserAgentString = $Encoder->encodeForHTML($row->user_agent_string);
				$lClientReferrer = $Encoder->encodeForHTML($row->referrer);
				$lData = $Encoder->encodeForHTML($row->data);
				$lCaptureDate = $Encoder->encodeForHTML($row->capture_date);				
			}// end if
							
			echo "<tr>
					<td>{$lHostname}</td>
					<td>{$lClientIPAddress}</td>
					<td>{$lClientPort}</td>
					<td>{$lClientUserAgentString}</td>
					<td>{$lClientReferrer}</td>
					<td>{$lData}</td>
					<td>{$lCaptureDate}</td>
				</tr>\n";
		}//end while $row
		echo "</table>";
	} catch (Exception $e) {
		echo $CustomErrorHandler->FormatError($e, "Error writing rows.");
	}// end try;
?>

<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');



	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    include('include/config/logging.php');


?>

<?php

    include ("menu-reports-logs.php");
  	
?>	
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"  onclick="javascript:toggleShowDiv('helpPage')"><? echo $l['Intro']['replogsdaloradius.php']; ?>
		<h144>+</h144></a></h2>

		<div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['replogsdaloradius'] ?>
			<br/>
		</div>
		<br/>

<?php

/*******************************************************************
* Extension name: radius log file                                  *
*                                                                  *
* Description:                                                     *
* this script displays the radius log file ofcourse                *
* proper premissions must be applied on the log file for the web   *
* server to be able to read it                                     *
*                                                                  *
* Author: Liran Tal <liran@enginx.com>                             *
*                                                                  *
*******************************************************************/


if (isset($configValues['CONFIG_LOG_FILE'])) {
	$logfile = $configValues['CONFIG_LOG_FILE'];

	if (!file_exists($logfile)) {
	        echo "<br/><br/>
	                error reading log file: <br/><br/>
	                looked for log file in $logfile but couldn't find it.<br/>
	                if you know where your daloradius log file is located, set it's location in your library/daloradius.conf file";
	        exit;
	}


	if (is_readable($logfile) == false) {
	        echo "<br/><br/>
	                error reading log file: <u>$logfile</u> <br/><br/>
	                possible cause is file premissions or file doesn't exist.<br/>";
	} else {
	        if ($filedata = file_get_contents($logfile)) {
	                $ret = eregi_replace("\n", "<br>", $filedata);
	                echo $ret;
	        }
	}
}

?>

		</div>
		
		<div id="footer">
		
								<?php
        include 'page-footer.php';
?>
		
		</div>
		
</div>
</div>


</body>
</html>

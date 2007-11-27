<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];

	include('library/check_operator_perm.php');

	//setting values for the order by and order type variables
	isset($_REQUEST['orderBy']) ? $orderBy = $_REQUEST['orderBy'] : $orderBy = "username";
	isset($_REQUEST['orderType']) ? $orderType = $_REQUEST['orderType'] : $orderType = "asc";


	isset($_REQUEST['username']) ? $username = $dbSocket->escapeSimple($_REQUEST['username']) : $username = "";
	isset($_REQUEST['enddate']) ? $enddate = $dbSocket->escapeSimple($_REQUEST['enddate']) : $enddate = "";
	isset($_REQUEST['startdate']) ? $startdate = $dbSocket->escapeSimple($_REQUEST['startdate']) : $startdate = "";

	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query for active accounting records on page: ";

?>


<?php
	
	include("menu-accounting.php");
	
?>
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><? echo $l['Intro']['acctactive.php']; ?></a></h2>
				
                <div id="helpPage" style="display:none;visibility:visible" >
			<?php echo $l['helpPage']['acctactive'] ?>		
		</div>
		<br/>


<?php

	include 'library/opendb.php';
    include 'library/datediff.php';
	include 'include/management/pages_numbering.php';		// must be included after opendb because it needs to read the CONFIG_IFACE_TABLES_LISTING variable from the config file
	$currdate = date("j M Y");

	//orig: used as maethod to get total rows - this is required for the pages_numbering.php page

	$sql = "select distinct(".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName) as username, ".$configValues['CONFIG_DB_TBL_RADCHECK'].".attribute as attribute, ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Value maxtimeexpiration, sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime) as usedtime from ".$configValues['CONFIG_DB_TBL_RADACCT'].", ".$configValues['CONFIG_DB_TBL_RADCHECK']." where (".$configValues['CONFIG_DB_TBL_RADACCT'].".Username = ".$configValues['CONFIG_DB_TBL_RADCHECK'].".UserName) and (".$configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute = 'Max-All-Session' or ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute = 'Expiration') group by ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName;";
	$res = $dbSocket->query($sql);
	$numrows = $res->numRows();
	
	$sql = "select distinct(".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName) as username, ".$configValues['CONFIG_DB_TBL_RADCHECK'].".attribute as attribute, ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Value maxtimeexpiration, sum(".$configValues['CONFIG_DB_TBL_RADACCT'].".AcctSessionTime) as usedtime from ".$configValues['CONFIG_DB_TBL_RADACCT'].", ".$configValues['CONFIG_DB_TBL_RADCHECK']." where (".$configValues['CONFIG_DB_TBL_RADACCT'].".Username = ".$configValues['CONFIG_DB_TBL_RADCHECK'].".UserName) and (".$configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute = 'Max-All-Session' or ".$configValues['CONFIG_DB_TBL_RADCHECK'].".Attribute = 'Expiration') group by ".$configValues['CONFIG_DB_TBL_RADACCT'].".UserName  ORDER BY $orderBy $orderType LIMIT $offset, $rowsPerPage;";
	$res = $dbSocket->query($sql);
	$logDebugSQL = "";
	$logDebugSQL .= $sql . "\n";


	/* START - Related to pages_numbering.php */
	$maxPage = ceil($numrows/$rowsPerPage);
	/* END */

        echo "<table border='2' class='table1'>\n";
        echo "
                        <thead>
                                <tr>
                                <th colspan='7'>".$l['all']['Records']."</th>
                                </tr>

                                                        <tr>
                                                        <th colspan='12' align='left'>
                <br/>
        ";

        if ($configValues['CONFIG_IFACE_TABLES_LISTING_NUM'] == "yes")
		setupNumbering($numrows, $rowsPerPage, $pageNum, $orderBy, $orderType,"&username=$username&startdate=$startdate&enddate=$enddate");
	
        echo " </th></tr>
                                        </thead>

                        ";

        echo "<thread> <tr>
                        <th scope='col'> ".$l['all']['Username']."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=username&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=username&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l['all']['Attribute']."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=attribute&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=attribute&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l['all']['MaxTimeExpiration']."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=maxtimeexpiration&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=maxtimeexpiration&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l['all']['UsedTime']."
						<br/>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=usedtime&orderType=asc\"> > </a>
						<a class='novisit' href=\"" . $_SERVER['PHP_SELF'] . "?orderBy=usedtime&orderType=desc\"> < </a>
						</th>
                        <th scope='col'> ".$l['all']['Status']." </th>
                        <th scope='col'> ".$l['all']['Usage']." </th>
                </tr> </thread>";
	while($row = $res->fetchRow()) {
		$status="Active";

		if ($row[1] == "Expiration") {		
			if (datediff('d', $row[2], "$currdate", false) > 0) {
				$status = "Expired";
			}
		} 


		if ($row[1] == "Max-All-Session") {		
			if ($row[3] >= $row[2]) {
				$status = "End";
			}
		}

                echo "<tr>
                        <td> $row[0] </td>
                        <td> $row[1] </td>
                        <td> $row[2] </td>
                        <td> $row[3] </td>
                        <td> $status </td>
			<td> ";

		if ($row[1] == "Expiration") {		
			$difference = datediff('d', $row[2], "$currdate", false);
			if ($difference > 0)
				echo "<h100> " . " $difference days since expired" . "</h100> ";
			else 
				echo substr($difference, 1) . " days until expiration";
		} 

		if ($row[1] == "Max-All-Session") {		
			if ($status == "End") {
				echo "<h100> " . abs($row[2] - $row[3]) . " seconds overdue credit" . "</h100>";
			} else {
				echo $row[2] - $row[3];
				echo " left on credit";
			}
		} 


		echo "	</td>
                </tr>";
        }

        echo "
                                        <tfoot>
                                                        <tr>
                                                        <th colspan='12' align='left'>
        ";
	setupLinks($pageNum, $maxPage, $orderBy, $orderType,"&username=$username&startdate=$startdate&enddate=$enddate");
        echo "
                                                        </th>
                                                        </tr>
                                        </tfoot>
                ";

        echo "</table>";

        include 'library/closedb.php';
?>


<?php
	include('include/config/logging.php');
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

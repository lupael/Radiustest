<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 * Description:
 *              returns user Connection Status, Subscription Analysis, Account Status etc...
 *		(borrowed from Joachim's capture pages)
 *
 * Authors:     Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */

/*
 *********************************************************************************************************
 * userSubscriptionAnalysis($username)
 * $username            username to provide information of
 * $drawTable           if set to 1 (enabled) a toggled on/off table will be drawn
 * 
 * provides information for user's subscription (packages or session limits) such as Max-All-Session,
 * Max-Monthly-Session, Max-Daily-Session, Expiration attribute, etc...
 *********************************************************************************************************
 */
function userSubscriptionAnalysis($username, $drawTable) {

	include_once('include/management/pages_common.php');
	include 'library/opendb.php';

	$username = $dbSocket->escapeSimple($username);			// sanitize variable for sql statement

	// check subscription limitation (max-*-session, etc)
        $sql = "SELECT Value AS 'Max-All-Session', ".
		" (SELECT Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK'].
		" WHERE UserName='$username' AND Attribute='Max-Monthly-Session' LIMIT 1) AS 'Max-Monthly-Session', ".
		" (SELECT Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK'].
		" WHERE UserName='$username' AND Attribute='Max-Daily-Session' LIMIT 1) AS 'Max-Daily-Session', ".
		" (SELECT Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK'].
		" WHERE UserName='$username' AND Attribute='Access-Period' LIMIT 1) AS 'Access-Period', ".
		" (SELECT Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK'].
		" WHERE UserName='$username' AND Attribute='Expiration' LIMIT 1) AS 'Expiration', ".
		" (SELECT Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK'].
		" WHERE UserName='$username' AND Attribute='Session-Timeout' LIMIT 1) AS 'Session-Timeout', ".
		" (SELECT Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK'].
		" WHERE UserName='$username' AND Attribute='Idle-Timeout' LIMIT 1) AS 'Idle-Timeout' ".
		" FROM ".$configValues['CONFIG_DB_TBL_RADCHECK'].
		" WHERE UserName='$username' AND Attribute='Max-All-Session' LIMIT 1";

	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	(isset($row['Max-All-Session'])) ? $userLimitMaxAllSession = time2str($row['Max-All-Session']) : $userLimitMaxAllSession = "none";
	(isset($row['Max-Monthly-Session'])) ? $userLimitMaxMonthlySession = time2str($row['Max-Monthly-Session']) : $userLimitMaxMonthlySession = "none";
	(isset($row['Max-Daily-Session'])) ? $userLimitMaxDailySession = time2str($row['Max-Daily-Session']) : $userLimitMaxDailySession = "none";
	(isset($row['Access-Period'])) ? $userLimitAccessPeriod = time2str($row['Access-Period']) : $userLimitAccessPeriod = "none";
	(isset($row['Expiration'])) ? $userLimitExpiration = $row['Expiration'] : $userLimitExpiration = "none";
	(isset($row['Session-Timeout'])) ? $userLimitSessionTimeout = time2str($row['Session-Timeout']) : $userLimitSessionTimeout = "none";
	(isset($row['Idle-Timeout'])) ? $userLimitIdleTimeout = time2str($row['Idle-Timeout']) : $userLimitIdleTimeout = "none";


	$userSumMaxAllSession = "unavailable";		// initialize variables
	$userSumDownload = "unavailable";
	$userSumUpload = "unavailable";
	if (!($userLimitMaxAllSession == "none")) {

	        $sql = "SELECT SUM(AcctSessionTime) AS 'SUMSession', SUM(AcctOutputOctets) AS 'SUMDownload', SUM(AcctInputOctets) AS 'SUMUpload', ".
			" COUNT(RadAcctId) AS 'Logins' ".
			" FROM ".$configValues['CONFIG_DB_TBL_RADACCT']." WHERE UserName='$username'";
		$res = $dbSocket->query($sql);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

		(isset($row['SUMSession'])) ? $userSumMaxAllSession = time2str($row['SUMSession']) : $userSumMaxAllSession = "unavailable";
		(isset($row['SUMDownload'])) ? $userSumDownload = toxbyte($row['SUMDownload']) : $userSumDownload = "unavailable";
		(isset($row['SUMUpload'])) ? $userSumUpload = toxbyte($row['SUMUpload']) : $userSumUpload = "unavailable";
		(isset($row['Logins'])) ? $userAllLogins = $row['Logins'] : $userAllLogins = "unavailable";

	}


	$userSumMaxMonthlySession = "unavailable";		// initialize variables
	$userSumMonthlyDownload = "unavailable";
	$userSumMonthlyUpload = "unavailable";
	if (!($userLimitMaxMonthlySession == "none")) {

		$currMonth = date("Y-m-01");
		$nextMonth = date("Y-m-01", mktime(0, 0, 0, date("m")+ 1, date("d"), date("Y")));

	        $sql = "SELECT SUM(AcctSessionTime) AS 'SUMSession', SUM(AcctOutputOctets) AS 'SUMDownload', SUM(AcctInputOctets) AS 'SUMUpload', ".
			" COUNT(RadAcctId) AS 'Logins' ".
			" FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].
			" WHERE AcctStartTime<'$nextMonth' AND AcctStartTime>='$currMonth' AND UserName='$username'";
		$res = $dbSocket->query($sql);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

		(isset($row['SUMSession'])) ? $userSumMaxMonthlySession = time2str($row['SUMSession']) : $userSumMaxMonthlySession = "unavailable";
		(isset($row['SUMDownload'])) ? $userSumMonthlyDownload = toxbyte($row['SUMDownload']) : $userSumMonthlyDownload = "unavailable";
		(isset($row['SUMUpload'])) ? $userSumMonthlyUpload = toxbyte($row['SUMUpload']) : $userSumMonthlyUpload = "unavailable";
		(isset($row['Logins'])) ? $userMonthlyLogins = $row['Logins'] : $userMonthlyLogins = "unavailable";

	}


	$userSumMaxDailySession = "unavailable";		// initialize variables
	$userSumDailyDownload = "unavailable";
	$userSumDailyUpload = "unavailable";
	if (!($userLimitMaxDailySession == "none")) {

		$currDay = date("Y-m-d");
		$nextDay = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+1, date("Y")));

	        $sql = "SELECT SUM(AcctSessionTime) AS 'SUM', SUM(AcctOutputOctets) AS 'SUMDownload', SUM(AcctInputOctets) AS 'SUMUpload', ".
			" COUNT(RadAcctId) AS 'Logins' ".
			" FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].
			" WHERE AcctStartTime<'$nextDay' AND AcctStartTime>='$currDay' AND UserName='$username'";
		$res = $dbSocket->query($sql);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

		(isset($row['SUMSession'])) ? $userSumMaxDailySession = time2str($row['SUMSession']) : $userSumMaxDailySession = "unavailable";
		(isset($row['SUMDownload'])) ? $userSumDailyDownload = toxbyte($row['SUMDownload']) : $userSumDailyDownload = "unavailable";
		(isset($row['SUMUpload'])) ? $userSumDailyUpload = toxbyte($row['SUMUpload']) : $userSumDailyUpload = "unavailable";
		(isset($row['Logins'])) ? $userDailyLogins = $row['Logins'] : $userDailyLogins = "unavailable";

	}


	$userSumAccessPeriod = "unavailable";			// initliaze variables
	if (!($userLimitAccessPeriod == "none")) {

	        $sql = "SELECT SUM(AcctSessionTime) AS 'SUMSession' ".
			" FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].
			" WHERE UserName='$username'";
		$res = $dbSocket->query($sql);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

		(isset($row['SUMSession'])) ? $userSumAccessPeriod = time2str($row['SUMSession']) : $userSumAccessPeriod = "unavailable";

	}


	$userSumExpiration = "unavailable";			// initialize variables
	if (!($userLimitExpiration == "none")) {

	        $sql = "SELECT SUM(AcctSessionTime) AS 'SUM' FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].
			" WHERE UserName='$username'";
		$res = $dbSocket->query($sql);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

		(isset($row['SUM'])) ? $userSumExpiration = time2str($row['SUM']) : $userSumExpiration = "unavailable";

	}

        include 'library/closedb.php';

        if ($drawTable == 1) {

                echo "<table border='0' class='table1'>";
                echo "
        		<thead>
        			<tr>
        	                <th colspan='10' align='left'> 
        				<a class=\"table\" href=\"javascript:toggleShowDiv('divSubscriptionAnalysis')\">Subscription Analysis</a>
        	                </th>
        	                </tr>
        		</thead>
        		</table>
        	";
        
                echo "
        		<div id='divSubscriptionAnalysis' style='display:none;visibility:visible'>
        		<table border='0' class='table1'>
        		<thread> <tr>
        
                        <th scope='col'>
                        </th>
        
                        <th scope='col'>
        		Global (Max-All-Session)
                        </th>
        
                        <th scope='col'>
        		Monthly (Max-Monthly-Session)
                        </th>
        
                        <th scope='col'>
        		Daily (Max-Daily-Session)
                        </th>
        
                        </tr> </thread>";
        
        	echo "
        		<tr>
        			<td>Session Limit</td>
        			<td>$userLimitMaxAllSession</td>
        			<td>$userLimitMaxMonthlySession</td>
        			<td>$userLimitMaxDailySession</td>
        		</tr>
        
        		<tr>
        			<td>Session Used</td>
        			<td>$userSumMaxMonthlySession</td>
        			<td>$userSumMaxMonthlySession</td>
        			<td>$userSumMaxDailySession</td>
        		</tr>
        
        		<tr>
        			<td>Session Download</td>
        			<td>$userSumDownload</td>
        			<td>$userSumMonthlyDownload</td>
        			<td>$userSumDailyDownload</td>
        		</tr>
        
        		<tr>
        			<td>Session Upload</td>
        			<td>$userSumUpload</td>
        			<td>$userSumMonthlyUpload</td>
        			<td>$userSumDailyUpload</td>
        		</tr>
        
        		<tr>
        			<td>Session Traffic (Up+Down)</td>
        			<td> ".($userSumDownload+$userSumUpload)."</td>
        			<td> ".($userSumMonthlyDownload+$userSumMonthlyUpload)."</td>
        			<td> ".($userSumDailyDownload+$userSumDailyUpload)."</td>
        		</tr>

        		<tr>
        			<td>Logins</td>
        			<td>$userAllLogins</td>
        			<td>$userMonthlyLogins</td>
        			<td>$userDailyLogins</td>
        		</tr>
        
        		</table>

        		<table border='0' class='table1'>
        		<thread>

                        <tr>        
                        <th scope='col' align='right'>
                        Access-Period
                        </th> 
        
                        <th scope='col' align='left'>
                        $userLimitAccessPeriod
                        </th>
                        </tr>

                        <tr>
                        <th scope='col' align='right'>
                        Expiration
                        </th> 
        
                        <th scope='col' align='left'>
                        $userLimitExpiration
                        </th>
                        </tr>


                        <tr>
                        <th scope='col' align='right'>
                        Session-Timeout
                        </th> 
        
                        <th scope='col' align='left'>
                        $userLimitSessionTimeout
                        </th>
                        </tr>


                        <tr>
                        <th scope='col' align='right'>
                        Idle-Timeout
                        </th> 
        
                        <th scope='col' align='left'>
                        $userLimitIdleTimeout
                        </th>
                        </tr>

                        </table>

        		</div>
        	";

	}		

}



/*
 *********************************************************************************************************
 * userConnectionStatus($username
 * $username            username to provide information of
 * $drawTable           if set to 1 (enabled) a toggled on/off table will be drawn
 * 
 * returns user connection information: uploads, download, last connectioned, total online time,
 * whether user is now connected or not.
 *
 *********************************************************************************************************
 */
function userConnectionStatus($username, $drawTable) {

	$userStatus = checkUserOnline($username);

	include_once('include/management/pages_common.php');
	include 'library/opendb.php';

	$username = $dbSocket->escapeSimple($username);			// sanitize variable for sql statement

        $sql = "SELECT AcctStartTime,AcctSessionTime,NASIPAddress,CalledStationId,FramedIPAddress,CallingStationId".
		",AcctInputOctets,AcctOutputOctets FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].
		" WHERE Username='$username' ORDER BY RadAcctId DESC LIMIT 1";
	$res = $dbSocket->query($sql);
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

	$userUpload = toxbyte($row['AcctInputOctets']);
	$userDownload = toxbyte($row['AcctOutputOctets']);
	$userLastConnected = $row['AcctStartTime'];
	$userOnlineTime = time2str($row['AcctSessionTime']);

        $nasIPAddress = $row['NASIPAddress'];
        $nasMacAddress = $row['CalledStationId'];
        $userIPAddress = $row['FramedIPAddress'];
        $userMacAddress = $row['CallingStationId'];

        include 'library/closedb.php';

        if ($drawTable == 1) {

                echo "<table border='0' class='table1'>";
                echo "
        		<thead>
        			<tr>
        	                <th colspan='10' align='left'> 
        				<a class=\"table\" href=\"javascript:toggleShowDiv('divConnectionStatus')\">Session Info</a>
        	                </th>
        	                </tr>
        		</thead>
        		</table>
        	";
        
                echo "
                        <div id='divConnectionStatus' style='display:none;visibility:visible'>
               		<table border='0' class='table1'>
        		<thread>

                        <tr>        
                        <th scope='col' align='right'>
                        User Status
                        </th> 
        
                        <th scope='col' align='left'>
                        $userStatus
                        </th>
                        </tr>

                        <tr>
                        <th scope='col' align='right'>
                        Last Connection                        
                        </th> 
        
                        <th scope='col' align='left'>
                        $userLastConnected
                        </th>
                        </tr>

                        <tr>
                        <th scope='col' align='right'>
                        Online Time
                        </th> 
        
                        <th scope='col' align='left'>
                        $userOnlineTime
                        </th>
                        </tr>

                        <tr>
                        <th scope='col' align='right'>
                        Server (NAS)
                        </th> 
        
                        <th scope='col' align='left'>
                        $nasIPAddress (MAC: $nasMacAddress)
                        </th>
                        </tr>

                        <tr>
                        <th scope='col' align='right'>
                        User Workstation
                        </th> 
        
                        <th scope='col' align='left'>
                        $userIPAddress (MAC: $userMacAddress)
                        </th>
                        </tr>

                        <tr>
                        <th scope='col' align='right'>
                        User Upload
                        </th> 
        
                        <th scope='col' align='left'>
                        $userUpload
                        </th>
                        </tr>


                        <tr>
                        <th scope='col' align='right'>
                        User Download
                        </th> 
        
                        <th scope='col' align='left'>
                        $userDownload
                        </th>
                        </tr>

                        </table>

        		</div>
        	";

	}		


}


/*
 *********************************************************************************************************
 * checkUserOnline
 * returns string variable "User is online" or "User is offline" based on radacct check for AcctStopTime
 * not set or set to 0000-00-00 00:00:00
 *
 *********************************************************************************************************
 */
function checkUserOnline($username) {

	include 'library/opendb.php';

	$username = $dbSocket->escapeSimple($username);

        $sql = "SELECT Username FROM ".$configValues['CONFIG_DB_TBL_RADACCT'].
		" WHERE AcctStopTime IS NULL AND Username='$username'";
	$res = $dbSocket->query($sql);
	if ($numrows = $res->numRows() >= 1) {
		$userStatus = "User is online";
	} else {
		$userStatus = "User is offline";
	}	

	include 'library/closedb.php';

	return $userStatus;

}



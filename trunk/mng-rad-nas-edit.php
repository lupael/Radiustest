<?php

    include ("library/checklogin.php");
    $operator = $_SESSION['operator_user'];


	
        include 'library/opendb.php';

	$nashost = "";
        $nassecret = "";
        $nasname = "";
        $nasports = "";
        $nastype = "";
        $nasdescription = "";
        $nascommunity = "";

	$nashost = $_REQUEST['nashost'];

        // fill-in nashost details in html textboxes
        $sql = "SELECT * FROM nas WHERE nasname='$nashost'";
        $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
        $row = mysql_fetch_array($res);		// array fetched with values from $sql query

						// assignment of values from query to local variables
						// to be later used in html to display on textboxes (input)
        $nassecret = $row['secret'];
        $nasname = $row['shortname'];
        $nasports = $row['ports'];
        $nastype = $row['type'];
        $nascommunity = $row['community'];
        $nasdescription = $row['description'];

        if (isset($_POST['submit'])) {
	        $nashost = $_POST['nashost'];
	        $nassecret = $_POST['nassecret'];;
	        $nasname = $_POST['nasname'];;
	        $nasports = $_POST['nasports'];;
	        $nastype = $_POST['nastype'];;
	        $nasdescription = $_POST['nasdescription'];;
	        $nascommunity = $_POST['nascommunity'];;

                
                include 'library/opendb.php';

                $sql = "SELECT * FROM nas WHERE nasname='$nashost'";
                $res = mysql_query($sql) or die('Query failed: ' . mysql_error());

                if (mysql_num_rows($res) == 1) {

                        if (trim($nashost) != "" and trim($nassecret) != "") {

				if (!$nasports) {
					$nasports = 0;
				}

                                // insert nas details
                                $sql = "UPDATE nas SET shortname='$nasname', type='$nastype', ports=$nasports, secret='$nassecret', community='$nascommunity', description='$nasdescription' WHERE nasname='$nashost'";
                                $res = mysql_query($sql) or die('Query failed: ' . mysql_error());
                        
			echo "<font color='#0000FF'>success<br/></font>";

			}

                } elseif (mysql_num_rows($res) > 1) {
                        echo "<font color='#FF0000'>error: NAS IP/Host [$nashost] already exist <br/></font>";
						echo "
                                <script language='JavaScript'>
                                <!--
                                alert('The NAS IP/Host $nashost already exists in the database.\\nPlease check that there are no duplicate entries in the database.');
                                -->
                                </script>
                                ";
                } else {
                        echo "<font color='#FF0000'>error: NAS IP/Host [$nashost] doesn't exist <br/></font>";
						echo "
                                <script language='JavaScript'>
                                <!--
                                alert('The NAS IP/Host $nashost doesn't exist at all in the database.\\nPlease re-check the username.');
                                -->
                                </script>
                                ";
				}

                include 'library/closedb.php';
        }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>

<SCRIPT TYPE="text/javascript">
<!--
function toggleShowDiv(pass) {

        var divs = document.getElementsByTagName('div');
        for(i=0;i<divs.length;i++) {
                if (divs[i].id.match(pass)) {
                        if (document.getElementById) {                                         
                                if (divs[i].style.display=="inline")
                                        divs[i].style.display="none";
                                else
                                        divs[i].style.display="inline";
                        } else if (document.layers) {                                           
                                if (document.layers[divs[i]].display=='visible')
                                        document.layers[divs[i]].display = 'hidden';
                                else
                                        document.layers[divs[i]].display = 'visible';
                        } else {
                                if (document.all.hideShow.divs[i].visibility=='visible')     
                                        document.all.hideShow.divs[i].visibility = 'hidden';
                                else
                                        document.all.hideShow.divs[i].visibility = 'visible';
                        }
                }
        }
}



// -->
</script>


<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
 
<?php
	include ("menu-mng-rad-nas.php");
?>
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#">New NAS Record</a></h2>
				
				<p>

                                <form name="newnas" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table border='2' class='table1'>
<tr><td>
                                                <input type="hidden" value="<?php echo $nashost ?>" name="nashost" /><br/>

                                                <?php if (trim($nassecret) == "") { echo "<font color='#FF0000'>";  }?>
	                                        <b>NAS Secret</b>
</td><td>											
                                                <input value="<?php echo $nassecret ?>" name="nassecret" /> 
                                                </font><br/>
</td></tr>
<tr><td>
                                                <?php if (trim($nasname) == "") { echo "<font color='#FF0000'>";  }?>
                                                <b>NAS Shortname</b> 
</td><td>												
                                                <input value="<?php echo $nasname ?>" name="nasname" /> (descriptive name)
                                                </font><br/>
</td></tr>
</table>

        <br/>
		<center>
        <h4> Advnaced NAS Attributes </h4>
		</center>

<table border='2' class='table1'>
<tr><td>
                                                <?php if (trim($nastype) == "") { echo "<font color='#FF0000'>";  }?>
			<input type="checkbox" onclick="javascript:toggleShowDiv('attributesNasType')">
                                                <b>NAS Type</b>
</td><td>												
<div id="attributesNasType" style="display:none;visibility:visible" >
						<br/>
                                                <input value="<?php echo $nastype ?>" name="nastype" />
                                                </font>
</div><br/>
</td></tr>
<tr><td>




                                                <?php if (trim($nasports) == "") { echo "<font color='#FF0000'>";  }?>
			<input type="checkbox" onclick="javascript:toggleShowDiv('attributesPorts')">
                                                <b>NAS Ports</b> 
</td><td>												
<div id="attributesPorts" style="display:none;visibility:visible" >
						<br/>
                                                <input value="<?php echo $nasports ?>" name="nasports" />
                                                </font>
</div><br/>
</td></tr>
<tr><td>



                                                <?php if (trim($nascommunity) == "") { echo "<font color='#FF0000'>";  }?>
			<input type="checkbox" onclick="javascript:toggleShowDiv('attributesCommunity')">
                                                <b>NAS Community</b> 
</td><td>												
<div id="attributesCommunity" style="display:none;visibility:visible" >
						<br/>
                                                <input value="<?php echo $nascommunity ?>" name="nascommunity" />
                                                </font>
</div><br/>
</td></tr>
<tr><td>




                                                <?php if (trim($nasdescription) == "") { echo "<font color='#FF0000'>";  }?>
			<input type="checkbox" onclick="javascript:toggleShowDiv('attributesDescription')">
                                                <b>NAS Description</b> 
</td><td>												
<div id="attributesDescription" style="display:none;visibility:visible" >
						<br/>
                                                <input value="<?php echo $nasdescription ?>" name="nasdescription" />
                                                </font>
</div>
</td></tr>
</table>

												<br/><br/>
<center>												
                                                <input type="submit" name="submit" value="Apply"/>
</center>
                                </form>



				</p>
				
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

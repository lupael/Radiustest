<?php

	include_once('library/config_read.php');
    $log = "visited page: ";
    $logQuery = "performed query on page: ";
    include('include/config/logging.php');


?>

<?php

    include ("menu-home.php");
  	
?>	
		
		
		<div id="contentnorightbar">
		
		<h2 id="Intro"><a href="#"><? echo $l[Intro][indexradiuslog.php]; ?></a></h2>
				<p>

<?php
	include 'library/exten-radius_log.php';
?>
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

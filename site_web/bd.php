<?php
	function getBD(){
		#Changer les données pour la mise en ligne sur InfinityFree
		#$host = 'sql309.infinityfree.com';
        #$dbname = 'if0_41581051_addiction';
        #$user = 'if0_41581051';
        #$pass = 'od1RVxvKLqA3FD';
        
		$host = 'localhost';
        $dbname = 'addiction';
        $user = 'root';
        $pass = 'root';

        $bdd = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
		return $bdd;
	}
?>
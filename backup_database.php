<?php
/*
Backups a MySQL database via web in PHP
Originally by David Walsh https://davidwalsh.name/backup-mysql-database-php
Adapted and converted to PHP5
by Antonio Bonifati http://ninuzzo.github.io
*/

if (isset($_POST['name'])) {
	/* backup the db OR just a table */
	function backup_tables($host,$user,$pass,$name,$tables = '*')
	{
		
		$link = mysqli_connect($host,$user,$pass);
		mysqli_select_db($link,$name);
		
		//get all of the tables
		if($tables == '*')
		{
			$tables = array();
			$result = mysqli_query($link, 'SHOW TABLES');
			while($row = mysqli_fetch_row($result))
			{
				$tables[] = $row[0];
			}
		}
		else
		{
			$tables = is_array($tables) ? $tables : explode(',',$tables);
		}
		
		//cycle through
                $return = '';
		foreach($tables as $table)
		{
			$result = mysqli_query($link, 'SELECT * FROM '.$table);
			$num_fields = mysqli_field_count($link);
			
			$return.= 'DROP TABLE '.$table.';';
			$row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE '.$table));
			$return.= "\n\n".$row2[1].";\n\n";
			
			for ($i = 0; $i < $num_fields; $i++) 
			{
				while($row = mysqli_fetch_row($result))
				{
					$return.= 'INSERT INTO '.$table.' VALUES(';
					for($j=0; $j < $num_fields; $j++) 
					{
						$row[$j] = addslashes($row[$j]);
						$row[$j] = preg_replace("/\n/","\\n",$row[$j]);
						if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
						if ($j < ($num_fields-1)) { $return.= ','; }
					}
					$return.= ");\n";
				}
			}
			$return.="\n\n\n";
		}
		
		//save file to the browser
		header("Content-Type: text/plain");
		header("Content-Disposition: attachment; filename=\"$name.sqldump.txt\"");
		header("Content-Length: " . strlen($return));
		header("Content-Transfer-Encoding: binary");
		echo $return;
	}

	backup_tables($_POST['host'], $_POST['user'], $_POST['pass'], $_POST['name']);
} else {
?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Backup database</title>
</head>
<body>
<h1>Backup database</h1>
<form method="post">
  <label>host: <input type="text" name="host" value="localhost"></label><br>
  <label>user: <input type="text" name="user"</label><br>
  <label>pass: <input type="password" name="pass"</label><br>
  <label>db: <input type="text" name="name"></label><br>
  <input type="submit" value="dump">
</form>
</body>
</html>
<?php
  }
?>

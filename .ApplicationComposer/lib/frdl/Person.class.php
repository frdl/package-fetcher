<?php 
die('Just example file .ApplicationComposer\lib\frdl\Person.class.php, script aborted.');
	require_once("easyCRUD.class.php");
	class Person  Extends Crud {
		
			# Your Table name 
			protected $table = 'persons';
			
			# Primary Key of the Table
			protected $pk	 = 'id';
	}
?>
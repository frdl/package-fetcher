<?php 
namespace frdl;
/**
* Easy Crud  -  This class kinda works like ORM. Just created for fun :) 
*
* @author		Author: Vivek Wicky Aswal. (https://twitter.com/#!/VivekWickyAswal)
* @version      0.1a
* 
* edited by webfan
*/

abstract class Crud {
	
	protected $s = null;  // DatabaseSchema -> DBSchema
	protected $schema_tables = null;
	
	protected $pfx; //TABLES PREFIX
	
    # Your Table name INHERIT!
	protected $table = '';
	protected $table_fields = null;
	
			
    # Primary Key of the Table INHERIT!
	protected $pk	 = '';
				
	protected $version = 0;
	
	protected $db;
	public $variables;
	
	
/**
* 
* @param undefined $args
* 
* @return  example:
*  	 return array(
* 				  'version' => self::VERSION,
* 				  'schema' => "(
* 				      `vendor` varchar(256) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' ,
* 				      `package` varchar(256) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
* 				      `time_last_fetch_info` INT(11) NOT NULL DEFAULT '0',
* 				      PRIMARY KEY (`vendor`, `package`)
* 				     )ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ",
* 				);
*/
	abstract public function shema();	
	
	/**
	* 
	* @param string $label | null
	* 
	* @return string $field | array $fields
	* 
	* @example  	   
	*     public function field($label = null){
	*			$l = array(
	*			 '_use' => 'Use this repository',
	*			 'name' => 'Name',
	*			 'host' => 'Host',
	*			 'homepage' => 'Homepage',
	*			 'description' => 'Description', 
	*			 'fetcher_class' => 'Fetcher Class', 
	*			);
	*			if(null === $label){
	*				return $l;
	*			}
	*			
	*			return (isset($l[$label])) ? $l[$label] : null;
	*		}
	*/
	abstract public function field($label = null);

	
	
/**
* 
* @param string $field | null
* 
* @return string $label | array $labels 
*/
   public function label($field  = null){
				$f = array_flip($this->field(null));
				return (isset($f[$field])) ? $f[$field] : null;
   }
   
   
    public function install(){
	        	$s = $this->shema();
				$this->db->query(" 
				     CREATE TABLE IF NOT EXISTS `".$this->table."` ".$s['schema']." ;
				");
				
		return $this;		
	}	
	
	
	
	
	
	final public function getTableName(){
		return $this->table;
	}
	

	public function __construct($data = array(), $settings = null, &$db = null) {
		$this->s = \frdl\ApplicationComposer\DBSchema::_($settings, $db);
		$this->schema_tables = $this->s->get_schema_tables(false);
		$this->db = (null === $db) ? \frdl\xGlobal\webfan::db() : $db;	
		if(is_array($settings))$this->db->settings($settings);
		$settings = $this->db->settings();
		$this->pfx = (isset($settings['pfx']) && is_string($settings['pfx'])) ? $settings['pfx'] : '';
		$this->table = $this->pfx . $this->table;
		$this->table_fields = &$this->schema_tables[$settings["dbname"]][$this->table];
		foreach($data as $k => $v){
			 $this->__set($k,$v);
		}

	}
	
   
   public function __invoke()
     {
      $args = func_get_args();
      if(2 === count($args) && is_string($args[0]) && is_array($args[1])){
	  	 return call_user_func_array(array($this->db, 'query'), $args);
	  }elseif(1 === count($args) && is_array($args[0])){
	  	 if(!is_array($this->variables))$this->variables = array();
	  	 $this->variables = array_merge($this->variables, $args[0]);
	  	 return $this;
	  }
	  
	  
	  trigger_error('Invoking '.get_class($this).' wrong parameter count error.', E_USER_WARNING);
	  return $this;
    }

	
	
	public function __set($name,$value){
		if(strtolower($name) === $this->pk) {
			$this->variables[$this->pk] = $value;
		}
		else {
			$this->variables[$name] = $value;
		}
	  return $this;	
	}
	public function __get($name)
	{	
		if(is_array($this->variables)) {
			if(array_key_exists($name,$this->variables)) {
				return $this->variables[$name];
			}
		}
		$trace = debug_backtrace();
		trigger_error(
		'Undefined property via __get(): ' . $name .
		' in ' . $trace[0]['file'] .
		' on line ' . $trace[0]['line'],
		E_USER_NOTICE);
		return null;
	}
	
	
   public function __call($name, $args){
   	   if('update' === $name){
  	    	 foreach($args[0] as $column => $value){
			   $this->variables[$column] = $value;
		     } 	   	
   	     	 return call_user_func_array(array($this, 'save'),$args);
   	   	 }
   	   if('read' === $name)return call_user_func_array(array($this, 'select'),$args);
   	   if('db' === $name && 0 === count($args)){
   	   	   return $this->db;
   	   	 }elseif('db' === $name && 2 === count($args) && is_string($args[0]) && is_array($args[1])){
   	   	    return call_user_func_array(array($this->db, 'query'), $args);
	   }elseif('db' === $name){
	   	      $method = array_shift($args);
	   	      return call_user_func_array(array($this->db, $method), $args);
	   }
   	   
   	   	$trace = debug_backtrace();
		trigger_error(
		'Undefined method via __call(): ' . $name .
		' in ' . $trace[0]['file'] .
		' on line ' . $trace[0]['line'],
		E_USER_WARNING);
		
		return $this;
   }



	public function save($id = "0") {
		$this->variables[$this->pk] = (empty($this->variables[$this->pk])) ? $id : $this->variables[$this->pk];
		$fieldsvals = '';
		$columns = array_keys($this->variables);
		foreach($columns as $column)
		{
			if($column !== $this->pk)
			$fieldsvals .= $column . " = :". $column . ",";
		}
		if(count($columns) > 1)$fieldsvals = substr_replace($fieldsvals , '', -1);
		if(count($columns) > 1 ) {
			$sql = "UPDATE " . $this->table .  " SET " . $fieldsvals . " WHERE " . $this->pk . "= :" . $this->pk;
			return $this->db->query($sql,$this->variables);
		}
	}
	public function create() { 
		$bindings   	= $this->variables;
		if(!empty($bindings)) {
			$fields     =  array_keys($bindings);
			$fieldsvals =  array(implode(",",$fields),":" . implode(",:",$fields));
			$sql 		= "INSERT INTO ".$this->table." (".$fieldsvals[0].") VALUES (".$fieldsvals[1].")";
		}
		else {
			$sql 		= "INSERT INTO ".$this->table." () VALUES ()";
		}
		return $this->db->query($sql,$bindings);
	}
	public function delete($id = "") {
		$id = (empty($this->variables[$this->pk])) ? $id : $this->variables[$this->pk];
		if(!empty($id)) {
			$sql = "DELETE FROM " . $this->table . " WHERE " . $this->pk . "= :" . $this->pk. " LIMIT 1" ;
			return $this->db->query($sql,array($this->pk=>$id));
		}
		return false;
	}
	
	public function find($id = "") {
		$id = (empty($this->variables[$this->pk])) ? $id : $this->variables[$this->pk];
		if(!empty($id)) {
			$sql = "SELECT * FROM " . $this->table ." WHERE " . $this->pk . "= :" . $this->pk . " LIMIT 1";	
			$this->variables = $this->db->row($sql,array($this->pk=>$id));
			return true;
		}
		return false;
	}
	
	
	public function search($_fields = array(), $from = 0, $count = 25, $orderBy = array(), $groupBy = array()) {
		$columns = array_keys($_fields);
		$fieldsvals = ' ';
		foreach($columns as $column)
		{
			if($column !== $this->pk)
			$fieldsvals .= $column . " LIKE  CONCAT(:".$column.", '%') AND ";
		}
		$fieldsvals = substr_replace($fieldsvals , '', -4);
		
       //$columns = array_keys($groupBy);
		$columns = $groupBy;
		$fieldsvals .= (0 === count($groupBy) ) ? ' '
		                 : ' GROUP BY ';		
		foreach($columns as $column => $order)
		{
			$fieldsvals .= " ". $column . ",";
		}		
  	    if(count($columns) > 0)$fieldsvals = substr_replace($fieldsvals , '', -1);


		
		
		
	//	$columns = array_keys($orderBy);
	    $columns = $orderBy;
		$fieldsvals .= (0 === count($orderBy) ) ? ' '
		                 : ' ORDER BY ';		
		foreach($columns as $column => $order)
		{
			$fieldsvals .= " ". $column . "  ". $order . ",";
		}		
        if(count($columns) > 0)$fieldsvals = substr_replace($fieldsvals , '', -1);



		
		if(count($columns) > 1 ) {
			$sql = "SELECT * FROM " . $this->table ." WHERE " . $fieldsvals . " LIMIT ".intval($from).",".intval($count);	
			return $this->db->query($sql,array_merge($_fields, $orderBy, $groupBy));
		}	
		return null;
	}
		

	public function select($from = 0, $count = 25, $orderBy = array(), $groupBy = array()){
		$fieldsvals = ' ';
		
		//$columns = array_keys($groupBy);
		$columns = $groupBy;
		$fieldsvals .= (0 === count($groupBy) ) ? ' '
		                 : ' GROUP BY ';		
		foreach($columns as $column => $order)
		{
			$fieldsvals .= " ". $column . ",";
		}			                 
		if(count($columns) > 0)$fieldsvals = substr_replace($fieldsvals , '', -1);
		
		

		//$columns = array_keys($orderBy);
		$columns = $orderBy;
		$fieldsvals .= (0 === count($orderBy) ) ? ' '
		                 : ' ORDER BY ';		
		foreach($columns as $column => $order)
		{
			$fieldsvals .= " ". $column . "  ". $order . ",";
		}		
        if(count($columns) > 0)$fieldsvals = substr_replace($fieldsvals , '', -1);


		
		return $this->db->query("SELECT * FROM " . $this->table. " " . $fieldsvals ." LIMIT ".intval($from).",".intval($count), array_merge( $orderBy, $groupBy) );
	}
			
	public function all($orderBy = array(), $groupBy = array()){
		return $this->db->query("SELECT * FROM " . $this->table, array_merge( $orderBy, $groupBy));
	}
	
	public function min($field)  {
		if($field)
		return $this->db->single("SELECT min(" . $field . ")" . " FROM " . $this->table);
	}
	public function max($field)  {
		if($field)
		return $this->db->single("SELECT max(" . $field . ")" . " FROM " . $this->table);
	}
	public function avg($field)  {
		if($field)
		return $this->db->single("SELECT avg(" . $field . ")" . " FROM " . $this->table);
	}
	public function sum($field)  {
		if($field)
		return $this->db->single("SELECT sum(" . $field . ")" . " FROM " . $this->table);
	}
	public function count($field)  {
		if($field)
		return $this->db->single("SELECT count(" . $field . ")" . " FROM " . $this->table);
	}	
	
}
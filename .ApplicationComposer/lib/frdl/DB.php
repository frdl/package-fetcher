<?php
namespace frdl;
use PDO;


/**
 *  DB - A simple database class 
 *
 * @author		Author: Vivek Wicky Aswal. (https://twitter.com/#!/VivekWickyAswal)
 * @git 		https://github.com/indieteq/PHP-MySQL-PDO-Database-Class
 * @version      0.2ab
 *
 * edited by webfan
 */
class DB
{
	# @object, The PDO object
	protected $pdo = null;
	# @object, PDO statement object
	protected $sQuery;
	# @array,  The database settings
	protected $settings = array();
	# @bool ,  Connected to the database
	protected $bConnected = false;
	# @object, Object for logging exceptions	
	protected $log;
	# @array, The parameters of the SQL query
	protected $parameters;
		
	protected static $connections = array();	
	protected static $_ = null;		
	
	
    protected $transactionCount = 0;
	
       /**
	*   Default Constructor 
	*
	*	1. Instantiate Log class.
	*	2. Connect to database.
	*	3. Creates the parameter array.
	*/
		public function __construct($settings = array(), $connect = true)
		{ 			
		    $this->settings = array();
			$this->log = new Log();	
			$this->settings($settings);
			if(true === $connect)$this->Connect($settings);
			$this->parameters = array();
		}
		
		
   public function __invoke()
     {
      $args = func_get_args();
      if(2 === count($args) && is_string($args[0]) && is_array($args[1])){
	  	 return call_user_func_array(array($this, 'query'), $args);
	  }
	  
	  trigger_error('Invoking '.get_class($this).' wrong parameter count error.', E_USER_WARNING);
    }
   
   
	/**
	* Transaction methods
	* 
	* @return
	*/
   public function begin()
    {
        if (!$this->transactionCounter++) {
            return  $this->pdo->beginTransaction();
        }
        $this->pdo->exec('SAVEPOINT trans'.$this->transactionCounter);
        return $this->transactionCounter >= 0;
    }	
	
    public function commit()
    {
        if (!--$this->transactionCounter) {
            return $this->pdo->commit();
        }
        return $this->transactionCounter >= 0;
    }	
	
    public function rollback()
    {
        if (--$this->transactionCounter) {
            $this->pdo->exec('ROLLBACK TO trans'.$this->transactionCounter + 1);
            return true;
        }
        return $this->pdo->rollback();
    }	
	
	   protected function _singletone($settings = array(), $connect = true){
	   	   if(null === self::$_){
		   	 self::$_ = new self($settings, $connect);
		   }
		   self::$_->settings($settings);
		   if(true === $connect) self::$_->Connect($settings, true);
		   return  self::$_;
	   }
	
	  public static function __callStatic($name, $args){
	  	  self::$_ = call_user_func_array(array(new self,'_singletone'), $args);
	  	  return call_user_func_array(array(self::$_,'_singletone'), $args);
	  }
	  
     public function __call($name, $args){
     	if('query' === strtolower($name)){
     		$trace = debug_backtrace();
		          	trigger_error(
		            'Rejecting unwrapped query from '.__CLASS__.'->query ' . $name .
		           ' in ' . $trace[0]['file'] .
		           ' on line ' . $trace[0]['line'],
		           E_USER_ERROR);
		
		       return $this;			
		}
     	
   	   try{
   	   	      return call_user_func_array(array($this->pdo, $name), $args);
   	   	    }catch(\Exception $e){
   	           	$trace = debug_backtrace();
	          	trigger_error(
		            'Error calling undefined method via '.__CLASS__.'__call(): ' . $name .
		           ' in ' . $trace[0]['file'] .
		           ' on line ' . $trace[0]['line'],
		           E_USER_ERROR);
		
		       return $this;				
			}
   	   

     }
   	
	   public function settings(Array $settings = null){
	   	  if(is_array($settings))$this->settings = array_merge($this->settings, $settings);
	   	  $p = $this->settings;
	  	  unset($p['password']);
	   	  return $p;	   	  
	   }
	
	
	   public function __get($name){
	   	   if('connected' === $name)return $this->bConnected;
	       if('pdo' === strtolower($name))return $this->pdo;
	   }
	
	  
	  public function dns(){
	    	return $this->settings["driver"].':dbname='.$this->settings["dbname"].';host='.$this->settings["host"].'';
	  }
	
	
	
	  public function k(){
	  	 return sha1($this->dns().';user='.$this->settings["user"].';pass='.$this->settings["password"].';salt='.__CLASS__);
	  } 
     /**
	*	This method makes connection to the database.
	*	
	*	1. Reads the database settings from a ini file. 
	*	2. Puts  the ini content into the settings array.
	*	3. Tries to connect to the database.
	*	4. If connection failed, exception is displayed and a log file gets created.
	*/
		public function Connect($settings = array(), $retry = false)
		{
			/*$this->settings = parse_ini_file("settings.ini.php");*/
			$this->settings($settings);
			try 
			{
              
                if(isset(self::$connections[$this->k()]) && (true === self::$connections[$this->k()]['bConnected'] && true !== $retry)){
                    $this->pdo = self::$connections[$this->k()]['pdo'];
                    $this->bConnected = self::$connections[$this->k()]['bConnected'];
                	return self::$connections[$this->k()];
                }
               
                
				$this->pdo = new PDO($this->dns(), $this->settings["user"], $this->settings["password"], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
				
				# We can now log any exceptions on Fatal error. 
				$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
				# Disable emulation of prepared statements, use REAL prepared statements instead.
				$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
				
				# Connection succeeded, set the boolean to true.
				$this->bConnected = true;

			}
			catch (\PDOException $e) 
			{
				# Write into log
				$this->ExceptionLog($e->getMessage());
				$this->bConnected = false;
    		}
			
				self::$connections[$this->k()] =array(
				    'dn' => $this->dns(),
				    'pdo' => &$this->pdo,
				    'settings' => & $this->settings,
				    'bConnected' => $this->bConnected,
				);			
			
			
			return self::$connections[$this->k()];
		}
	/*
	 *   You can use this little method if you want to close the PDO connection
	 *
	 */
	 	public function CloseConnection()
	 	{
	 		# Set the PDO object to null to close the connection
	 		# http://www.php.net/manual/en/pdo.connections.php
	 		$this->pdo = null;
	 	}
		
       /**
	*	Every method which needs to execute a SQL query uses this method.
	*	
	*	1. If not connected, connect to the database.
	*	2. Prepare Query.
	*	3. Parameterize Query.
	*	4. Execute Query.	
	*	5. On exception : Write Exception into the log + SQL query.
	*	6. Reset the Parameters.
	*/	
		protected function Init($query,$parameters = "")
		{
		# Connect to database
		/* if(!$this->bConnected) { $this->Connect($this->settings); } */
		  
		try {
			
			if(!$this->bConnected)$this->Connect($this->settings, false);
				# Prepare query
				$this->sQuery = $this->pdo->prepare($query);
				
				# Add parameters to the parameter array	
				$this->bindMore($parameters);
				# Bind parameters
				#if(!empty($this->parameters)) {
					foreach($this->parameters as &$param)
					{
					  /*	$parameters = explode("\x7F",$param);  */
						$this->sQuery->bindParam($param[0],$param[1]);
					}		
				#}
				# Execute SQL 
				$this->succes 	= $this->sQuery->execute();		
			}
			catch(\PDOException $e)
			{
				    $this->succes = false;
					# Write into log and display Exception
					/*  echo $this->ExceptionLog($e->getMessage(), $query );   */
					$this->ExceptionLog($e->getMessage(), $query ); 
			}
			# Reset the parameters
			$this->parameters = array();
		}
		
       /**
	*	@void 
	*
	*	Add the parameter to the parameter array
	*	@param string $para  
	*	@param string $value 
	*/	
		public function bind($para, $value)
		{	
			/*   $this->parameters[sizeof($this->parameters)] = ":" . $para . "\x7F" . utf8_encode($value);  */
			 $this->parameters[sizeof($this->parameters)] = array(":" . $para , utf8_encode($value) );
		}
       /**
	*	@void
	*	
	*	Add more parameters to the parameter array
	*	@param array $parray
	*/	
		public function bindMore($parray)
		{
			$this->parameters = array();
			if(/*  empty($this->parameters) &&  */ is_array($parray)) {
				$columns = array_keys($parray);
				foreach($columns as $i => &$column)	{
					$this->bind($column, $parray[$column]);
				}
			}
		}
       /**
	*   	If the SQL query  contains a SELECT or SHOW statement it returns an array containing all of the result set row
	*	If the SQL statement is a DELETE, INSERT, or UPDATE statement it returns the number of affected rows
	*
	*   	@param  string $query
	*	@param  array  $params
	*	@param  int    $fetchmode
	*	@return mixed
	*/			
		public function query($query,$params = null, $fetchmode = PDO::FETCH_ASSOC)
		{
			$query = trim($query);
			$this->Init($query,$params);
			$rawStatement = explode(" ", trim($query));
			
			# Which SQL statement is used 
			$statement = strtolower($rawStatement[0]);
			
			if ($statement === 'select' || $statement === 'show' || $statement === 'call' || $statement === 'describe') {
				return $this->sQuery->fetchAll($fetchmode);
			}
			elseif ( $statement === 'insert' ||  $statement === 'update' || $statement === 'delete' ) {
				return $this->sQuery->rowCount();	
			}	
			else {
				return $this->succes;
			}
		}
		
      /**
       *  Returns the last inserted id.
       *  @return string
       */	
		public function lastInsertId() {
			return $this->pdo->lastInsertId();
		}	
		
       /**
	*	Returns an array which represents a column from the result set 
	*
	*	@param  string $query
	*	@param  array  $params
	*	@return array
	*/	
		public function column($query,$params = null)
		{
			$this->Init($query,$params);
			$Columns = $this->sQuery->fetchAll(PDO::FETCH_NUM);		
			
			$column = null;
			foreach($Columns as $cells) {
				$column[] = $cells[0];
			}
			return $column;
			
		}	
       /**
	*	Returns an array which represents a row from the result set 
	*
	*	@param  string $query
	*	@param  array  $params
	*   	@param  int    $fetchmode
	*	@return array
	*/	
		public function row($query,$params = null,$fetchmode = PDO::FETCH_ASSOC)
		{				
			$this->Init($query,$params);
			return $this->sQuery->fetch($fetchmode);			
		}
       /**
	*	Returns the value of one single field/column
	*
	*	@param  string $query
	*	@param  array  $params
	*	@return string
	*/	
		public function single($query,$params = null)
		{
			$this->Init($query,$params);
			return $this->sQuery->fetchColumn();
		}
       /**	
	* Writes the log and returns the exception
	*
	* @param  string $message
	* @param  string $sql
	* @return string
	*/
	protected function ExceptionLog($message , $sql = "")
	{
		$exception  = 'Unhandled Exception. <br />';
		$exception .= $message;
		$exception .= "<br /> You can find the error back in the log.";
		if(!empty($sql)) {
			# Add the Raw SQL to the Log
			$message .= "\r\nRaw SQL : "  . $sql;
		}
			# Write into log
			$this->log->write($message);
		return $exception;
	}			
}

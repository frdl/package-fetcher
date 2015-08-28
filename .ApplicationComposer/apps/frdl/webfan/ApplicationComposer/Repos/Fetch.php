<?php
/**
 * Copyright  (c) 2015, Till Wehowski
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. Neither the name of frdl/webfan nor the
 *    names of its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY frdl/webfan ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL frdl/webfan BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 */
namespace frdl\ApplicationComposer\Repos;
use frdl\ApplicationComposer;

class Fetch
{
   protected $o; //options	
   protected $r; //result
   
   protected $repos = null;
   protected $db;
	
   function __construct($options = array()){
   	  $this->o = array_merge($this->defaultOptions(), $options);
   	 $this->db =  \frdl\xGlobal\webfan::db();
   }	

  
   public function __call($name, $args){
   	   $method = '_q_'.$name;
   	   if(is_callable(array($this, $method))){
	   	  return call_user_func_array(array($this,$method), $args);
	   }
	   
	  $trace = debug_backtrace();
  	 trigger_error(
		            'Undefined method call: ' . $name . '('.$method.')' .
		           ' in ' . $trace[0]['file'] .
		           ' on line ' . $trace[0]['line'],
		           E_USER_WARNING);
		return $this;
   }
     
     
  public function getActiveRepositories($refresh = false){
  	if(true === $refresh && is_array($this->repos))return $this->repos;

    try{
     $R = new \frdl\ApplicationComposer\Repository( array(), $this->db->settings(), $this->db );
    // $this->repos  = $R->search(array('_use' => 1));
      $this->repos  = $R->all();			
	}catch(\Exception $e){
      trigger_error($e->getMessage().' in '.__METHOD__.' '.__LINE__, E_USER_ERROR);
     $this->repos = array();
	}	
	
	
	return $this->repos;
  }    
     

   public function defaultOptions(){
   	  return array(
   	        'cache_time' => 8 * 60,
   	        'save' => false,
   	        'debug' => false,
   	        'cachekey' => '~pmfetch'.sha1(get_class($this)),
   	  );
   }	
   
   
   protected function cachefile($sub){
   	 return $this->o['DIRS']['cache'] . $this->o['cachekey'].'.'.sha1($sub).'.'.strlen($sub).'.php';
   }
   
   protected function cache($sub, $value = null){
   	  $file = $this->cachefile($sub);
   	  if(null === $value && (!file_exists($file) || filemtime($file) < time() -  $this->o['cache_time']))return null;
   	  if(null === $value){
   	  	try{
   	  	    require $file;
   	  	    if($time < time() -  $this->o['cache_time'])return null;
   	  	    return $value;			
		}catch(\Exception $e){
			trigger_error($e->getMessage(), E_USER_ERROR);
		}

   	  	}
   	  	
   	  $code = "<?php
  \$time = ".time().";
  \$expires = ".(time() + intval($this->o['cache_time'])).";
  \$value = ".str_replace("stdClass::__set_state", "(object)", var_export($value, true)).";
             	  
";
   	   file_put_contents($file, $code);
   }
   
   
   protected function result(){
   	 return $this->r;
   }
   	
   protected function _q_info(){
   	
   }
   
   protected function _q_all(){
   	
   }
   
   protected function _q_search($query){
     	$k = 'search '.$query;
     	$cache = $this->cache($k, null);
     	if(is_array($cache)){
     	    $this->r =$cache;
			return $this->r;
		}
   	
   	   $this->r = array(); 
   	   foreach($this->getActiveRepositories(false) as $num => $repos){
 	   	  $classname = urldecode($repos['fetcher_class']);
 	   	  if(1!==intval($repos['_use']))continue;
 	   	 try{
	   	  $f = new $classname;
	   	  $f->setConfig($this->o);
	   	  $r = $f->search($query);
	   	  $this->r[] = $r;		 	
		 }catch(\Exception $e){
		 	trigger_error($e->getMessage(), E_USER_ERROR);
		 }

	   	  
	   }
	   
	
	   $this->cache($k, $this->r);
	   return $this->r;
   }
   
   protected function _q_package($vendor, $packagename){
   	
   }  
  
   protected function _q_download($vendor, $packagename){
   	
   }  	
}
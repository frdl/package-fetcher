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

class Packagist extends PackageFetcher
{
	
   protected $purl = 'https://packagist.org';



   public function info(){
   	
   }
   
   public function all(){
   	
   }
   
   public function search($query){
   	  return $this->_search($query);
   }
   public function _search($query, array $search = array())
    {
        $result = array();
        $search['q'] = $query;
        $url =  $this->url('/search.json?' . http_build_query($search));

        $this->request($url, $body, $error);
        
        if('' !== $error)return $error;
        try{
			$r = json_decode($body);
			$result = $r->results;
		   
		   
		if(isset($r->next))
		{
		do {
			$this->request($r->next, $body, $error);
			 if('' !== $error)return $result;
		 	$r = json_decode($body);
			$result = array_merge($result, $r->results);
        } while (isset($r->next));			
		}

	
			
		}catch(\Exception $e){
			trigger_error($e->getMessage(), E_USER_WARNING);
			return false;
		}
        
        return $result;
    }
   
   
   public function package($vendor, $packagename){
        $result = array();
        $url =  $this->url('/packages/'.$vendor.'/'.$packagename.'.json');

        $this->request($url, $body, $error);
        
        if('' !== $error)return $error;
        try{
			$r = json_decode($body);
			$result = $r;
		    if(isset($result->package->versions))$result->package->versions=(array)$result->package->versions;
			
		}catch(\Exception $e){
			trigger_error($e->getMessage(), E_USER_WARNING);
			return false;
		}
        
        return $result; 	
   }  
	
}
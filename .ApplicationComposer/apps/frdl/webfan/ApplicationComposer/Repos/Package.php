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


abstract class Package extends PackageFetcher
{
	
   protected $_data = null;

   function __construct(){
   	  $this->_data = &$this->data();
   }

   public function __call($name, $args){
        if('data' === $name){
			if(0 === count($args)){
				  return $this->_data();
		    }elseif(1 === count($args)){
				  return $this->_data($args[0]);
		    }
		}	
		
		
   }
   
   /**
   *   @param   $data [\frdl\o]  OPTIONAL
   *   @returns \frdl\o 
     e.g 1: {
            "type": "package",
            "package": {
                "name": "TerraProject/pragmamx",
                "version": "2.2.2",
                "source": {
                    "url": "http://download.pragmamx.org/pmx/pragmaMx_2.2_2015-04-01--18-33_full.zip",
                    "type": "zip"
                }
            }
        },
      #  
      e.g.2:  {
            "type": "package",
            "package": {
                "name": "composer/composer",
                "version": "1.0.0-alpha10",
                "source": {
                    "url": "https://github.com/composer/composer/archive/1.0.0-alpha10.zip",
                    "type": "zip"
                }
            }
        }
  
   */
   abstract protected function _data(\frdl\o $data = null);
 	
   public function info(){
   	
   }
   
   public function all(){
   	
   }
   
   public function search($query){
   	
   }
   
   public function package($vendor, $packagename){
   	
   }  
	
}
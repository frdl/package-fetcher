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

abstract class PackageFetcher
{
   const DEF_CLIENT = '\frdl\Broxy';
   protected $http_class;	
	
   protected $config;
   protected $Client = null;
   

   public function __construct($config = null){
   	  if(null !== $config) $this->config = $config;
   	  $this->Client($client, ((isset($config['http_class'])) ? $config['http_class'] : self::DEF_CLIENT ));
   	  return $config;
   }	
   
   
   public function cache($key, $value){
   	
   }
   
   
   public function Client(&$client = null, $classname = null, $create = false){
   	  $this->http_class = (null !== $classname && '' !== $classname && class_exists($classname)) ? $classname : self::DEF_CLIENT;
   	  if(null === $this->Client || true === $create){
	  	$this->Client = new $this->http_class;
	  }
	  
	  $client = $this->Client;
	  return $this;
   }
   	
   abstract public function info();
   abstract public function all();
   abstract public function search($query);
   abstract public function package($vendor, $packagename);   
	
}
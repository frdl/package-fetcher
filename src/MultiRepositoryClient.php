<?php
namespace frdl\PackageFetcher;

use frdl\PackageFetcher\RepositoryClientInterface;
  
class MultiRepositoryClient
{
    protected $clients = [];
    public function __construct(array $clients = null){
      if(is_array($clients)){
        foreach($clients as $client){
           $this->addClient($client);
        }
      }
    }
    
    public function addClient(RepositoryClientInterface $client){
       $this->clients[] = $client;
       return $this;
    }
    public function _call($name, $params){
      $result = [];
       foreach($this->clients as $client){
             if(in_array($name,$client->supports()){
                $r = call_user_func_array($client->service($name), $params);
                $r = (array)$r;
                $result = array_merge($result, $r);
             }
       }
       
       return $result;
    }
}

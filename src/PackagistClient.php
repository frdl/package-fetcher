<?php
namespace frdl\PackageFetcher;

use Packagist\Api\Client as PClient;
use frdl\PackageFetcher\AbstractComposerRepositoryClient as RepositoryClient;

class PackagistClient extends RepositoryClient
{
     protected $Client;
     protected $services;
     public function __construct($packagistUrl = "https://packagist.org"){
        $this->Client = new PClient(null, null, $packagistUrl);
        $this->services = [
          RepositoryClient::SERVICE_PACKAGE => [$this,'get'],
          RepositoryClient::SERVICE_ALL => [$this,'all'],
          RepositoryClient::SERVICE_SEARCH => [$this,'search'],
          RepositoryClient::SERVICE_POPULAR => [$this,'popular'],
      ];      
     }
     
     public function __call($name, $params){
       return call_user_func_array([$this->Client, $name], $params);
     }
     
    public function service(string $service) :?\callable
    {
        if(isset($this->services[$service]) && is_callable($this->services[$service])){
          return $this->services[$service];
        }
        
        return null;
    }
    
    public function supports() : array
    {
      return array_keys($this->services);
    }
}

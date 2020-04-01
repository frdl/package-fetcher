<?php
namespace frdl\PackageFetcher;

use Packagist\Api\Client as BaseClient
use frdl\PackageFetcher\RepositoryClientInterface;
  
    
abstract class AbstractComposerRepositoryClient extends BaseClient implements RepositoryClientInterface
{
    const SERVICE_PACKAGE = 'package';
    const SERVICE_ALL = 'all';
    const SERVICE_SEARCH = 'search';
    const SERVICE_POPULAR = 'popular';
    

    public function getPackagistUrl() : string
    {
        return $this->packagistUrl;
    }
    public function setPackagistUrl($packagistUrl) :void
    {
        $this->packagistUrl = $packagistUrl;
    }
    abstract public function service(string $service) :?\callable;
    abstract public function supports() :array;
}

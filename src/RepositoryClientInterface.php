<?php
namespace frdl\PackageFetcher;


interface RepositoryClientInterface
{
    const SERVICE_PACKAGE = 'package';
    const SERVICE_ALL = 'all';
    const SERVICE_SEARCH = 'search';
    const SERVICE_POPULAR = 'popular';
    
    public function service(string $service) :?\callable;
    public function supports() :array;
}

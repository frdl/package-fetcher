<?php
namespace frdl\PackageFetcher;

use Packagist\Api\Client as PClient;
use frdl\PackageFetcher\AbstractComposerRepositoryClient as RepositoryClient;

class FrdlRepositoryClient extends RepositoryClient
{
     protected $Client;
     protected $services;
     public function __construct($packagistUrl = 'https://packages.frdl.de'){
        $this->Client = new PClient(null, null, $packagistUrl);
        $this->services = [
          RepositoryClient::SERVICE_PACKAGE => [$this,'package'],
          RepositoryClient::SERVICE_ALL => [$this,'all'],
  //        RepositoryClient::SERVICE_SEARCH => [$this,'search'],
    //      RepositoryClient::SERVICE_POPULAR => [$this,'popular'],
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
   /**
     * Search packages
     *
     * Available filters :
     *
     *    * vendor: vendor of package (require or require-dev in composer.json)
     *    * type:   type of package (type in composer.json)
     *    * tags:   tags of package (keywords in composer.json)
     *
     * @since 1.0
     *
     * @param string $query   Name of package
     * @param array  $filters An array of filters
     *
     * @return array The results
    
    public function search($query, array $filters = array())
    {
        $results = $response = array();
        $filters['q'] = $query;
        $url = '/search.json?' . http_build_query($filters);
        $response['next'] = $this->url($url);

        do {
            $response = $this->request($response['next']);
            $response = $this->parse($response);
            $results = array_merge($results, $this->create($response));
        } while (isset($response['next']));

        return $results;
    }
 */
    /**
     * Retrieve full package informations
     *
     * @since 1.0
     *
     * @param string $package Full qualified name ex : myname/mypackage
     *
     * @return \Packagist\Api\Result\Package A package instance
     */
    public function package($package)
    {
        $providers = $this->respond($this->url('/packages.json'));
        $providers =(array)$providers;
        $providers['providers']=(array)$providers['providers'];
        foreach($providers['providers'] as $packagename => $pakageinfo){
           if($packagename !== $package)continue;
           $pakageinfo=(array)$pakageinfo;
           $uri = str_replace(['%package%', '%hash%'], [$packagename, $pakageinfo['sha256']], $providers['providers-url']);
           return $this->respond($this->url($uri));
        }
        
       
    }

    /**
     * Search packages
     *
     * Available filters :
     *
     *    * vendor: vendor of package (require or require-dev in composer.json)
     *    * type:   type of package (type in composer.json)
     *    * tags:   tags of package (keywords in composer.json)
     *
     * @since 1.0
     *
     * @param array  $filters An array of filters
     *
     * @return array The results
     */
    public function all(array $filters = array())
    {
        $url = '/packages/list.json';
        if ($filters) {
            $url .= '?'.http_build_query($filters);
        }

        return $this->respond($this->url($url));
    }

    /**
     * Popular packages
     *
     * @since 1.3
     *
     * @param $total
     * @return array The results
     */
     /*
    public function popular($total)
    {
        $results = $response = array();
        $url = '/explore/popular.json?' . http_build_query(array('page' => 1));
        $response['next'] = $this->url($url);

        do {
            $response = $this->request($response['next']);
            $response = $this->parse($response);
            $results = array_merge($results, $this->create($response));
        } while (count($results) < $total && isset($response['next']));

        return array_slice($results, 0, $total);
    }
*/
    /**
     * Assemble the packagist URL with the route
     *
     * @param string $route API Route that we want to achieve
     *
     * @return string Fully qualified URL
     */
    protected function url($route)
    {
        return $this->packagistUrl.$route;
    }

    /**
     * Execute the url request and parse the response
     *
     * @param string $url
     *
     * @return array|\Packagist\Api\Result\Package
     */
    protected function respond($url)
    {
        $response = $this->request($url);
        $response = $this->parse($response);

        return $this->create($response);
    }

    /**
     * Execute the url request
     *
     * @param string $url
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function request($url)
    {
        if (null === $this->httpClient) {
            $this->httpClient = new HttpClient();
        }

        return $this->httpClient
            ->get($url)
            ->getBody();
    }

    /**
     * Decode json
     *
     * @param string $data Json string
     *
     * @return array Json decode
     */
    protected function parse($data)
    {
        return json_decode($data, true);
    }

    /**
     * Hydrate the knowing type depending on passed data
     *
     * @param array $data
     *
     * @return array|\Packagist\Api\Result\Package
     */
    protected function create(array $data)
    {
        if (null === $this->resultFactory) {
            $this->resultFactory = new Factory();
        }

        return $this->resultFactory->create($data);
    }

    /**
     * Change the packagist URL
     *
     * @since 1.1
     *
     * @param string $packagistUrl URL
     */
    public function setPackagistUrl($packagistUrl)
    {
        $this->packagistUrl = $packagistUrl;
    }

    /**
     * Return the actual packagist URL
     *
     * @since 1.1
     *
     * @return string URL
     */
    public function getPackagistUrl()
    {
        return $this->packagistUrl;
    }    
    
}

<?php
/**
 * Created by PhpStorm.
 * User: jover
 * Date: 09/08/16
 * Time: 12:17
 */

namespace RipeDB;

use Zend\Http\Client as HttpClient;
use SimpleXMLElement;

class Client
{
    private $_RESTurl =
        [
            'production' => 'http://rest.db.ripe.net/search',
            'test'       => 'http://rest-test.db.ripe.net/search'
        ];

    private $_httpclient = null;

    private $_environment = null;

    /**
     * Client constructor.
     * @param string $environment
     * @throws \Exception
     */
    public function __construct($environment = 'production')
    {
        // check the environment
        if (!array_key_exists($environment, $this->_RESTurl))
        {
            throw (
                new \Exception(
                    "Environment '$environment' does not exist, you can use [" . implode(', ', $this->_RESTurl) ."]"
                )
            );
        }
        $this->_environment = $environment;

        // initialise the Zend Http Cient
        $this->_httpclient = new HttpClient();
    }

    /**
     * @param array $searchterms
     * @return array containing SimpleXMLElements
     * @throws \Exception
     */
    function search($searchterms = [])
    {
        $results = [];
        foreach ($searchterms as $term) {
            // construct query url
            $url = $this->_RESTurl[$this->_environment] . "?query-string=$term";

            $this->_httpclient->setUri($url);
            $response = $this->_httpclient->send();

            if (!$response->isSuccess()) {
                throw (
                    new \Exception(
                        "Something went wrong, while talking to [$url], statuscode: {$response->getStatusCode()}"
                    )
                );
            }

            // return json data as an multidimensional array
            $results[] = new SimpleXMLElement($response->getBody());
        }

        return $results;
    }
}
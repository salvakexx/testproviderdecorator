<?php

namespace SomeApiIntegration\Integration;

use SomeApiIntegration\Exceptions\ApiException;
use SomeApiIntegration\Interfaces\DataProviderInterface;

/**
 * Class DataProvider
 * @package SomeApiIntegration\Integration
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @var
     */
    private $host;
    /**
     * @var
     */
    private $user;
    /**
     * @var
     */
    private $password;

    /**
     * @param $host
     * @param $user
     * @param $password
     */
    public function __construct($host, $user, $password)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @param array $request
     * @return array
     * @throws ApiException
     */
    public function get(array $request)
    {
        try {
            return [
                'response' => [
                    'nice' => [
                        'data' => true
                    ]
                ]
            ];
        }catch (\Exception $exception){
            throw new ApiException();
        }

    }
}
<?php

namespace SomeApiIntegration\Decorator;

use DateTime;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use \Psr\Cache\CacheItemInterface;
use SomeApiIntegration\Exceptions\ApiException;
use SomeApiIntegration\Interfaces\DataProviderInterface;

/**
 * Class DecoratorManager
 * @package SomeApiIntegration\Decorator
 */
class DecoratorManager
{
    /**
     * @var CacheItemPoolInterface
     */
    public $cache;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var DataProviderInterface
     */
    public $dataProvider;

    /**
     * @param DataProviderInterface $dataProvider
     * @param CacheItemPoolInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct(DataProviderInterface $dataProvider, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $this->setDataProvider($dataProvider);
        $this->setCache($cache);
        $this->setLogger($logger);
    }


    /**
     *
     * Get the response from data provider or cache if isHit
     *
     * @param array $input
     * @return array|mixed
     */
    public function getResponse(array $input)
    {
        try {
            $cacheItem = $this->getCacheItemByInput($input);

            if ($this->isCacheItemValid($cacheItem)) {
                return $cacheItem->get();
            }

            $this->updateCacheItem($cacheItem,$result = $this->dataProvider->get($input));

            return $result;
        }
        catch (ApiException $exception){
            $this->logError($exception,$input,'error');
        }
        catch (Exception $exception) {
            $this->logError($exception,$input);
        }

        return [];
    }

    /**
     * Save formatted log message
     *
     * @param Exception $exception
     * @param array $parameters
     * @param string $type = 'critical|error' etc.
     */
    protected function logError(Exception $exception, array $parameters = [], $type = 'critical')
    {
        //add trace and lines if needed
        $formattedText = 'Error : '.$exception->getMessage();

        $formattedText.= ' Parameters : '.json_encode($parameters);

        $this->logger->{$type}($formattedText);
    }

    /**
     * @param  array $input
     * @return CacheItemInterface
     */
    protected function getCacheItemByInput($input)
    {
        return $this->cache->getItem($this->getCacheKey($input));
    }

    /**
     * @param  CacheItemInterface $cacheItem
     * @return boolean
     */
    protected function isCacheItemValid(CacheItemInterface $cacheItem)
    {
        return ($cacheItem->isHit());
    }

    /**
     * @param CacheItemInterface $cacheItem
     * @param array $result
     */
    protected function updateCacheItem(CacheItemInterface $cacheItem, array $result)
    {
        $cacheItem
            ->set($result)
            ->expiresAt(
                (new DateTime())->modify('+1 day')
            );
    }

    /**
     * Generate Cache key based on input data
     *
     * @param  array $input
     * @return string
     */
    public function getCacheKey(array $input)
    {
        return get_class($this->dataProvider).'-'.md5(json_encode($input));
    }

    /**
     * @return CacheItemPoolInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param CacheItemPoolInterface $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return DataProviderInterface
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
    }

    /**
     * @param DataProviderInterface $dataProvider
     */
    public function setDataProvider($dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

}
<?php

namespace SomeApiIntegration\Interfaces;

interface DataProviderInterface
{
    /**
     * @param array $request
     *
     * @return array
     */
    public function get(array $request);
}
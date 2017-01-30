<?php

namespace AppBundle\Service\Finder;

class GithubFinder
{
    private $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function findUsers($name)
    {
        $response = $this->client->get(sprintf('search/users?q=%s+in:login', $name));
        $result = json_decode($response->getBody()->getContents(), true);

        return $result['items'];
    }
}

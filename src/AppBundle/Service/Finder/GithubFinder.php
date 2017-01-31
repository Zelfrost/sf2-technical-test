<?php

namespace AppBundle\Service\Finder;

use GuzzleHttp\Client;

class GithubFinder
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $username
     *
     * @return array
     */
    public function findUsers($username)
    {
        $response = $this->client->get(sprintf('search/users?q=%s+in:login', $username));
        $result = json_decode($response->getBody()->getContents(), true);

        $users = [];
        foreach ($result['items'] as $user) {
            $users[] = $user['login'];
        }

        return $users;
    }

    /**
     * @param string      $username
     * @param string|null $repositoryName
     *
     * @return array
     */
    public function findRepositories($username, $repositoryName = null)
    {
        $url = sprintf(
            'search/repositories?q=%s+user:%s',
            $repositoryName,
            $username
        );
        $response = $this->client->get($url);
        $result = json_decode($response->getBody()->getContents(), true);

        $repositories = [];
        foreach ($result['items'] as $repository) {
            $repositories[$repository['full_name']] = $repository['full_name'];
        }

        return $repositories;
    }
}

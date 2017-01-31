<?php

namespace AppBundle\Service\Finder;

class GithubFinder
{
    private $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

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

<?php

namespace AppBundle\Service\Finder;

use AppBundle\Service\Finder\GithubFinder;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery as m;

class GithubFinderTest extends \PHPUnit_Framework_TestCase
{
    public function testFindUsersWithNoResult()
    {
        $response = m::mock(Response::class);
        $response->shouldReceive('getBody->getContents')
            ->withNoArgs()
            ->once()
            ->andReturn('{"items":[]}');

        $client = m::mock(Client::class);
        $client->shouldReceive('get')
            ->with('search/users?q=Test+in:login')
            ->once()
            ->andReturn($response);

        $finder = new GithubFinder($client);
        $users = $finder->findUsers('Test');

        $this->assertEmpty($users);
    }

    public function testFindUsersWithResults()
    {
        $body = ['items' => [
            ['login' => 'Test'],
            ['login' => 'TestA'],
            ['login' => 'TestB'],
        ]];

        $response = m::mock(Response::class);
        $response->shouldReceive('getBody->getContents')
            ->withNoArgs()
            ->once()
            ->andReturn(json_encode($body));

        $client = m::mock(Client::class);
        $client->shouldReceive('get')
            ->with('search/users?q=Test+in:login')
            ->once()
            ->andReturn($response);

        $finder = new GithubFinder($client);
        $users = $finder->findUsers('Test');

        $this->assertEquals(['Test', 'TestA', 'TestB'], $users);
    }

    public function testFindRepositoriesWithNoNameAndNoResult()
    {
        $response = m::mock(Response::class);
        $response->shouldReceive('getBody->getContents')
            ->withNoArgs()
            ->once()
            ->andReturn('{"items":[]}');

        $client = m::mock(Client::class);
        $client->shouldReceive('get')
            ->with('search/repositories?q=+user:Test')
            ->once()
            ->andReturn($response);

        $finder = new GithubFinder($client);
        $repositories = $finder->findRepositories('Test');

        $this->assertEmpty($repositories);
    }

    public function testFindRepositoriesWithNoNameButResults()
    {
        $body = ['items' => [
            ['full_name' => 'Repo'],
            ['full_name' => 'RepoA'],
            ['full_name' => 'RepoB'],
        ]];

        $response = m::mock(Response::class);
        $response->shouldReceive('getBody->getContents')
            ->withNoArgs()
            ->once()
            ->andReturn(json_encode($body));

        $client = m::mock(Client::class);
        $client->shouldReceive('get')
            ->with('search/repositories?q=+user:Test')
            ->once()
            ->andReturn($response);

        $finder = new GithubFinder($client);
        $repositories = $finder->findRepositories('Test');

        $this->assertEquals(['Repo' => 'Repo', 'RepoA' => 'RepoA', 'RepoB' => 'RepoB'], $repositories);
    }

    public function testFindRepositoriesWithNameAndNoResult()
    {
        $response = m::mock(Response::class);
        $response->shouldReceive('getBody->getContents')
            ->withNoArgs()
            ->once()
            ->andReturn('{"items":[]}');

        $client = m::mock(Client::class);
        $client->shouldReceive('get')
            ->with('search/repositories?q=Repo+user:Test')
            ->once()
            ->andReturn($response);

        $finder = new GithubFinder($client);
        $repositories = $finder->findRepositories('Test', 'Repo');

        $this->assertEmpty($repositories);
    }

    public function testFindRepositoriesWithNameAndResults()
    {
        $body = ['items' => [
            ['full_name' => 'Repo'],
            ['full_name' => 'RepoA'],
            ['full_name' => 'RepoB'],
        ]];

        $response = m::mock(Response::class);
        $response->shouldReceive('getBody->getContents')
            ->withNoArgs()
            ->once()
            ->andReturn(json_encode($body));

        $client = m::mock(Client::class);
        $client->shouldReceive('get')
            ->with('search/repositories?q=Repo+user:Test')
            ->once()
            ->andReturn($response);

        $finder = new GithubFinder($client);
        $repositories = $finder->findRepositories('Test', 'Repo');

        $this->assertEquals(['Repo' => 'Repo', 'RepoA' => 'RepoA', 'RepoB' => 'RepoB'], $repositories);
    }
}

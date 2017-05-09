<?php
require_once './vendor/autoload.php';

use Github\Client;
use Lpdigital\Github\Entity\Commit;
use Lpdigital\Github\Entity\Issue;

class GitHubClient
{
    private $owner;
    private $repository;
    private $token;
    private $client;

    public function __construct($owner, $repository, $token)
    {
        $this->owner = $owner;
        $this->repository = $repository;
        $this->token = $token;

        $this->client = new Client(new Github\HttpClient\CachedHttpClient());
        $this->client->authenticate($this->token, Client::AUTH_HTTP_TOKEN);
    }

    public function comment($issueId, $comment)
    {
        return $this->client
            ->api('issue')
            ->comments()
            ->create(
                $this->owner,
                $this->repository,
                $issueId,
                ['body' => $comment]
            );
    }

    public function getAllCommits()
    {
        $commitsApi = $this->client
            ->api('repo')
            ->commits()->all(
                $this->owner,
                $this->repository,
                ['sha' => 'master']
            );
        
        $commits = [];
        
        foreach ($commitsApi as $commitApi) {
            $commits[] = Commit::createFromData($commitApi);
        }

        return $commits;
    }

    public function getAllIssuesFromUser($userLogin)
    {
        $issuesApi = $this->client
            ->api('search')
            ->issues('repo:'.$this->owner .'/'.$this->repository.' author:'.$userLogin)
        ;

        $issues = [];

        foreach ($issuesApi['items'] as $issueApi) {
            if (
                !array_key_exists('pull_request', $issueApi)
                && $userLogin === $issueApi['user']['login']
            ){
                $issues[] = Issue::createFromData($issueApi);
            }
        }

        return $issues;
    }

    public function isAContributor($userLogin)
    {
        $commits = $this->getAllCommits();

        foreach ($commits as $commit) {
            $authorName = $commit->getAuthor()->getName();
            if ($authorName === $userLogin){
                return true;
            }
        }

        return false;
    }

    public function isAnIssuer($userLogin)
    {
        $issues = $this->getAllIssuesFromUser($userLogin);

        return count($issues) > 1;
    }
}
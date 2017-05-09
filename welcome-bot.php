<?php

require_once './vendor/autoload.php';
require_once './github-client.php';

use Lpdigital\Github\Parser\WebhookResolver;
use Lpdigital\Github\EventType\ActionableEventInterface;

$repositoryOwner = 'XXXXXXXX';
$repositoryName  = 'YYYYYYYY';
$personalToken   = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXX';

$client = new GitHubClient(
    $repositoryOwner,
    $repositoryName,
    $personalToken
);

if ('POST' === $_SERVER['REQUEST_METHOD']) {
     $decodedJson = json_decode(file_get_contents('php://input'), true);
     $resolver    = new WebhookResolver();
     $event       = $resolver->resolve($decodedJson);
     $eventName   = $event::name();
     writeLog($event::name());

     if (isValid($event)) {
         if ($eventName === 'IssuesEvent') {
             $userLogin = $event->issue->getUser()->getLogin();
             if(!$client->isAnIssuer($userLogin)) {
                 $client->comment($event->issue->getNumber(), "Welcome $userLogin this is your first issue");
             }
         }else {
             $userLogin = $event->pullRequest->getUser()->getLogin();
             if(!$client->isAContributor($userLogin)) {
                 $client->comment($event->number, "Welcome $userLogin this is your first pull request.");
             }
         }
     }
}

function isValid($event)
{
    if ($event instanceof ActionableEventInterface) {
         if (
                in_array($event::name(), ['IssuesEvent', 'PullRequestEvent'])
                && 'opened' == $event->getAction()
             ){
                 return true;
             }
     }
     return false;
}

function writeLog($message)
{
    file_put_contents('events.log', PHP_EOL. '['.date('d/m/Y h:i:s').'] '.$message, FILE_APPEND | LOCK_EX);
}
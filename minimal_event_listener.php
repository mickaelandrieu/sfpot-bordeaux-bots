<?php
require_once './vendor/autoload.php';

use Lpdigital\Github\Parser\WebhookResolver;
// GitHub send data using POST method

if ('POST' === $_SERVER['REQUEST_METHOD']) {
     // get the POST Request Body
     $decodedJson = json_decode(file_get_contents('php://input'), true);
     $resolver    = new WebhookResolver();
     $event       = $resolver->resolve($decodedJson); // for ex, we get instance of PullRequestEvent

     file_put_contents('minimal_events.log', PHP_EOL. $event::name(), FILE_APPEND | LOCK_EX);
}
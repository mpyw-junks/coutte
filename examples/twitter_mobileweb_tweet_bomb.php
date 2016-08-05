<?php

require __DIR__ . '/../vendor/autoload.php';

use mpyw\Coutte\Client;
use mpyw\Co\Co;
use mpyw\Co\CURLException;

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=UTF-8', true, 400);
    echo 'This script is only for php-cli.';
    exit(1);
}
set_time_limit(0);

function input($msg, $hidden = false)
{
    echo $msg;
    $hidden = $hidden && DIRECTORY_SEPARATOR === '/';
    $input = $hidden
        ? `stty -echo; read x; stty echo; printf "\$x"`
        : trim(fgets(STDIN));
    $hidden && fwrite(STDOUT, "\n");
    return $input;
}

Co::wait(function () {

    $client = new Client;
    $crawler = yield $client->requestAsync('GET', 'https://mobile.twitter.com/login');
    $form = $crawler->filter('*[name=commit]')->form([
        'session[username_or_email]' => input('screen_name: '),
        'session[password]' => input('password: ', true),
    ]);
    $crawler = yield $client->submitAsync($form);

    if ($client->getResponse()->getStatus() !== 200) {
        echo "Failed\n";
        return;
    }

    $crawler = yield $client->requestAsync('GET', 'https://mobile.twitter.com/compose/tweet');

    for ($i = 0; $i < 5; ++$i) {
        $form = $crawler->filter('*[name=commit]')->form([
            'tweet[text]' => "@tos HAHAHA!! [$i] " . mt_rand(),
        ]);
        Co::async($client->submitAsync($form), false);
    }

});

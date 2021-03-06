<?php

namespace Raq\Examples;

use Psr\Http\Message\ResponseInterface;

require_once __DIR__ . '/../vendor/autoload.php';

$result = \Raq\fetch('http://foo.bar')->query(function (\Raq\QueryFactory $fac) {
    // callable accepting:
    // function (ResponseInterface, Builder);
    return $fac->htmlQuery()->select('a > h2 > abbr');
});

$requests = [
    'http://foo.bar/user1',
    'http://foo.bar/user2',
    'http://foo.bar/user3',
    'http://foo.bar/user4',
];

$multiResult = \Raq\gen2arr(\Raq\fetchAll($requests)->queryAll(function (\Raq\QueryFactory $fac) {
    return $fac->htmlQuery()->select('a > h2 > abbr');
}));



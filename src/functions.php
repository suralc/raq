<?php
/**
 * functions.php
 *
 * MIT LICENSE
 *
 * LICENSE: This source file is subject to the MIT license.
 * A copy of the licenses text was distributed alongside this
 * file (usually the repository or package root). The text can also
 * be obtained on one of the following sources:
 * * http://opensource.org/licenses/MIT
 * * https://github.com/suralc/raq/blob/master/LICENSE
 *
 * @author     suralc <suralc.github@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */

namespace Raq;

use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;

/**
 * @param string|\Psr\Http\Message\RequestInterface $target
 * @param \GuzzleHttp\Client $client
 * @param array $requestOptions
 * @return \Raq\ResponseHandler|\Raq\SingleResponseHandler
 */
function fetch($target, Client $client = null, array $requestOptions = [])
{
    if ($client === null) {
        // @codeCoverageIgnoreStart
        $client = _getDefaultClientInstance();
        // @codeCoverageIgnoreEnd
    }
    if ($target instanceof RequestInterface) {
        return fetchFromRequest($target, $client, $requestOptions);
    } elseif (is_string($target)) {
        return fetchFromString($target, $client, $requestOptions);
    } else {
        throw new \InvalidArgumentException('Only strings and implementations of the Psr-7 RequestInterface are accepted.');
    }
}

/**
 * @param array $targets
 * @param \GuzzleHttp\Client $client
 * @param array $requestOptions
 * @return \Raq\MultiResponseHandler
 */
function fetchAll(array $targets, Client $client = null, array $requestOptions = [])
{
    if ($client === null) {
        // @codeCoverageIgnoreStart
        $client = _getDefaultClientInstance();
        // @codeCoverageIgnoreEnd
    }
    $requests = [];
    foreach ($targets as $target) {
        if ($target instanceof RequestInterface) {
            $requests[] = fetchFromRequest($target, $client, $requestOptions);
        } elseif (is_string($target)) {
            $requests[] = fetchFromString($target, $client, $requestOptions);
        } else {
            throw new \InvalidArgumentException('Only strings and implementations of the Psr-7 RequestInterface are accepted.');
        }
    }

    return new MultiResponseHandler($requests);
}


/**
 * @param string $target
 * @param \GuzzleHttp\Client $client
 * @param array $requestOptions
 * @return \Raq\SingleResponseHandler
 */
function fetchFromString($target, Client $client, array $requestOptions = [])
{
    if (!is_string($target)) {
        throw new \InvalidArgumentException('Only strings are acceptable parameters for this function.' .
            'Use the more generic fetch function to use other data types.');
    }
    return new SingleResponseHandler($client->getAsync($target, $requestOptions));
}

/**
 * @param \Psr\Http\Message\RequestInterface $request
 * @param \GuzzleHttp\Client $client
 * @param array $requestOptions
 * @return \Raq\SingleResponseHandler
 */
function fetchFromRequest(RequestInterface $request, Client $client, array $requestOptions = [])
{
    return new SingleResponseHandler($client->sendAsync($request, $requestOptions));
}

/**
 * @param \Generator $generator
 * @return array
 */
function gen2arr(\Generator $generator)
{
    $values = [];
    foreach($generator as $key => $value) {
        $values[ $key ] = $value;
    }
    return $values;
}

/**
 * @return \GuzzleHttp\Client
 */
function _getDefaultClientInstance()
{
    static $client = null;
    if ($client === null) {
        $client = new Client();
    }
    return $client;
}

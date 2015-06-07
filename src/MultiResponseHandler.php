<?php
/**
 * MultiResponseHandler.php
 *
 * MIT LICENSE
 *
 * LICENSE: This source file is subject to the MIT license.
 * A copy of the licenses text was distributed alongside this
 * file (usually the repository or package root). The text can also
 * be obtained on one of the following sources:
 * * http://opensource.org/licenses/MIT
 * * https://github.com/suralc/pvra/blob/master/LICENSE
 *
 * @author     suralc <suralc.github@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */

namespace Raq;


use Psr\Http\Message\ResponseInterface;

class MultiResponseHandler
{
    /**
     * @var SingleResponseHandler[]
     */
    private $responseHandlers = [];

    /**
     * @param array $responses
     */
    public function __construct(array $responses = [])
    {
        /** @var ResponseInterface $response */
        foreach ($responses as $response) {
            if ($response instanceof SingleResponseHandler) {
                $this->responseHandlers[] = $response;
            } else {
                $this->responseHandlers[] = new SingleResponseHandler($response);
            }
        }
    }

    /**
     * @param callable $queryComposer
     * @param \Raq\QueryBuilder $builder
     * @return \Generator
     */
    public function queryAll(callable $queryComposer, QueryBuilder $builder = null)
    {
        if ($builder === null) {
            $builder = new QueryBuilder();
        }
        foreach ($this->responseHandlers as $handler) {
            yield $handler->query($queryComposer, $builder);
        }
    }

    public function getResponseHandlers()
    {
        return $this->responseHandlers;
    }
}

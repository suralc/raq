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


use Prophecy\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use Raq\Query\Exception\QueryHandlerException;

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
     * @param SingleResponseHandler|PromiseInterface|ResponseInterface $response
     * @return $this
     */
    public function addResponse($response)
    {
        if ($response instanceof SingleResponseHandler) {
            $this->responseHandlers[] = $response;
        } else {
            $this->responseHandlers[] = new SingleResponseHandler($response);
        }

        return $this;
    }

    /**
     * @param array|SingleResponseHandler[]|PromiseInterface[]|ResponseInterface[] $responses
     * @return $this
     */
    public function addResponses(array $responses = [])
    {
        foreach ($responses as $response) {
            $this->addResponse($response);
        }

        return $this;
    }

    /**
     * @param callable $queryComposer
     * @param \Raq\QueryFactory $factory
     * @return \Generator
     */
    public function queryAll(callable $queryComposer, QueryFactory $factory = null)
    {
        if ($factory === null) {
            $factory = new QueryFactory();
        }
        foreach ($this->responseHandlers as $handler) {
            try {
                yield $handler->query($queryComposer, $factory);
            } catch (QueryHandlerException $qe) {
                yield $qe;
            }
        }
    }

    /**
     * @return \Raq\SingleResponseHandler[]
     */
    public function getResponseHandlers()
    {
        return $this->responseHandlers;
    }
}

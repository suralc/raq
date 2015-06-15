<?php

namespace Raq\tests;


use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use Raq\MultiResponseHandler;
use Raq\Query\Exception\QueryHandlerException;
use Raq\QueryFactory;
use Raq\SingleResponseHandler;

class MultiResponseHandlerTest extends RaqTest
{
    public function testThatSingleResponseHandlersAreCorrectlyCreated()
    {
        $responseMock = $this->getMock(ResponseInterface::class);
        $singleHandler = new SingleResponseHandler($responseMock);
        $multi = new MultiResponseHandler([$singleHandler, $responseMock]);
        foreach ($multi->getResponseHandlers() as $handler) {
            $this->assertInstanceOf(SingleResponseHandler::class, $handler);
            $this->assertSame($responseMock, $handler->getResponseHandler());
        }
    }

    public function testFullQueryAllIteration()
    {
        $response = $this->getMock(ResponseInterface::class);
        $response->expects($this->exactly(3))->method('getBody')->willReturnOnConsecutiveCalls('1', '2', '3');
        $multi = new MultiResponseHandler([$response, $response, $response]);
        $queryBuilder = function () {
            return function (ResponseInterface $r) {
                return $r->getBody();
            };
        };
        $generator = $multi->queryAll($queryBuilder);
        $this->assertSame(['1', '2', '3'], iterator_to_array($generator));
    }

    public function testDeferredQueryCall()
    {
        $firstResponse = $this->getMock(ResponseInterface::class);
        $firstResponse->expects($this->exactly(1))->method('getBody')->willReturn('foo');
        $multi = new MultiResponseHandler([$firstResponse, $firstResponse]);
        $queryBuilder = function () {
            return function ($r) {
                return $r->getBody();
            };
        };
        foreach ($multi->queryAll($queryBuilder) as $content) {
            $this->assertSame('foo', $content);
            break;
        }
    }

    public function testThatQueryBuilderIsReusedOnConsecutiveCalls()
    {
        $builder = new QueryFactory();
        $response = $this->getMock(ResponseInterface::class);
        $multi = new MultiResponseHandler([$response, $response, $response]);
        $queryComposer = function (QueryFactory $b) use ($builder, $response) {
            $this->assertSame($builder, $b);
            return function (ResponseInterface $r) use ($response) {
                $this->assertSame($response, $r);
                return '';
            };
        };
        iterator_to_array($multi->queryAll($queryComposer, $builder));
    }

    public function testAddResponse()
    {
        $multi = new MultiResponseHandler();
        $this->assertCount(0, $multi->getResponseHandlers());
        $response = $this->getMock(ResponseInterface::class);
        $multi->addResponse(new SingleResponseHandler($response));
        $this->assertCount(1, $multi->getResponseHandlers());
        $return = $multi->addResponse($response);
        $this->assertCount(2, $multi->getResponseHandlers());
        $this->assertSame($response, $multi->getResponseHandlers()[1]->getResponseHandler());
        $this->assertSame($multi, $return);
    }

    public function testAddResponses()
    {
        $types = [];
        $in = [
            $this->getMock($types[0] = ResponseInterface::class),
            $this->getMock($types[1] = PromiseInterface::class),
            new SingleResponseHandler($this->getMock($types[2] = ResponseInterface::class))
        ];
        $multi = new MultiResponseHandler();
        $return = $multi->addResponses($in);
        $this->assertSame($multi, $return);
        $it = array_map(null, $types, $multi->getResponseHandlers());
        foreach ($it as $pair) {
            $this->assertInstanceOf(SingleResponseHandler::class, $pair[1]);
            $this->assertInstanceOf($pair[0], $pair[1]->getResponseHandler());
        }
    }

    public function testHandlingOfQueryHandlerExceptions()
    {
        $multi = new MultiResponseHandler([$this->getMock(ResponseInterface::class), $this->getMock(ResponseInterface::class)]);
        $results = \Raq\gen2arr($multi->queryAll(function(QueryFactory $q, SingleResponseHandler $sh) {
            static $i = 0;
            $i++;
            return function(ResponseInterface $r) use(&$i) {
                if($i === 1) {
                    throw (new QueryHandlerException())->setResponse($r)->setOptions(['runs' => (int)$i]);
                }
                return ['content' => 'hello'];
            };
        }));
        $this->assertInstanceOf(QueryHandlerException::class, $results[0]);
        $this->assertSame(1, $results[0]->getOptions()['runs']);
        $this->assertInstanceOf(ResponseInterface::class, $results[0]->getResponse());
        $this->assertSame(['content' => 'hello'], $results[1]);
    }
}

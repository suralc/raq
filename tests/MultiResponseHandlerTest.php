<?php

namespace Raq\tests;


use Psr\Http\Message\ResponseInterface;
use Raq\MultiResponseHandler;
use Raq\QueryBuilder;
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
        $queryBuilder = function() {
            return function(ResponseInterface $r) {
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
        $builder = new QueryBuilder();
        $response = $this->getMock(ResponseInterface::class);
        $multi = new MultiResponseHandler([$response, $response, $response]);
        $queryComposer = function(QueryBuilder $b) use($builder, $response) {
            $this->assertSame($builder, $b);
            return function(ResponseInterface $r) use($response) {
                $this->assertSame($response, $r);
                return '';
            };
        };
        iterator_to_array($multi->queryAll($queryComposer, $builder));
    }
}

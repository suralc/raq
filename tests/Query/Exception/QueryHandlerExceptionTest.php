<?php

namespace Query\Exception;


use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use Raq\Query\Exception\QueryHandlerException;
use Raq\tests\RaqTest;

class QueryHandlerExceptionTest extends RaqTest
{
    public function testReturns()
    {
        $e = new QueryHandlerException();
        $this->assertSame($e, $e->setOptions([]));
        $this->assertSame([], $e->getOptions());
        $this->assertSame($e, $e->setResponse($this->getMock(ResponseInterface::class)));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetResponseException()
    {
        $e = new QueryHandlerException();
        $e->setResponse(new \stdClass());
    }

    public function testThatSetResponseAcceptsPromisesAndResponses()
    {
        $e = new QueryHandlerException();
        $responseMock = $this->getMock(ResponseInterface::class);
        $this->assertSame($e, $e->setResponse($responseMock));
        $this->assertSame($responseMock, $e->getResponse());
        $promiseMock = $this->getMock(PromiseInterface::class);
        $this->assertSame($e, $e->setResponse($promiseMock));
        $this->assertSame($promiseMock, $e->getResponse());
    }
}

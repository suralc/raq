<?php

namespace Raq\tests;


use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use Raq\QueryBuilder;
use Raq\SingleResponseHandler;

class SingleResponseHandlerTest extends RaqTest
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage string is not a supported type
     */
    public function testConstructorExceptionWithScalarType()
    {
        new SingleResponseHandler('my string response');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage DateTime is not a supported type
     */
    public function testConstructorExceptionWithComplexType()
    {
        new SingleResponseHandler(new \DateTime());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidQueryComposerRuntimeException()
    {
        $response = $this->getMock(ResponseInterface::class);
        (new SingleResponseHandler($response))->query(function () {
            return '';
        });
    }

    public function testThatPromiseIsResolvedOnQueryCall()
    {
        $promiseMock = $this->getMock(PromiseInterface::class);
        $promiseMock->expects($this->exactly(1))->method('wait')->with(true)->willReturn('Hello World');
        $srh = new SingleResponseHandler($promiseMock);
        $srh->query(function () {
            return function ($response) {
                $this->assertSame('Hello World', $response);
            };
        });
    }

    public function testThatGetResponseHandlerAlwaysReturnsCurrentStateOfResponse()
    {
        $responseMock = $this->getMock(ResponseInterface::class);
        $promiseMock = $this->getMock(PromiseInterface::class);
        $promiseMock->expects($this->exactly(1))->method('wait')->with(true)->willReturn($responseMock);
        $srh = new SingleResponseHandler($promiseMock);
        $this->assertInstanceOf(PromiseInterface::class, $srh->getResponseHandler());
        $srh->query(function ($_, $responseHandler) use ($srh) {
            return function (ResponseInterface $r) use ($srh, $responseHandler) {
                $this->assertInstanceOf(ResponseInterface::class, $r);
                $this->assertSame($srh, $responseHandler);
            };
        });
        $this->assertInstanceOf(ResponseInterface::class, $srh->getResponseHandler());
    }

    public function testThatResponseIsNotModifiedOnGetResponseHandler()
    {
        $responseMock = $this->getMock(ResponseInterface::class);
        $responseMock->expects($this->exactly(1))->method('withStatus')->willReturn(null);
        $srh = new SingleResponseHandler($responseMock);
        $this->assertSame($responseMock, $srh->getResponseHandler());
        $this->assertNull($srh->query(function () {
            return function (ResponseInterface $response) {
                return $response->withStatus(201);
            };
        }));
        $this->assertSame($responseMock, $srh->getResponseHandler());
    }

    public function testThatCorrectBuilderIsPassed()
    {
        $response = $this->getMock(ResponseInterface::class);
        $builder = new QueryBuilder();
        $srh = new SingleResponseHandler($response);
        $queryComposer = function (QueryBuilder $b) use ($builder) {
            $this->assertSame($builder, $b);
            return function () {
                return '';
            };
        };
        $srh->query($queryComposer, $builder);
        $queryComposer = function (QueryBuilder $b) use ($builder) {
            $this->assertNotSame($builder, $b);
            return function () {
                return '';
            };
        };
        $srh->query($queryComposer);
    }
}

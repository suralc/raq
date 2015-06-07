<?php

namespace Raq\tests;


use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Raq\MultiResponseHandler;
use Raq\SingleResponseHandler;

class FetchTest extends RaqTest
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchAllInvalidArgumentException()
    {
        \Raq\fetchAll([12]);
    }

    public function testFetchAll()
    {
        $data = [
            'foo-bar',
            $this->getMock(RequestInterface::class),
            $this->getMock(RequestInterface::class),
            'example.org'
        ];
        $promiseMock = $this->getMock(PromiseInterface::class);
        $clientMock = $this->getMockBuilder(Client::class)->setMethods(['getAsync', 'sendAsync'])->getMock();
        $clientMock->expects($this->exactly(2))
            ->method('getAsync')
            ->withConsecutive(
                [$this->matches('foo-bar'), $this->isEmpty()],
                [$this->matches('example.org'), $this->isEmpty()]
            )
            ->willReturn($promiseMock);
        $clientMock->expects($this->exactly(2))
            ->method('sendAsync')
            ->withConsecutive(
                [$this->identicalTo($data[1])],
                [$this->identicalTo($data[2])]
            )
            ->willReturn($promiseMock);
        $multiHandler = \Raq\fetchAll($data, $clientMock);
        $this->assertInstanceOf(MultiResponseHandler::class, $multiHandler);
        foreach ($multiHandler->getResponseHandlers() as $handler) {
            $this->assertInstanceOf(SingleResponseHandler::class, $handler);
            $this->assertInstanceOf(PromiseInterface::class, $handler->getResponseHandler());
        }
    }

    // region single fetch
    public function testFetchWithString()
    {
        $promiseMock = $this->getMock(PromiseInterface::class);
        $clientMock = $this->getMockBuilder(Client::class)->setMethods(['getAsync'])->getMock();
        $clientMock->expects($this->once())->method('getAsync')->with($this->identicalTo('url'),
            [])->willReturn($promiseMock);
        $handler = \Raq\fetch('url', $clientMock);
        $this->assertInstanceOf(SingleResponseHandler::class, $handler);
        $this->assertSame($promiseMock, $handler->getResponseHandler());
    }

    public function testFetchWithRequest()
    {
        $promiseMock = $this->getMock(PromiseInterface::class);
        $requestMock = $this->getMock(RequestInterface::class);
        $clientMock = $this->getMockBuilder(Client::class)->setMethods(['sendAsync'])->getMock();
        $clientMock->expects($this->once())->method('sendAsync')->with($this->identicalTo($requestMock),
            [])->willReturn($promiseMock);
        $handler = \Raq\fetch($requestMock, $clientMock);
        $this->assertInstanceOf(SingleResponseHandler::class, $handler);
        $this->assertSame($promiseMock, $handler->getResponseHandler());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchInvalidArgumentException()
    {
        \Raq\fetch(['not a string'], new Client());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchFromStringException()
    {
        \Raq\fetchFromString(['not a string'], new Client());
    }

    public function testFetchFromString()
    {
        $promiseMock = $this->getMock(PromiseInterface::class);
        $clientMock = $this->getMockBuilder(Client::class)->setMethods(['getAsync'])->getMock();
        $target = 'http://suralc.github.io/raq';
        $clientMock->expects($this->once())->method('getAsync')->with($this->matches($target),
            $this->isEmpty())->willReturn($promiseMock);
        $wrappedSingularResponse = \Raq\fetchFromString($target, $clientMock, []);
        $this->assertInstanceOf(SingleResponseHandler::class, $wrappedSingularResponse);
        $this->assertSame($promiseMock, $wrappedSingularResponse->getResponseHandler());
    }

    public function testFetchFromRequest()
    {
        $requestMock = $this->getMock(RequestInterface::class);
        $promiseMock = $this->getMock(PromiseInterface::class);
        $clientMock = $this->getMockBuilder(Client::class)->setMethods(['sendAsync'])->getMock();
        $clientMock->expects($this->once())->method('sendAsync')->with($this->identicalTo($requestMock),
            $this->isEmpty())->willReturn($promiseMock);
        $wrappedSingularResponse = \Raq\fetchFromRequest($requestMock, $clientMock, []);
        $this->assertInstanceOf(SingleResponseHandler::class, $wrappedSingularResponse);
        $this->assertSame($promiseMock, $wrappedSingularResponse->getResponseHandler());
    }
    // endregion
    //region helpers
    public function testCreateDefaultClient()
    {
        $client = \Raq\_getDefaultClientInstance();
        $this->assertInstanceOf(Client::class, $client);
        $this->assertSame($client, \Raq\_getDefaultClientInstance());
    }

    public function testGen2ArrCompleteIt()
    {
        $generator = function () {
            for ($i = 0; $i < 4; $i++) {
                yield $i => 'my' . $i;
            }
        };
        $array = \Raq\gen2arr($generator());
        $this->assertSame([
            'my0',
            'my1',
            'my2',
            'my3',
        ], $array);
    }

    public function testGen2ArrIncomplete()
    {
        /**
         * @return \Generator
         */
        $generatorGenerator = function () {
            for ($i = 0; $i < 4; $i++) {
                yield 'my' . $i;
            }
        };
        $generator = $generatorGenerator();
        $generator->next();
        $generator->next();
        $this->assertSame([
            2 => 'my2',
            3 => 'my3',
        ], \Raq\gen2arr($generator));
    }
    //endregion
}

<?php

namespace Raq\tests\Query\Html;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Raq\Query\Html;
use Raq\tests\RaqTest;
use Symfony\Component\DomCrawler\Crawler;

class SelectTest extends RaqTest
{
    /**
     * @expectedException \Raq\Query\Exception\QueryHandlerException
     * @expectedExceptionMessage No content type header could be found in the response and the
     */
    public function testNoContentTypeException()
    {
        $query = new Html();
        $processor = $query->select('a > b', null, ['ignoreMissingContentTypeHeader' => false]);
        $response = $this->getMock(ResponseInterface::class);
        $response->expects($this->once())->method('hasHeader')->willReturn(false);
        $processor($response);
    }

    /**
     * @expectedException \Raq\Query\Exception\QueryHandlerException
     * @expectedExceptionMessage The given content type application/json does not comply with the required validation rules
     */
    public function testWrongContentTypeException()
    {
        $query = new Html();
        $processor = $query->select('a > b', null, ['validateContentType' => true]);
        $response = $this->getMock(ResponseInterface::class);
        $response->expects($this->once())->method('hasHeader')->with('Content-Type')->willReturn(true);
        $response->expects($this->exactly(2))->method('getHeader')->with('Content-Type')->willReturn(['application/json']);
        $processor($response);
    }

    /**
     * @expectedException \Raq\Query\Exception\QueryHandlerException
     */
    public function testThatEmptyContentTypeTriggersException()
    {
        $query = new Html();
        $response = $this->getMock(ResponseInterface::class);
        $response->expects($this->once())->method('hasHeader')->with('Content-Type')->willReturn(true);
        $response->expects($this->exactly(2))->method('getHeader')->with('Content-Type')->willReturn([]);
        $processor = $query->select('a > b');
        $processor($response);
    }

    /**
     * @dataProvider optionDataProvider
     */
    public function testThatOptionsAreCorrectlyMerged($options)
    {
        $expectedOptions = $options;
        $htmlQuery = new Html();
        $processor = function (Crawler $c, ResponseInterface $r, array $options = []) use ($expectedOptions) {
            $this->assertArraySubset($expectedOptions, $options);
            return null;
        };
        $query = $htmlQuery->select('div > p', $processor, $options);
        $query($this->getResponseMock());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The callback given to the customProcessor
     */
    public function testCustomProcessorInvalidReturnValueException()
    {
        $htmlQuery = new Html();
        $processor = function () {
            return 'not a crawler or null';
        };
        $query = $htmlQuery->select('div > p', $processor);
        $query($this->getResponseMock());
    }

    public function testRawNodesAreReturned()
    {
        $htmlQuery = new Html();
        $query = $htmlQuery->select('div > p', null, ['returnRawNodes' => true]);
        /** @var Crawler $result */
        $result = $query($this->getResponseMock());
        $this->assertInstanceOf(Crawler::class, $result);
        $this->assertSame('This is a content paragraph', $result->text());
    }

    public function testIndexByNodeNameRetrieval()
    {
        $htmlQuery = new Html();
        $query = $htmlQuery->select('div > ol > li', null, ['indexByNodeName' => true]);
        $results = $query($this->getResponseMock());
        $expected = ['li' => 'second'];
        $this->assertSame($expected, $results);
    }

    public function testIndexByMatchNumber()
    {
        $htmlQuery = new Html();
        $query = $htmlQuery->select('div > ol > li', null, ['indexByNodeName' => false]);
        $results = $query($this->getResponseMock());
        $expected = [['name' => 'li', 'value' => 'First'], ['name' => 'li', 'value' => 'second']];
        $this->assertSame($expected, $results);
    }

    private function getResponseMock()
    {
        $streamInterfaceMock = $this->getMock(StreamInterface::class);
        $streamInterfaceMock->expects($this->once())->method('getContents')->willReturn(<<<'HTML'
<html>
<body>
<h1>Hello World</h1>

<div class="foo">
    <div id="content">
        <p>This is a content paragraph</p>
    </div>
    <div id="content2">
        <ol>
            <li>First</li>
            <li>second</li>
        </ol>
    </div>
</div>
</body>
</html>
HTML
        );
        $responseMock = $this->getMock(ResponseInterface::class);
        $responseMock->expects($this->once())->method('getBody')->willReturn($streamInterfaceMock);
        $responseMock->expects($this->any())->method('hasHeader')->with('Content-Type')->willReturn(true);
        $responseMock->expects($this->any())->method('getHeader')->with('Content-Type')->willReturn(['text/html;charset=utf-8']);
        return $responseMock;
    }

    public function optionDataProvider()
    {
        return [
            // set 0
            [
                [
                    'validateContentType' => true,
                    'returnRawNodes' => false,
                    'indexByNodeName' => false,
                ]
            ],
            // set 1
            [
                [

                    'validateContentType' => false,
                    'returnRawNodes' => true,
                    'indexByNodeName' => true,
                ]
            ]
        ];
    }
}

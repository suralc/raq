<?php
/**
 * Html.php
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

namespace Raq\Query;


use Psr\Http\Message\ResponseInterface;
use Raq\Query\Exception\QueryHandlerException;
use Symfony\Component\DomCrawler\Crawler;

class Html extends Xml
{
    /**
     * @var array
     */
    protected $validContentTypes = [
        'text/html',
        'application/xhtml+xml'
    ];

    /**
     * @var array
     */
    protected $defaultOptions = [
        'validateContentType' => true,
        'ignoreMissingContentTypeHeader' => false,
        'returnRawNodes' => false,
        'indexByNodeName' => false,
    ];

    /**
     * @param $selector
     * @param callable $customProcessor
     * @param array $options
     * @return \Closure
     */
    public function select($selector, callable $customProcessor = null, array $options = [])
    {
        $composedOptions = array_replace($this->defaultOptions,
            ['selector' => $selector, 'customProcessor' => $customProcessor], $options);
        return function (ResponseInterface $r) use ($composedOptions) {
            if ($composedOptions['validateContentType']) {
                if (!$r->hasHeader('Content-Type') && !$composedOptions['ignoreMissingContentTypeHeader']) {
                    throw (new QueryHandlerException('No content type header could be found in the response and the `ignoreMissingContentTypeHeader`option was deactivated'))
                        ->setResponse($r)->setOptions($composedOptions);
                }
                if (!$this->validateContentType($r->getHeader('Content-Type'))) {
                    throw (new QueryHandlerException('The given content type ' . implode(',' ,$r->getHeader('Content-Type')) . ' does not comply with the required validation rules.'))
                        ->setResponse($r)->setOptions($composedOptions);
                }
            }
            $crawler = new Crawler($r->getBody()->getContents());
            $resultNodes = $crawler->filter($composedOptions['selector']);
            if ($composedOptions['customProcessor'] !== null) {
                $resultNodes = $composedOptions['customProcessor']($resultNodes, $r, $composedOptions);
                if ($resultNodes === null) {
                    return [];
                }
                if (!($resultNodes instanceof Crawler)) {
                    throw new \RuntimeException('The callback given to the customProcessor option should return a Crawler instance or null.');
                }
            }
            if ($composedOptions['returnRawNodes']) {
                return $resultNodes;
            }
            $results = [];
            /** @var \DOMElement $domElement */
            foreach ($resultNodes as $domElement) {
                if ($composedOptions['indexByNodeName']) {
                    $results[ $domElement->nodeName ] = $domElement->nodeValue;
                } else {
                    $results[] = ['name' => $domElement->nodeName, 'value' => $domElement->nodeValue];
                }
            }

            return $results;
        };
    }
}

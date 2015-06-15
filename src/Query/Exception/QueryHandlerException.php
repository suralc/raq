<?php
/**
 * QueryHandlerException.php
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

namespace Raq\Query\Exception;


use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

class QueryHandlerException extends \Exception
{
    /**
     * @var ResponseInterface
     */
    private $response;
    private $options;

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface|PromiseInterface $r
     * @return $this
     */
    public function setResponse($r)
    {
        if (!($r instanceof ResponseInterface || $r instanceof PromiseInterface)) {
            throw new \InvalidArgumentException('Only instances of the PSR-7 ResponseInterface or the Guzzle promise interface are allowed as parameters');
        }
        $this->response = $r;

        return $this;
    }
}

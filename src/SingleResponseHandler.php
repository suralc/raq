<?php
/**
 * SingleResponseHandler.php
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

namespace Raq;


use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

class SingleResponseHandler
{
    /**
     * @var ResponseInterface|PromiseInterface
     */
    private $response;
    private $unresolved = false;

    public function __construct($response)
    {
        if ($response instanceof ResponseInterface) {
            $this->response = $response;
        } elseif ($response instanceof PromiseInterface) {
            $this->unresolved = true;
            $this->response = $response;
        } else {
            throw new \InvalidArgumentException(sprintf('%s is not a supported type. ' .
                'Only implementations of guzzles PromiseInterface or the PSR-7 ResponseInterface are accepted',
                is_object($response) ? get_class($response) : gettype($response)));
        }
    }

    /**
     * @param callable $queryComposer
     * @param \Raq\QueryBuilder $builder
     * @return mixed
     */
    public function query(callable $queryComposer, QueryBuilder $builder = null)
    {
        if ($this->unresolved) {
            $this->response = $this->response->wait(true);
        }

        if ($builder === null) {
            $builder = new QueryBuilder();
        }

        $query = $queryComposer($builder, $this);
        if (!is_callable($query)) {
            throw new \RuntimeException('The value returned by the query composer callback has to be callable');
        }
        return $query($this->response);
    }

    public function getResponseHandler()
    {
        return $this->response;
    }
}

<?php
/**
 * Json.php
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
use Rs\Json\Pointer;

class Json extends BaseQuery
{
    public function path($path, array $options = [])
    {
        return function (ResponseInterface $r) use ($path, $options) {
            try {
                $pointer = new Pointer($r->getBody()->getContents());
                return $pointer->get($path);
            } catch (Pointer\InvalidJsonException $je) {
                throw (new QueryHandlerException('', $je->getCode(), $je))->setResponse($r)->setOptions($options);
            } catch (Pointer\NonexistentValueReferencedException $ex) {
                return [];
            }
        };
    }
}

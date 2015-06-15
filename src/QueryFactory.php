<?php
/**
 * QueryFactory.php
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


use Raq\Query\Html;
use Raq\Query\Json;
use Raq\Query\Xml;

class QueryFactory
{
    /**
     * @return \Raq\Query\Xml
     */
    public function xmlQuery()
    {
        return new Xml;
    }

    /**
     * @return \Raq\Query\Html
     */
    public function htmlQuery()
    {
        return new Html;
    }

    /**
     * @return \Raq\Query\Json
     */
    public function jsonQuery()
    {
        return new Json;
    }
}

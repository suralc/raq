<?php
/**
 * BaseQuery.php
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


abstract class BaseQuery
{
    /**
     * @var array
     */
    protected $validContentTypes = [];

    /**
     * @param array $actualTypes
     * @return bool
     */
    protected function validateContentType(array $actualTypes)
    {
        if (empty($actualTypes)) {
            return false;
        }
        foreach ($actualTypes as $type) {
            if (in_array($this->removeCharsetFromContentTypeLine($type), $this->validContentTypes)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $line
     * @return string
     */
    private function removeCharsetFromContentTypeLine($line)
    {
        return trim(explode(';', $line)[0]);
    }
}

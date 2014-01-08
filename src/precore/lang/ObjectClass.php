<?php
/*
 * Copyright (c) 2012 Szurovecz János
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace precore\lang;

use ReflectionClass;
use RuntimeException;

/**
 * Extends ReflectionClass with some new features.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class ObjectClass extends ReflectionClass
{
    protected function getSlashedFileName()
    {
        $classFileName = $this->getFileName();
        if ($classFileName === false) {
            throw new RuntimeException('This method cannot be called for built-in classes!');
        }
        return str_replace('\\', '/', $classFileName);
    }

    protected function getSlashedName()
    {
        return str_replace('\\', '/', $this->getName());
    }

    /**
     * Whether the class name is PSR-0 compatible or not.
     *
     * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
     * @return boolean
     */
    public function isPsr0Compatible()
    {
        return preg_match("#{$this->getSlashedName()}.php$#", $this->getSlashedFileName()) !== 0;
    }

    /**
     * Get a file name depending on current class.
     *
     * Suppose the class is \foo\Bar which located in src/foo/Bar.php
     * Absolute path: /resources/res1 must be located in src/resources/res1
     * Relative path: resources/res2 must be located in src/foo/resources/res2
     *
     * @param string $resource
     * @return string File path of $resource or null if not exists
     * @see java.lang.Class
     * @throws RuntimeException Class is built-int
     * @throws RuntimeException Class is not PSR-0 compatible
     */
    public function getResource($resource)
    {
        if (!$this->isPsr0Compatible()) {
            throw new RuntimeException("Class '{$this->getName()}' must be PSR-0 compatible!");
        }
        $slashedFileName = $this->getSlashedFileName();
        $filePath = $resource[0] == '/'
            ? preg_replace("#{$this->getSlashedName()}\.php$#", '', $slashedFileName) . '/' . $resource
            : dirname($slashedFileName) . '/' . $resource;
        return is_file($filePath) ? $filePath : null;
    }

    /**
     * Check whether $object can be cast to $this->getName().
     *
     * @param object $object
     * @return object $object itself
     * @throws ClassCastException
     */
    public function cast($object)
    {
        if (!$this->isInstance($object)) {
            $objectClass = get_class($object);
            throw new ClassCastException("'{$objectClass}' cannot be cast to '{$this->getName()}'");
        }
        return $object;
    }

    /**
     * Creates a new class instance without invoking the constructor.
     *
     * @link http://php.net/manual/en/reflectionclass.newinstancewithoutconstructor.php
     * @return object
     */
    public function newInstanceWithoutConstructor()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return parent::newInstanceWithoutConstructor();
        }
        return unserialize(sprintf('O:%u:"%s":0:{}', strlen($this->getName()), $this->getName()));
    }
}

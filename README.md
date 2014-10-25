precore
=======

Precore is a common library which based on ideas coming from the Java world.

1. Object and ObjectClass
2. Enum
3. Error handling
4. Object utilities
5. Preconditions

1. Object and ObjectClass
-------------------------

In Java, all objects are implicitly extend the `Object` class. It is really convenient since some common methods are defined for all objects.
This behavior is missing from PHP, therefore precore provides `precore\lang\Object`. Sometimes it is required to be able to enforce in an interface, that
the implementation must be an `Object`, thus `precore\lang\ObjectInterface` can be used for that.

* `Object::objectClass()` static function, returns the `ObjectClass` for the particular class
* `Object::getObjectClass()` the same, but non-static method
* `Object::className()` the same as `AnyClass.class` in PHP 5.5
* `Object::getClassName()` returns the class name of the actual object
* `Object::hashCode()` returns `spl_object_hash($this)`
* `Object::equals(ObjectInterface $object)` returns true if the actual object and the argument are equal
* `Object::toString()` and `Object::__toString()`: both return the string representation of the object (the default format is: `{className}@{hashCode}`)
* `Object::getLogger()` retrieves an [lf4php](https://github.com/szjani/lf4php) logger object for the actual class

The `ObjectClass` class extends `ReflectionClass` and gives some more features. These objects are cached if we get them through `ObjectClass::forName($className)` function.
`ObjectClass` also supports resources (almost as in Java) in case of classes follow PSR-0.

2. Enum
-------

In PHP unfortunately there is no Enum. `precore\lang\Enum` is an abstract class which tries to solve this lack of feature. Our enum class must extends this class
and all possible values must be defined as public static variables which will be automatically found and initialized by precore.

```php
final class Color extends Enum
{
    public static $RED;
    public static $GREED;
    public static $BLUE;
}

echo Color::$RED->name() . PHP_EOL;
foreach (Color::values() as $color) {
    echo $color->name() . PHP_EOL;
}
```

The following code produces the following output:

```
RED
RED
GREED
BLUE
```

3. Error handling
-----------------

In several cases, PHP trigger errors instead of exceptions. Precore can automatically handle these errors and convert them into exception. The only thing should be done:

```php
ErrorHandler::register();
```

After that, we will be able to catch specific exceptions. For the available exceptions see `precore\util\error` namespace.

4. Object utilities
-------------------

### ToStringHelper

Creating a string representation of an object is important, but not an exciting thing and we always need to use almost the same boilerplate code. With `precore\util\ToStringHelper`
it can be simplified. A new instance can be created through `Objects::toStringHelper()` as well.

```php
namespace HelloWorld;

class Foo {
    private $bar = 'foobar';
    
    public function __toString()
    {
        return Objects::toStringHelper($this)
            ->add('bar', $this->bar)
            ->toString();
    }
}

echo (string) new Foo();
// prints 'HelloWorld\Foo{bar=foobar}'
```

It supports arrays and `DateTime` as well. If the `ErrorHandler` is registered, `spl_object_hash()` will be used for those objects which cannot be cast to string.

### Equality

Two variables equality can be checked with `Objects::equal($a, $b)`. It supports null, primitive types, objects, and `ObjectInterface` implementations as well as it is expected.

### Comparing objects

Objects can be compared if they implement `precore\lang\Comparable` or if proper `precore\util\Comparator` is used which can compare the given objects. In several cases, comparing two objects
depend on their member variables so they need to be compared as well. It also can be simplified with `precore\util\ComparisonChain`.

```php
$strcmp = function ($left, $right) {
    return strcmp($left, $right);
};
$result = ComparisonChain::start()
    ->withClosure('aaa', 'aaa', $strcmp)
    ->withClosure('abc', 'bcd', $strcmp)
    ->withClosure('abc', 'bcd', $strcmp)
    ->result();
// $result < 0, because abc < bcd
```

In the previous example, the third comparison will not be executed, since it is unnecessary.

5. Preconditions
----------------

It is a very lightweight assertion tool, which supports argument, object state, null value, and array index checking. Customized messages can be passed with arguments similar to `printf()`.
 
```php
/**
 * @param $number
 * @throws \InvalidArgumentException if $number is 0
 */
function divide($number) {
    Preconditions::checkArgument($number !== 0, 'Division by zero');
    // ...
}
```
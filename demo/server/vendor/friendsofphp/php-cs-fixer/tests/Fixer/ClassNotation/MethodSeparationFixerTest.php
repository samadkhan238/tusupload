<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tests\Fixer\ClassNotation;

use PhpCsFixer\Tests\Test\AbstractFixerTestCase;
use PhpCsFixer\WhitespacesFixerConfig;

/**
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\ClassNotation\MethodSeparationFixer
 */
final class MethodSeparationFixerTest extends AbstractFixerTestCase
{
    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideFixClassesCases
     */
    public function testFixClasses($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function provideFixClassesCases()
    {
        $cases = [];
        $cases[] = ['<?php
class SomeClass1
{
    // This comment
    // is multiline.
    public function echoA()
    {
        echo "a";
    }
}
'];
        $cases[] = [
            '<?php
class SomeClass2
{
    // This comment
    /* is multiline. */
public function echoA()
    {
        echo "a";
    }
}
            ',
            '<?php
class SomeClass2
{
    // This comment
    /* is multiline. */public function echoA()
    {
        echo "a";
    }
}
            ',
        ];
        $cases[] = [
            '<?php
class SomeClass3
{
    // This comment
    // is multiline.

    public function echoA()
    {
        echo "a";
    }
}
', ];
        $cases[] = [
            '<?php
class SomeClass1
{
    private $a; //

    public function methodA()
    {
    }

    private $b;

    //
    public function methodB()
    {
    }

    // C
    public function methodC()
    {
    }

    // D

    public function methodD()
    {
    }

    /* E */

    public function methodE()
    {
    }

    /* F */
    public function methodF()
    {
    }
}
',
            '<?php
class SomeClass1
{
    private $a; //
    public function methodA()
    {
    }

    private $b;
    //
    public function methodB()
    {
    }
    // C
    public function methodC()
    {
    }

    // D

    public function methodD()
    {
    }

    /* E */

    public function methodE()
    {
    }

    /* F */
    public function methodF()
    {
    }
}
', ];
        $cases[] = ['<?php
class SomeClass
{
    // comment
    public function echoA()
    {
        echo "a";
    }
}
'];
        $cases[] = ['<?php
class SomeClass
{
    // This comment
    // is multiline.
    public function echoA()
    {
        echo "a";
    }
}
'];
        $cases[] = [
            '<?php
class SomeClass
{
    // comment

    public function echoA()
    {
        echo "a";
    }
}
',
            '<?php
class SomeClass
{
    // comment


    public function echoA()
    {
        echo "a";
    }
}
',
        ];
        $cases[] = [
            '<?php
class SomeClass
{
    /* comment */
public function echoB()
    {
        echo "a";
    }
}
',
            '<?php
class SomeClass
{
    /* comment */public function echoB()
    {
        echo "a";
    }
}
',
        ];
        $cases[] = [
            '<?php
class SomeClass
{
    /* comment */
 public function echoC()
    {
        echo "a";
    }
}
',
            '<?php
class SomeClass
{
    /* comment */ public function echoC()
    {
        echo "a";
    }
}
',
        ];
        $cases[] = [
            '<?php
abstract class MethodTest2
{
    public function method045()
    {
        $files = null;
            if (!empty($files)) {
            $this->filter(
                function (\SplFileInfo $file) use ($files) {
                    return !in_array($file->getRelativePathname(), $files, true);
                }
            );
        }
    }

     private $a;

     public static function method145()
     {
     }

       abstract protected function method245();

    // comment

    final private function method345()
    {
    }
}
function test1(){ echo 1;}
function test2(){ echo 2;}',
            '<?php
abstract class MethodTest2
{
    public function method045()
    {
        $files = null;
            if (!empty($files)) {
            $this->filter(
                function (\SplFileInfo $file) use ($files) {
                    return !in_array($file->getRelativePathname(), $files, true);
                }
            );
        }
    }
     private $a;

     public static function method145()
     {
     }
       abstract protected function method245();
    // comment

    final private function method345()
    {
    }
}
function test1(){ echo 1;}
function test2(){ echo 2;}',
        ];

        $cases[] = [
            '<?php

/*
 * This file is part of the PHP CS utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Linter;

/**
 * Dummy linter. No linting is performed. No error is raised.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class NullLinter implements LinterInterface
{
    /**
     * {@inheritdoc}
     */
    public function lintFile($path)
    {
        unset($path);
    }

    /**
     * {@inheritdoc}
     */
    public function lintSource($source)
    {
        unset($source);
    }
}
',
        ];

        // do not touch anonymous functions (since PHP doesn't allow
        // for class attributes being functions :(, we only have to test
        // those used within methods)
        $cases[] = [
            '<?php
class MethodTestAnonymous
{
    public function method444a()
    {
        $text = "hello";
        $example = function ($arg) use ($message) {
            var_dump($arg . " " . $message);
        };
        $example($text);
        $example = function($arg) use ($message) {
            var_dump($arg . " " . $message);
        };
        $example = function /*test*/ ($arg) use ($message) {
            var_dump($arg . " " . $message);
        };
    }
}',
        ];

        $cases[] = [
            '<?php
class MethodTest1
{
    private $c; //

    public function method444a()
    {
    }

    /**
     *
     */
    public function method444b()
    {
    }

    //

    public function method444c()
    {
    }

    private $a;

    public function method444d()
    {
    }

    private $b;

    //
    public function method444e()
    {
    }

    public function method444f()
    {
    }

    private $d; //

    public function method444f1()
    {
    }

    /**/
    public function method444g()
    {
    }
}',
            '<?php
class MethodTest1
{
    private $c; //
    public function method444a()
    {
    }
    /**
     *
     */
    public function method444b()
    {
    }

    //


    public function method444c()
    {
    }

    private $a;
    public function method444d()
    {
    }
    private $b;
    //
    public function method444e()
    {
    }

    public function method444f()
    {
    }

    private $d; //
    public function method444f1()
    {
    }

    /**/
    public function method444g()
    {
    }
}',
        ];

        // spaces between methods
        $cases[] = [
            '<?php
abstract class MethodTest3
{
    public function method021()
    {
    }

    public static function method121()
    {
    }

    abstract protected function method221();	'.'

    final private function method321a()
    {
    }
}',
            '<?php
abstract class MethodTest3
{
    public function method021()
    {
    }

    public static function method121()
    {
    }


    abstract protected function method221();



	'.'




    final private function method321a()
    {
    }
}', ];
        // don't change correct code
        $cases[] = [
            '<?php
class SmallHelperException extends \Exception
{
    public function getId111()
    {
        return 1;
    }

    public function getMessage111()
    {
        return \'message\';
    }
}

class MethodTest123124124
{
    public function method111a(){}

    public function method211a(){}
}',
        ];

        // do not touch function out of class scope
        $cases[] = [
            '<?php
function test0() {

}
class MethodTest4
{
    public function method122b()
    {
    }

    public function method222b()
    {
    }
}
function test() {

}
function test2() {

}
',
        ];

        return $cases;
    }

    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideFixTraitsCases
     */
    public function testFixTraits($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function provideFixTraitsCases()
    {
        $cases = [];

        // do not touch well formatted traits
        $cases[] = [
            '<?php
trait OkTrait
{
    function getReturnTypeOk()
    {
    }

    /**
     *
     */
    function getReturnDescriptionOk()
    {
    }
}',
        ];

        $cases[] = [
            '<?php
trait ezcReflectionReturnInfo {
    public $x = 1;

    protected function getA(){echo 1;}

function getB(){echo 2;}

    protected function getC(){echo 3;}

/** Description */
function getD(){echo 4;}

    protected function getE(){echo 3;}

private $a;

function getF(){echo 4;}
}',
            '<?php
trait ezcReflectionReturnInfo {
    public $x = 1;
    protected function getA(){echo 1;}function getB(){echo 2;}
    protected function getC(){echo 3;}/** Description */function getD(){echo 4;}
    protected function getE(){echo 3;}private $a;function getF(){echo 4;}
}',
        ];

        $cases[] = [
            '<?php
trait SomeReturnInfo {
    function getReturnType()
    {
    }

    function getReturnDescription()
    {
    }

 function getReturnDescription2()
    {
    }

    abstract public function getWorld();
}',
            '<?php
trait SomeReturnInfo {
    function getReturnType()
    {
    }
    function getReturnDescription()
    {
    } function getReturnDescription2()
    {
    }

    abstract public function getWorld();
}',
        ];

        return $cases;
    }

    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideFixInterfaceCases
     */
    public function testFixInterface($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function provideFixInterfaceCases()
    {
        $cases = [];
        $cases[] = [
            '<?php
interface TestInterface
{
    public function testInterfaceMethod4();

    public function testInterfaceMethod5();

    /**
     * {@link}
     */           '.'
    public function testInterfaceMethod6();

    public function testInterfaceMethod7();

 public function testInterfaceMethod8();
}',
            '<?php
interface TestInterface
{    public function testInterfaceMethod4();
    public function testInterfaceMethod5();


    /**
     * {@link}
     */           '.'
    public function testInterfaceMethod6();


    public function testInterfaceMethod7(); public function testInterfaceMethod8();
}',
        ];

        // do not touch well formatted interfaces
        $cases[] = [
            '<?php
interface TestInterfaceOK
{
    public function testMethod1();

    public function testMethod2();
}',
        ];

        // method after trait use
        $cases[] = [
            '<?php
trait ezcReflectionReturnInfo {
    function getReturnDescription() {}
}
class ezcReflectionMethod extends ReflectionMethod {
    use ezcReflectionReturnInfo;

function afterUseTrait(){}

function afterUseTrait2(){}
}',
            '<?php
trait ezcReflectionReturnInfo {
    function getReturnDescription() {}
}
class ezcReflectionMethod extends ReflectionMethod {
    use ezcReflectionReturnInfo;function afterUseTrait(){}function afterUseTrait2(){}



}',
        ];

        return $cases;
    }

    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideMessyWhitespacesCases
     */
    public function testMessyWhitespaces($expected, $input = null)
    {
        $this->fixer->setWhitespacesConfig(new WhitespacesFixerConfig("\t", "\r\n"));

        $this->doTest($expected, $input);
    }

    public function provideMessyWhitespacesCases()
    {
        return [
            [
                "<?php\r\nclass SomeClass\r\n{\r\n    // comment\n\n    public function echoA()\r\n    {\r\n        echo 'a';\r\n    }\r\n}\r\n",
                "<?php\r\nclass SomeClass\r\n{\r\n    // comment\n\n\n    public function echoA()\r\n    {\r\n        echo 'a';\r\n    }\r\n}\r\n",
            ],
            [
                "<?php\r\nclass SomeClass\r\n{\r\n    // comment\r\n\r\n    public function echoA()\r\n    {\r\n        echo 'a';\r\n    }\r\n}\r\n",
                "<?php\r\nclass SomeClass\r\n{\r\n    // comment\r\n\r\n\r\n    public function echoA()\r\n    {\r\n        echo 'a';\r\n    }\r\n}\r\n",
            ],
        ];
    }
}

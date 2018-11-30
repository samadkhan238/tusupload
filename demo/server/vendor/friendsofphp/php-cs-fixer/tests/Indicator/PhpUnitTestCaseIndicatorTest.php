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

namespace PhpCsFixer\Tests\Indicator;

use PhpCsFixer\Indicator\PhpUnitTestCaseIndicator;
use PhpCsFixer\Tests\TestCase;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author SpacePossum
 *
 * @internal
 * @covers \PhpCsFixer\Indicator\PhpUnitTestCaseIndicator
 */
final class PhpUnitTestCaseIndicatorTest extends TestCase
{
    /**
     * @var PhpUnitTestCaseIndicator
     */
    private $indicator;

    protected function setUp()
    {
        $this->indicator = new PhpUnitTestCaseIndicator();
    }

    /**
     * @param bool   $expected
     * @param Tokens $tokens
     * @param int    $index
     *
     * @dataProvider provideIsPhpUnitClassCases
     */
    public function testIsPhpUnitClass($expected, Tokens $tokens, $index)
    {
        $this->assertSame($expected, $this->indicator->isPhpUnitClass($tokens, $index));
    }

    public function provideIsPhpUnitClassCases()
    {
        return [
            'Test class' => [
                true,
                Tokens::fromCode('<?php final class MyTest {}'),
                3,
            ],
            'TestCase class' => [
                true,
                Tokens::fromCode('<?php final class SomeTestCase {}'),
                3,
            ],
            'Extends Test' => [
                true,
                Tokens::fromCode('<?php final class foo extends Test {}'),
                3,
            ],
            'Extends TestCase' => [
                true,
                Tokens::fromCode('<?php final class bar extends TestCase {}'),
                3,
            ],
            'Implements AbstractFixerTest' => [
                true,
                Tokens::fromCode('<?php
class A extends Foo implements PhpCsFixer\Tests\Fixtures\Test\AbstractFixerTest
{
}
'),
                1,
            ],
            'Extends TestCase implements Foo' => [
                true,
                Tokens::fromCode('<?php
class A extends TestCase implements Foo
{
}
'),
                1,
            ],
            'Implements TestInterface' => [
                true,
                Tokens::fromCode('<?php
class Foo implements SomeTestInterface
{
}
'),
                1,
            ],
            'Implements TestInterface, SomethingElse' => [
                true,
                Tokens::fromCode('<?php
class Foo implements TestInterface, SomethingElse
{
}
'),
                1,
            ],
            [
                false,
                Tokens::fromCode('<?php final class MyClass {}'),
                3,
            ],
        ];
    }

    public function testThrowsExceptionIfNotClass()
    {
        $tokens = Tokens::fromCode('<?php echo 1;');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageRegExp('/^No T_CLASS at given index 1, got T_ECHO\.$/');

        $this->indicator->isPhpUnitClass($tokens, 1);
    }

    /**
     * @param array<[int, int]> $expectedIndexes
     * @param string            $code
     *
     * @dataProvider provideFindPhpUnitClassesCases
     */
    public function testFindPhpUnitClasses(array $expectedIndexes, $code)
    {
        $tokens = Tokens::fromCode($code);

        $classesFromTop = $this->indicator->findPhpUnitClasses($tokens, false);
        $classesFromTop = iterator_to_array($classesFromTop);
        $classesFromBottom = $this->indicator->findPhpUnitClasses($tokens);
        $classesFromBottom = iterator_to_array($classesFromBottom);

        $this->assertSame($expectedIndexes, $classesFromTop);
        $this->assertSame(array_reverse($classesFromBottom), $classesFromTop);
    }

    public function provideFindPhpUnitClassesCases()
    {
        return [
            'empty' => [
                [],
                '',
            ],
            'empty script' => [
                [],
                "<?php\n",
            ],
            'no test class' => [
                [],
                '<?php class Foo{}',
            ],
            'single test class' => [
                [
                    [6, 7],
                ],
                '<?php
                    class MyTest {}
                ',
            ],
            'two PHPUnit classes' => [
                [
                    [6, 7],
                    [13, 26],
                ],
                '<?php
                    class My1Test {}
                    class My2Test { public function A() {} }
                ',
            ],
            'mixed classes' => [
                [
                    [25, 38],
                    [63, 76],
                ],
                '<?php
                    class Foo1 { public function A() {} }
                    class My1Test { public function A() {} }
                    class Foo2 { public function A() {} }
                    class My2Test { public function A() {} }
                    class Foo3 { public function A() { return function (){}; } }
                ',
            ],
            'class with anonymous class inside' => [
                [],
                '<?php
                    class Foo
                    {
                        public function getClass()
                        {
                            return new class {};
                        }
                    }
                ',
            ],
        ];
    }
}

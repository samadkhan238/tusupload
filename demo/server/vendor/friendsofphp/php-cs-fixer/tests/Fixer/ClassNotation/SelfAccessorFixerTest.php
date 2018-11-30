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

/**
 * @author Gregor Harlan <gharlan@web.de>
 *
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer
 */
final class SelfAccessorFixerTest extends AbstractFixerTestCase
{
    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideFixCases
     */
    public function testFix($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function provideFixCases()
    {
        return [
            [
                '<?php class Foo { const BAR = self::BAZ; }',
                '<?php class Foo { const BAR = Foo::BAZ; }',
            ],
            [
                '<?php class Foo { private $bar = self::BAZ; }',
                '<?php class Foo { private $bar = fOO::BAZ; }', // case insensitive
            ],
            [
                '<?php class Foo { function bar($a = self::BAR) {} }',
                '<?php class Foo { function bar($a = Foo::BAR) {} }',
            ],
            [
                '<?php class Foo { function bar() { self::baz(); } }',
                '<?php class Foo { function bar() { Foo::baz(); } }',
            ],
            [
                '<?php class Foo { function bar() { self::class; } }',
                '<?php class Foo { function bar() { Foo::class; } }',
            ],
            [
                '<?php class Foo { function bar() { $x instanceof self; } }',
                '<?php class Foo { function bar() { $x instanceof Foo; } }',
            ],
            [
                '<?php class Foo { function bar() { new self(); } }',
                '<?php class Foo { function bar() { new Foo(); } }',
            ],
            [
                '<?php interface Foo { const BAR = self::BAZ; function bar($a = self::BAR); }',
                '<?php interface Foo { const BAR = Foo::BAZ; function bar($a = Foo::BAR); }',
            ],

            [
                '<?php class Foo { const Foo = 1; }',
            ],
            [
                '<?php class Foo { function foo() { } }',
            ],
            [
                '<?php class Foo { function bar() { new \Baz\Foo(); } }',
            ],
            [
                '<?php class Foo { function bar() { new Foo\Baz(); } }',
            ],
            [
                '<?php class Foo { function bar() { function ($a = self::BAZ) { new self(); }; } }',
                '<?php class Foo { function bar() { function ($a = Foo::BAZ) { new Foo(); }; } }',
            ],
            [
                // In trait "self" will reference the class it's used in, not the actual trait, so we can't replace "Foo" with "self" here
                '<?php trait Foo { function bar() { Foo::bar(); } } class Bar { use Foo; }',
            ],
            [
                '<?php class Foo { public function bar(self $foo, self $bar) { return new self(); } }',
                '<?php class Foo { public function bar(Foo $foo, Foo $bar) { return new Foo(); } }',
            ],
            [
                '<?php interface Foo { public function bar(self $foo, self $bar); }',
                '<?php interface Foo { public function bar(Foo $foo, Foo $bar); }',
            ],
        ];
    }

    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideFix70Cases
     * @requires PHP 7.0
     */
    public function testFix70($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function provideFix70Cases()
    {
        return [
            [
                '<?php class Foo { function bar() {
                    new class() { function baz() { new Foo(); } };
                } }',
            ],
            [
                '<?php class Foo { protected $foo; function bar() { return $this->foo::find(2); } }',
            ],
            [
                '<?php class Foo { public function bar(self $foo, self $bar): self { return new self(); } }',
                '<?php class Foo { public function bar(Foo $foo, Foo $bar): Foo { return new Foo(); } }',
            ],
            [
                '<?php interface Foo { public function bar(self $foo, self $bar): self; }',
                '<?php interface Foo { public function bar(Foo $foo, Foo $bar): Foo; }',
            ],
        ];
    }

    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideFix71Cases
     * @requires PHP 7.1
     */
    public function testFix71($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function provideFix71Cases()
    {
        return [
            [
                '<?php class Foo { public function bar(?self $foo, ?self $bar): ?self { return new self(); } }',
                '<?php class Foo { public function bar(?Foo $foo, ?Foo $bar): ?Foo { return new Foo(); } }',
            ],
            [
                "<?php interface Foo{ public function bar()\t/**/:?/**/self; }",
                "<?php interface Foo{ public function bar()\t/**/:?/**/Foo; }",
            ],
        ];
    }
}

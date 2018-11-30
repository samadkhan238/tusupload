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

namespace PhpCsFixer\Tests\Fixer\FunctionNotation;

use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 *
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\FunctionNotation\PhpdocToReturnTypeFixer
 */
final class PhpdocToReturnTypeFixerTest extends AbstractFixerTestCase
{
    /**
     * @param string      $expected
     * @param null|string $input
     * @param null|int    $versionSpecificFix
     * @param null|array  $config
     *
     * @dataProvider provideFixCases
     */
    public function testFix($expected, $input = null, $versionSpecificFix = null, $config = null)
    {
        if (
            (null !== $input && \PHP_VERSION_ID < 70000)
            || (null !== $versionSpecificFix && \PHP_VERSION_ID < $versionSpecificFix)
        ) {
            $expected = $input;
            $input = null;
        }
        if (null !== $config) {
            $this->fixer->configure($config);
        }

        $this->doTest($expected, $input);
    }

    public function provideFixCases()
    {
        return [
            'no phpdoc return' => [
                '<?php function my_foo() {}',
            ],
            'invalid return' => [
                '<?php /** @return */ function my_foo() {}',
            ],
            'invalid class 1' => [
                '<?php /** @return \9 */ function my_foo() {}',
            ],
            'invalid class 2' => [
                '<?php /** @return \\Foo\\\\Bar */ function my_foo() {}',
            ],
            'blacklisted class methods' => [
                '<?php

                    class Foo
                    {
                        /** @return Bar */
                        function __construct() {}
                        /** @return Bar */
                        function __destruct() {}
                        /** @return Bar */
                        function __clone() {}
                    }
                ',
            ],
            'multiple returns' => [
                '<?php

                    /**
                     * @return Bar
                     * @return Baz
                     */
                    function xyz() {}
                ',
            ],
            'non-root class' => [
                '<?php /** @return Bar */ function my_foo(): Bar {}',
                '<?php /** @return Bar */ function my_foo() {}',
            ],
            'non-root namespaced class' => [
                '<?php /** @return My\Bar */ function my_foo(): My\Bar {}',
                '<?php /** @return My\Bar */ function my_foo() {}',
            ],
            'root class' => [
                '<?php /** @return \My\Bar */ function my_foo(): \My\Bar {}',
                '<?php /** @return \My\Bar */ function my_foo() {}',
            ],
            'interface' => [
                '<?php interface Foo { /** @return Bar */ function my_foo(): Bar; }',
                '<?php interface Foo { /** @return Bar */ function my_foo(); }',
            ],
            'void return on ^7.1' => [
                '<?php /** @return void */ function my_foo(): void {}',
                '<?php /** @return void */ function my_foo() {}',
                70100,
            ],
            'invalid void return on ^7.1' => [
                '<?php /** @return null|void */ function my_foo() {}',
            ],
            'iterable return on ^7.1' => [
                '<?php /** @return iterable */ function my_foo(): iterable {}',
                '<?php /** @return iterable */ function my_foo() {}',
                70100,
            ],
            'object return on ^7.2' => [
                '<?php /** @return object */ function my_foo(): object {}',
                '<?php /** @return object */ function my_foo() {}',
                70200,
            ],
            'fix scalar types by default' => [
                '<?php /** @return int */ function my_foo(): int {}',
                '<?php /** @return int */ function my_foo() {}',
            ],
            'fix scalar types when configured' => [
                '<?php /** @return int */ function my_foo() {}',
                null,
                null,
                ['scalar_types' => false],
            ],
            'array native type' => [
                '<?php /** @return array */ function my_foo(): array {}',
                '<?php /** @return array */ function my_foo() {}',
            ],
            'callable type' => [
                '<?php /** @return callable */ function my_foo(): callable {}',
                '<?php /** @return callable */ function my_foo() {}',
            ],
            'self accessor' => [
                '<?php
                    class Foo {
                        /** @return self */ function my_foo(): self {}
                    }
                ',
                '<?php
                    class Foo {
                        /** @return self */ function my_foo() {}
                    }
                ',
            ],
            'report static as self' => [
                '<?php
                    class Foo {
                        /** @return static */ function my_foo(): self {}
                    }
                ',
                '<?php
                    class Foo {
                        /** @return static */ function my_foo() {}
                    }
                ',
            ],
            'skip resource special type' => [
                '<?php /** @return resource */ function my_foo() {}',
            ],
            'skip mixed special type' => [
                '<?php /** @return mixed */ function my_foo() {}',
            ],
            'null alone cannot be a return type' => [
                '<?php /** @return null */ function my_foo() {}',
            ],
            'skip mixed types' => [
                '<?php /** @return Foo|Bar */ function my_foo() {}',
            ],
            'nullable type' => [
                '<?php /** @return null|Bar */ function my_foo(): ?Bar {}',
                '<?php /** @return null|Bar */ function my_foo() {}',
                70100,
            ],
            'nullable type reverse order' => [
                '<?php /** @return Bar|null */ function my_foo(): ?Bar {}',
                '<?php /** @return Bar|null */ function my_foo() {}',
                70100,
            ],
            'nullable native type' => [
                '<?php /** @return null|array */ function my_foo(): ?array {}',
                '<?php /** @return null|array */ function my_foo() {}',
                70100,
            ],
            'skip mixed nullable types' => [
                '<?php /** @return null|Foo|Bar */ function my_foo() {}',
            ],
            'skip generics' => [
                '<?php /** @return array<int, bool> */ function my_foo() {}',
            ],
            'array of types' => [
                '<?php /** @return Foo[] */ function my_foo(): array {}',
                '<?php /** @return Foo[] */ function my_foo() {}',
            ],
            'skip array of array of types' => [
                '<?php /** @return Foo[][] */ function my_foo() {}',
            ],
            'nullable array of types' => [
                '<?php /** @return null|Foo[] */ function my_foo(): ?array {}',
                '<?php /** @return null|Foo[] */ function my_foo() {}',
                70100,
            ],
            'comments' => [
                '<?php
                    class A
                    {
                        // comment 0
                        /** @return Foo */ # comment 1
                        final/**/public/**/static/**/function/**/bar/**/(/**/$var/**/=/**/1/**/): Foo/**/{# comment 2
                        } // comment 3
                    }
                ',
                '<?php
                    class A
                    {
                        // comment 0
                        /** @return Foo */ # comment 1
                        final/**/public/**/static/**/function/**/bar/**/(/**/$var/**/=/**/1/**/)/**/{# comment 2
                        } // comment 3
                    }
                ',
            ],
        ];
    }
}

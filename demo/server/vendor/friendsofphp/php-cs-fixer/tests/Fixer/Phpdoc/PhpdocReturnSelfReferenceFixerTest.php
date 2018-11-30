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

namespace PhpCsFixer\Tests\Fixer\Phpdoc;

use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @author SpacePossum
 *
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\Phpdoc\PhpdocReturnSelfReferenceFixer
 */
final class PhpdocReturnSelfReferenceFixerTest extends AbstractFixerTestCase
{
    /**
     * @param string      $expected PHP code
     * @param null|string $input    PHP code
     *
     * @group legacy
     * @dataProvider provideDefaultConfigurationTestCases
     * @expectedDeprecation Passing NULL to set default configuration is deprecated and will not be supported in 3.0, use an empty array instead.
     */
    public function testLegacyFixWithDefaultConfiguration($expected, $input = null)
    {
        $this->fixer->configure(null);
        $this->doTest($expected, $input);
    }

    /**
     * @param string      $expected PHP code
     * @param null|string $input    PHP code
     *
     * @dataProvider provideDefaultConfigurationTestCases
     */
    public function testFixWithDefaultConfiguration($expected, $input = null)
    {
        $this->fixer->configure([]);
        $this->doTest($expected, $input);
    }

    public function provideDefaultConfigurationTestCases()
    {
        return [
            [
                '<?php interface A{/** @return    $this */public function test();}',
                '<?php interface A{/** @return    this */public function test();}',
            ],
            [
                '<?php interface B{/** @return self|int */function test();}',
                '<?php interface B{/** @return $SELF|int */function test();}',
            ],
            [
                '<?php class D {} /** @return {@this} */ require_once($a);echo 1;echo 1;echo 1;echo 1;echo 1;echo 1;echo 1;echo 1;',
            ],
            [
                '<?php /** @return this */ require_once($a);echo 1;echo 1;echo 1;echo 1;echo 1;echo 1;echo 1;echo 1; class E {}',
            ],
        ];
    }

    /**
     * @param string      $expected      PHP code
     * @param null|string $input         PHP code
     * @param array       $configuration
     *
     * @group legacy
     * @dataProvider provideTestCases
     * @expectedDeprecation Passing "replacements" at the root of the configuration for rule "phpdoc_return_self_reference" is deprecated and will not be supported in 3.0, use "replacements" => array(...) option instead.
     */
    public function testLegacyFix($expected, $input = null, array $configuration = [])
    {
        $this->fixer->configure($configuration);
        $this->doTest($expected, $input);
    }

    /**
     * @param string      $expected      PHP code
     * @param null|string $input         PHP code
     * @param array       $configuration
     *
     * @dataProvider provideTestCases
     */
    public function testFix($expected, $input = null, array $configuration = [])
    {
        $this->fixer->configure(['replacements' => $configuration]);
        $this->doTest($expected, $input);
    }

    public function provideTestCases()
    {
        return [
            [
                '<?php interface C{/** @return $self|int */function test();}',
                null,
                ['$static' => 'static'],
            ],
        ];
    }

    /**
     * @param string $expected
     * @param string $input
     *
     * @dataProvider provideGeneratedFixCases
     */
    public function testGeneratedFix($expected, $input)
    {
        $config = ['replacements' => [$input => $expected]];
        $this->fixer->configure($config);

        $expected = sprintf('<?php
/**
 * Please do not use @return %s|static|self|this|$static|$self|@static|@self|@this as return type hint
 */
class F
{
    /**
     * @param %s
     *
     * @return %s
     */
     public function AB($self)
     {
        return $this; // %s
     }
}
', $input, $input, $expected, $input);

        $input = sprintf('<?php
/**
 * Please do not use @return %s|static|self|this|$static|$self|@static|@self|@this as return type hint
 */
class F
{
    /**
     * @param %s
     *
     * @return %s
     */
     public function AB($self)
     {
        return $this; // %s
     }
}
', $input, $input, $input, $input);

        $this->doTest($expected, $input);
    }

    /**
     * Expected after fixing, return type to fix.
     *
     * @return array<array<string, string>
     */
    public function provideGeneratedFixCases()
    {
        return [
            ['$this', 'this'],
            ['$this', '@this'],
            ['self', '$self'],
            ['self', '@self'],
            ['static', '$static'],
            ['static', '@STATIC'],
        ];
    }

    /**
     * @param array  $configuration
     * @param string $message
     *
     * @dataProvider provideInvalidConfigurationCases
     */
    public function testInvalidConfiguration(array $configuration, $message)
    {
        $this->expectException(\PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException::class);
        $this->expectExceptionMessageRegExp(sprintf('/^\[phpdoc_return_self_reference\] %s$/', preg_quote($message, '/')));

        $this->fixer->configure($configuration);
    }

    public function provideInvalidConfigurationCases()
    {
        return [
            [
                ['replacements' => [1 => 'a']],
                'Invalid configuration: Unknown key "integer#1", expected any of "this", "@this", "$self", "@self", "$static", "@static".',
            ],
            [
                ['replacements' => [
                    'this' => 'foo',
                ]],
                'Invalid configuration: Unknown value "string#foo", expected any of "$this", "static", "self".',
            ],
        ];
    }

    /**
     * @requires PHP 7.0
     */
    public function testAnonymousClassFixing()
    {
        $this->doTest(
            '<?php
                $a = new class() {

                    /** @return $this */
                    public function a() {
                    }
                };

                class C
                {
                    public function A()
                    {
                        $a = new class() {
                            /** @return $this */
                            public function a() {}
                        };
                    }
                }
            ',
            '<?php
                $a = new class() {

                    /** @return @this */
                    public function a() {
                    }
                };

                class C
                {
                    public function A()
                    {
                        $a = new class() {
                            /** @return @this */
                            public function a() {}
                        };
                    }
                }
            '
        );
    }
}

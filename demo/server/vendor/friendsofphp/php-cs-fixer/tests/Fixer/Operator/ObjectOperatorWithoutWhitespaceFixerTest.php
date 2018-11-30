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

namespace PhpCsFixer\Tests\Fixer\Operator;

use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @author Farhad Safarov <farhad.safarov@gmail.com>
 *
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer
 */
final class ObjectOperatorWithoutWhitespaceFixerTest extends AbstractFixerTestCase
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
                '<?php $object->method();',
                '<?php $object   ->method();',
            ],
            [
                '<?php $object->method();',
                '<?php $object   ->   method();',
            ],
            [
                '<?php $object->method();',
                '<?php $object->   method();',
            ],
            [
                '<?php $object->method();',
                '<?php $object	->method();',
            ],
            [
                '<?php $object->method();',
                '<?php $object->	method();',
            ],
            [
                '<?php $object->method();',
                '<?php $object	->	method();',
            ],
            [
                '<?php echo "use it as -> you want";',
            ],
            // Ensure that doesn't break chained multi-line statements
            [
                '<?php $object->method()
                        ->method2()
                        ->method3();',
            ],
            [
                '<?php $this
             ->add()
             // Some comment
             ->delete();',
            ],
        ];
    }
}

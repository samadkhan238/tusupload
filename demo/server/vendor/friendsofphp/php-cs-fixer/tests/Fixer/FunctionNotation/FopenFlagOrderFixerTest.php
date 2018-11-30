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
 * @author SpacePossum
 *
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\FunctionNotation\FOpenFlagOrderFixer
 */
final class FopenFlagOrderFixerTest extends AbstractFixerTestCase
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
                '<?php
                    $a = fopen($foo, \'rw+b\');
                ',
                '<?php
                    $a = fopen($foo, \'brw+\');
                ',
            ],
            [
                '<?php
                    $a = \FOPEN($foo, "cr+w+b");
                    $a = \FOPEN($foo, "crw+b");
                ',
                '<?php
                    $a = \FOPEN($foo, "bw+r+c");
                    $a = \FOPEN($foo, "bw+rc");
                ',
            ],
            [
                '<?php
                    $a = fopen($foo,/*0*/\'rb\'/*1*/);
                ',
                '<?php
                    $a = fopen($foo,/*0*/\'br\'/*1*/);
                ',
            ],
            'binary string' => [
                '<?php
                    $a = \fopen($foo, b"cr+w+b");
                    $b = \fopen($foo, B"crw+b");
                    $c = \fopen($foo, b\'cr+w+b\');
                    $d = \fopen($foo, B\'crw+b\');
                ',
                '<?php
                    $a = \fopen($foo, b"bw+r+c");
                    $b = \fopen($foo, B"bw+rc");
                    $c = \fopen($foo, b\'bw+r+c\');
                    $d = \fopen($foo, B\'bw+rc\');
                ',
            ],
            'common typos' => [
                '<?php
                     $a = fopen($a, "b+r");
                     $b = fopen($b, "b+w");
                ',
            ],
            // `t` cases
            [
                '<?php
                    $a = fopen($foo, \'rw+t\');
                ',
                '<?php
                    $a = fopen($foo, \'trw+\');
                ',
            ],
            [
                '<?php
                    $a = fopen($foo, \'rw+tb\');
                ',
                '<?php
                    $a = fopen($foo, \'btrw+\');
                ',
            ],
            // don't fix cases
            'single flag' => [
                '<?php
                    $a = fopen($foo, "r");
                    $a = fopen($foo, "r+");
                ',
            ],
            'not simple flags' => [
                '<?php
                    $a = fopen($foo, "br+".$a);
                ',
            ],
            'wrong # of arguments' => [
                '<?php
                    $b = fopen("br+");
                    $c = fopen($foo, "bw+", 1, 2 , 3);
                ',
            ],
            '"flags" is too long (must be overridden)' => [
                '<?php
                    $d = fopen($foo, "r+w+a+x+c+etbX");
                ',
            ],
            'static method call' => [
                '<?php
                    $e = A::fopen($foo, "bw+");
                ',
            ],
            'method call' => [
                '<?php
                    $f = $b->fopen($foo, "br+");
                ',
            ],
            'comments, PHPDoc and literal' => [
                '<?php
                    // fopen($foo, "brw");
                    /* fopen($foo, "brw"); */
                    echo("fopen($foo, \"brw\")");
                ',
            ],
        ];
    }
}

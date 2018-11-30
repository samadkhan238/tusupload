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

namespace PhpCsFixer\Tests\Fixer\ListNotation;

use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @requires PHP 7.1
 *
 * @author SpacePossum
 *
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer
 */
final class ListSyntaxFixerTest extends AbstractFixerTestCase
{
    public function testFixWithDefaultConfiguration()
    {
        $this->fixer->configure([]);
        $this->doTest(
            '<?php $a = list($a, $b) = $a; list($b) = $a;',
            '<?php $a = list($a, $b) = $a; [$b] = $a;'
        );
    }

    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideToLongCases
     */
    public function testFixToLongSyntax($expected, $input = null)
    {
        $this->fixer->configure(['syntax' => 'long']);
        $this->doTest($expected, $input);
    }

    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideToShortCases
     */
    public function testFixToShortSyntax($expected, $input = null)
    {
        $this->fixer->configure(['syntax' => 'short']);
        $this->doTest($expected, $input);
    }

    public function provideToLongCases()
    {
        // reverse testing
        $shortCases = $this->provideToShortCases();
        $cases = [];
        foreach ($shortCases as $label => $shortCase) {
            $cases[$label] = [$shortCase[1], $shortCase[0]];
        }

        // the reverse of this is different because of all the comments and white space,
        // therefore we override with a similar case case here
        $cases['comment case'] = [
            '<?php
#
list(#
$a#
)#
=#
$a#
;#',
            '<?php
#
[#
$a#
]#
=#
$a#
;#',
        ];

        // cannot fix cases
        $cases[] = ['<?php [[$a, $b], [$c, $d]] = $a;'];
        $cases[] = ['<?php [[$a, [$b]], [[$c, [$d]]]] = $a;'];

        return $cases;
    }

    public function provideToShortCases()
    {
        return [
            [
                '<?php [$x] = $a;',
                '<?php list($x) = $a;',
            ],
            [
                '<?php [$a, $b, $c] = $array;',
                '<?php list($a, $b, $c) = $array;',
            ],
            [
                '<?php ["a" => $a, "b" => $b, "c" => $c] = $array;',
                '<?php list("a" => $a, "b" => $b, "c" => $c) = $array;',
            ],
            [
                '<?php
#
[//
    $x] =/**/$a?>',
                '<?php
#
list(//
    $x) =/**/$a?>',
            ],
            'comment case' => [
                '<?php
#a
#g
[#h
#f
$a#
#e
]#
#
=#c
#
$a;#
#
',
                '<?php
#a
list#g
(#h
#f
$a#
#e
)#
#
=#c
#
$a;#
#
',
            ],
        ];
    }
}

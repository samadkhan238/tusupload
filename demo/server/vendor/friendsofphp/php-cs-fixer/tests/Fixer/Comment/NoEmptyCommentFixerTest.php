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

namespace PhpCsFixer\Tests\Fixer\Comment;

use PhpCsFixer\Tests\Test\AbstractFixerTestCase;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author SpacePossum
 *
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer
 */
final class NoEmptyCommentFixerTest extends AbstractFixerTestCase
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
            // fix cases
            [
                '<?php
                    echo 0;
echo 1;
                ',
                '<?php
                    echo 0;//
echo 1;
                ',
            ],
            [
                '<?php
                    echo 0;
    echo 1;
                ',
                '<?php
                    echo 0;//
    echo 1;
                ',
            ],
            [
                '<?php
                    echo 1;
                ',
                '<?php
                    echo 1;//
                ',
            ],
            [
                '<?php
                echo 2;
                    '.'
echo 1;
                ',
                '<?php
                echo 2;
                    //
echo 1;
                ',
            ],
            [
                '<?php

                ?>',
                '<?php

                //?>',
            ],
            [
                '<?php
                    '.'
                ',
                '<?php
                    //
                ',
            ],
            [
                '<?php
                    '.'
                ',
                '<?php
                    #
                ',
            ],
            [
                '<?php
                    '.'
                ',
                '<?php
                    /**/
                ',
            ],
            [
                '<?php
                    echo 0;echo 1;
                ',
                '<?php
                    echo 0;/**/echo 1;
                ',
            ],
            [
                '<?php
                    echo 0;echo 1;
                ',
                '<?php
                    echo 0;/**//**//**/echo 1/**/;
                ',
            ],
            [
                '<?php
                ',
                '<?php
                //',
            ],
            [
                '<?php
                ',
                '<?php
                /*


                */',
            ],
            [
                '<?php
                    '.'
                    '.'
                    '.'
                    '.'
                ',
                '<?php
                    //
                    //
                    //
                    /**///
                ',
            ],
            [
                "<?php\n                    \n                    \n                    \n                    \n                ",
                "<?php\n                    //\n                    //\n                    //\n                    /**///\n                ",
            ],
            [
                "<?php\r                    \r                    \r                    \r                    \r                ",
                "<?php\r                    //\r                    //\r                    //\r                    /**///\r                ",
            ],
            [
                "<?php\r\n                    \r\n                    \r\n                    \r\n                    \r\n                ",
                "<?php\r\n                    //\r\n                    //\r\n                    //\r\n                    /**///\r\n                ",
            ],
            [
                "<?php\necho 1;\r\recho 2;",
                "<?php\necho 1;\r//\recho 2;",
            ],
            // do not fix cases
            [
                '<?php
                // a
            // /**/
              // #
/* b */ // s
          #                        c',
            ],
            [
                '<?php
                    // This comment could be nicely formatted.
                    //
                    //
                    // For that, it could have some empty comment lines inside.
                    //

                    ## A 1
                    ##
                    ##
                    ## A 2
                    ##

                    // B 1
                    //
                    // B 2

                    ## C 1
                    ##
                    ## C 2

                    $foo = 1;

                    //
                    // a
                    //

                    $bar = 2;
                ',
            ],
        ];
    }

    /**
     * @param string $source     valid PHP source code
     * @param int    $startIndex start index of the comment block
     * @param int    $endIndex   expected index of the last token of the block
     * @param bool   $isEmpty    expected value of empty flag returned
     *
     * @dataProvider provideCommentBlockCases
     */
    public function testGetCommentBlock($source, $startIndex, $endIndex, $isEmpty)
    {
        Tokens::clearCache();
        $tokens = Tokens::fromCode($source);
        $this->assertTrue($tokens[$startIndex]->isComment(), sprintf('Misconfiguration of test, expected comment token at index "%d".', $startIndex));

        $method = new \ReflectionMethod($this->fixer, 'getCommentBlock');
        $method->setAccessible(true);

        list($foundStart, $foundEnd, $foundIsEmpty) = $method->invoke($this->fixer, $tokens, $startIndex);

        $this->assertSame($startIndex, $foundStart, 'Find start index of block failed.');
        $this->assertSame($endIndex, $foundEnd, 'Find end index of block failed.');
        $this->assertSame($isEmpty, $foundIsEmpty, 'Is empty comment block detection failed.');
    }

    public function provideCommentBlockCases()
    {
        $cases = [
            [
                '<?php // a',
                1,
                1,
                false,
            ],
            [
                '<?php
                    // This comment could be nicely formatted.
                    //
                    //
                    // For that, it could have some empty comment lines inside.
                    //           ',
                2,
                11,
                false,
            ],
            [
                '<?php
/**///',
                1,
                1,
                true,
            ],
            [
                '<?php
//
//

#
#
',
                5,
                8,
                true,
            ],
            [
                '<?php
//
//

//
//
',
                5,
                8,
                true,
            ],
            [
                '<?php
//
//

//
//
',
                1,
                3,
                true,
            ],
            [
                str_replace("\n", "\r", "<?php\n//\n//\n\n//\n//\n"),
                1,
                3,
                true,
            ],
            [
                str_replace("\n", "\r\n", "<?php\n//\n//\n\n//\n//\n"),
                1,
                3,
                true,
            ],
            [
                '<?php
//

//
',
                1,
                1,
                true,
            ],
            [
                '<?php
//
//
              $a;  ',
                1,
                4,
                true,
            ],
            [
                '<?php
//',
                1,
                1,
                true,
            ],
        ];

        $src = '<?php
                // a2
            // /*4*/
              // #6
/* b8 */ // s10
          #                        c12';

        foreach ([2, 4, 6] as $i) {
            $cases[] = [$src, $i, 7, false];
        }

        $cases[] = [$src, 8, 9, false];
        $cases[] = [$src, 10, 11, false];
        $cases[] = [$src, 12, 12, false];

        return $cases;
    }
}

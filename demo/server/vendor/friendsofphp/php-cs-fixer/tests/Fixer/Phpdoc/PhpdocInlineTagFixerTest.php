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
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\Phpdoc\PhpdocInlineTagFixer
 */
final class PhpdocInlineTagFixerTest extends AbstractFixerTestCase
{
    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideTestFixInlineDocCases
     */
    public function testFixInlineDoc($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function provideTestFixInlineDocCases()
    {
        $cases = [
            [
                '<?php
    /**
     * {link} { LINK }
     * { test }
     * {@inheritdoc rire éclatant des écoliers qui décontenança®¶ñ¿}
     * test other comment
     * {@inheritdoc test} a
     * {@inheritdoc test} b
     * {@inheritdoc test} c
     * {@inheritdoc foo bar.} d
     * {@inheritdoc foo bar.} e
     * {@inheritdoc test} f
     * end comment {@inheritdoc here we are done} @spacepossum {1}
     */
',
                '<?php
    /**
     * {link} { LINK }
     * { test }
     * {@inheritDoc rire éclatant des écoliers qui décontenança®¶ñ¿ }
     * test other comment
     * @{inheritdoc test} a
     * {{@inheritdoc    test}} b
     * {@ inheritdoc   test} c
     * { @inheritdoc 	foo bar.  } d
     * {@ 	inheritdoc foo bar.	} e
     * @{{inheritdoc test}} f
     * end comment {@inheritdoc here we are done} @spacepossum {1}
     */
',
            ],
        ];

        foreach (['example', 'id', 'internal', 'inheritdoc', 'link', 'source', 'toc', 'tutorial'] as $tag) {
            $cases[] = [
                sprintf("<?php\n     /**\n      * {@%s}a\n      */\n", $tag),
                sprintf("<?php\n     /**\n      * @{%s}a\n      */\n", $tag),
            ];
            $cases[] = [
                sprintf("<?php\n     /**\n      * {@%s} b\n      */\n", $tag),
                sprintf("<?php\n     /**\n      * {{@%s}} b\n      */\n", $tag),
            ];
            $cases[] = [
                sprintf("<?php\n     /**\n      * c {@%s}\n      */\n", $tag),
                sprintf("<?php\n     /**\n      * c @{{%s}}\n      */\n", $tag),
            ];
            $cases[] = [
                sprintf("<?php\n     /**\n      * c {@%s test}\n      */\n", $tag),
                sprintf("<?php\n     /**\n      * c @{{%s test}}\n      */\n", $tag),
            ];
            // test unbalanced { tags
            $cases[] = [
                sprintf("<?php\n     /**\n      * c {@%s test}\n      */\n", $tag),
                sprintf("<?php\n     /**\n      * c {@%s test}}\n      */\n", $tag),
            ];
            $cases[] = [
                sprintf("<?php\n     /**\n      * c {@%s test}\n      */\n", $tag),
                sprintf("<?php\n     /**\n      * c {{@%s test}\n      */\n", $tag),
            ];
            $cases[] = [
                sprintf("<?php\n     /**\n      * c {@%s test}\n      */\n", $tag),
                sprintf("<?php\n     /**\n      * c {@%s test}}\n      */\n", $tag),
            ];
            $cases[] = [
                sprintf("<?php\n     /**\n      * c {@%s test}\n      */\n", $tag),
                sprintf("<?php\n     /**\n      * c @{{%s test}}}\n      */\n", $tag),
            ];
        }

        // don't touch custom tags
        $tag = 'foo';
        $cases[] = [
            sprintf("<?php\n     /**\n      * @{%s}a\n      */\n", $tag),
        ];
        $cases[] = [
            sprintf("<?php\n     /**\n      * {{@%s}} b\n      */\n", $tag),
        ];
        $cases[] = [
            sprintf("<?php\n     /**\n      * c @{{%s}}\n      */\n", $tag),
        ];

        // don't auto inline tags with the exception of inheritdoc
        foreach (['example', 'id', 'internal', 'foo', 'link', 'source', 'toc', 'tutorial'] as $tag) {
            $cases[] = [
                sprintf("<?php\n     /**\n      * @%s\n      */\n", $tag),
            ];
        }

        // don't touch well formatted tags
        foreach (['example', 'id', 'internal', 'inheritdoc', 'link', 'source', 'toc', 'tutorial'] as $tag) {
            $cases[] = [
                sprintf("<?php\n     /**\n      * {@%s}\n      */\n", $tag),
            ];
        }

        // common typos
        $cases[] = [
            '<?php
    /**
     * Typo {@inheritdoc} {@example} {@id} {@source} {@tutorial} {links}
     * inheritdocs
     */
',
            '<?php
    /**
     * Typo {@inheritdocs} {@exampleS} { @ids} { @sources } {{{ @tutorials }} {links}
     * inheritdocs
     */
',
        ];

        // invalid syntax
        $cases[] = [
            '<?php
    /**
     * {@link https://www.ietf.org/rfc/rfc1035.txt)
     */
    $someVar = "hello";',
        ];

        return $cases;
    }

    /**
     * @dataProvider provideTestFixInheritDocCases
     */
    public function testFixInheritDoc($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function provideTestFixInheritDocCases()
    {
        return [
            [
                '<?php
    /**
     * {@inheritdoc} should this be inside the tag?
     * {@inheritdoc}
     * {@inheritdoc}
     * {@inheritdoc}
     * inheritdoc
     */
',
                // missing { } test for inheritdoc
                '<?php
    /**
     * @inheritdoc should this be inside the tag?
     * @inheritdoc
     * @inheritdocs
     * {@inheritdocs}
     * inheritdoc
     */
',
            ],
        ];
    }
}

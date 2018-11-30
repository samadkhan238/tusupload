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

namespace PhpCsFixer\Tests\Fixer\ReturnNotation;

use PhpCsFixer\Tests\Test\AbstractFixerTestCase;
use PhpCsFixer\WhitespacesFixerConfig;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\ReturnNotation\BlankLineBeforeReturnFixer
 */
final class BlankLineBeforeReturnFixerTest extends AbstractFixerTestCase
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
                '
$a = $a;
return $a;',
            ],
            [
                '<?php
$a = $a;

return $a;',
                '<?php
$a = $a; return $a;',
            ],
            [
                '<?php
$b = $b;

return $b;',
                '<?php
$b = $b;return $b;',
            ],
            [
                '<?php
$c = $c;

return $c;',
                '<?php
$c = $c;
return $c;',
            ],
            [
                '<?php
    $d = $d;

    return $d;',
                '<?php
    $d = $d;
    return $d;',
            ],
            [
                '<?php
    if (true) {
        return 1;
    }',
            ],
            [
                '<?php
    if (true)
        return 1;
    ',
            ],
            [
                '<?php
    if (true) {
        return 1;
    } else {
        return 2;
    }',
            ],
            [
                '<?php
    if (true)
        return 1;
    else
        return 2;
    ',
            ],
            [
                '<?php
    if (true) {
        return 1;
    } elseif (false) {
        return 2;
    }',
            ],
            [
                '<?php
    if (true)
        return 1;
    elseif (false)
        return 2;
    ',
            ],
            [
                '<?php
    throw new Exception("return true;");',
            ],
            [
                '<?php
    function foo()
    {
        // comment
        return "foo";
    }',
            ],
            [
                '<?php
    function foo()
    {
        // comment

        return "bar";
    }',
            ],
        ];
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
                "<?php\r\n\$a = \$a;\r\n\r\nreturn \$a;",
                "<?php\r\n\$a = \$a; return \$a;",
            ],
            [
                "<?php\r\n\$b = \$b;\r\n\r\nreturn \$b;",
                "<?php\r\n\$b = \$b;return \$b;",
            ],
            [
                "<?php\r\n\$c = \$c;\r\n\r\nreturn \$c;",
                "<?php\r\n\$c = \$c;\r\nreturn \$c;",
            ],
        ];
    }
}

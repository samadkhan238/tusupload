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

namespace PhpCsFixer\Tests\Linter;

use PhpCsFixer\Linter\LinterInterface;
use PhpCsFixer\Tests\TestCase;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
abstract class AbstractLinterTestCase extends TestCase
{
    abstract public function testIsAsync();

    public function testLintingAfterTokenManipulation()
    {
        $linter = $this->createLinter();

        $tokens = Tokens::fromCode("<?php \n#EOF\n");
        $tokens->insertAt(1, new Token([T_NS_SEPARATOR, '\\']));

        $this->expectException(\PhpCsFixer\Linter\LintingException::class);
        $linter->lintSource($tokens->generateCode())->check();
    }

    /**
     * @param string      $file
     * @param null|string $errorRegExp
     *
     * @dataProvider provideLintFileCases
     */
    public function testLintFile($file, $errorRegExp = null)
    {
        if (null !== $errorRegExp) {
            $this->expectException(\PhpCsFixer\Linter\LintingException::class);
            $this->expectExceptionMessageRegExp($errorRegExp);
        }

        $linter = $this->createLinter();

        $this->assertNull($linter->lintFile($file)->check());
    }

    /**
     * @return array
     */
    public function provideLintFileCases()
    {
        return [
            [
                __DIR__.'/../Fixtures/Linter/valid.php',
            ],
            [
                __DIR__.'/../Fixtures/Linter/invalid.php',
                '/syntax error, unexpected.*T_ECHO.*line 5/',
            ],
        ];
    }

    /**
     * @param string      $source
     * @param null|string $errorRegExp
     *
     * @dataProvider provideLintSourceCases
     */
    public function testLintSource($source, $errorRegExp = null)
    {
        if (null !== $errorRegExp) {
            $this->expectException(\PhpCsFixer\Linter\LintingException::class);
            $this->expectExceptionMessageRegExp($errorRegExp);
        }

        $linter = $this->createLinter();

        $this->assertNull($linter->lintSource($source)->check());
    }

    /**
     * @return array
     */
    public function provideLintSourceCases()
    {
        return [
            [
                '<?php echo 123;',
            ],
            [
                '<?php
                    print "line 2";
                    print "line 3";
                    print "line 4";
                    echo echo;
                ',
                '/syntax error, unexpected.*T_ECHO.*line 5/',
            ],
        ];
    }

    /**
     * @return LinterInterface
     */
    abstract protected function createLinter();
}

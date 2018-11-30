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

namespace PhpCsFixer\Tests\Fixer\Basic;

use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @author Graham Campbell <graham@alt-three.com>
 *
 * @internal
 *
 * @covers \PhpCsFixer\AbstractPsrAutoloadingFixer
 * @covers \PhpCsFixer\Fixer\Basic\Psr4Fixer
 */
final class Psr4FixerTest extends AbstractFixerTestCase
{
    public function testFixCase()
    {
        $fileProphecy = $this->prophesize();
        $fileProphecy->willExtend(\SplFileInfo::class);
        $fileProphecy->getBasename()->willReturn('Bar.php');
        $fileProphecy->getRealPath()->willReturn(__DIR__.'/Psr4/Foo/Bar.php');
        $file = $fileProphecy->reveal();

        $expected = <<<'EOF'
<?php
namespace Psr4\foo;
class Bar {}
EOF;
        $input = <<<'EOF'
<?php
namespace Psr4\foo;
class bar {}
EOF;

        $this->doTest($expected, $input, $file);

        $expected = <<<'EOF'
<?php
class Psr4_Foo_Bar {}
EOF;
        $input = <<<'EOF'
<?php
class Psr4_fOo_bAr {}
EOF;

        $this->doTest($expected, $input, $file);
    }

    public function testFixClassName()
    {
        $file = $this->getTestFile(__FILE__);

        $expected = <<<'EOF'
<?php
namespace PhpCsFixer\Tests\Fixer\Basic;
class Psr4FixerTest {}
/* class foo */
EOF;
        $input = <<<'EOF'
<?php
namespace PhpCsFixer\Tests\Fixer\Basic;
class blah {}
/* class foo */
EOF;

        $this->doTest($expected, $input, $file);
    }

    public function testFixAbstractClassName()
    {
        $file = $this->getTestFile(__FILE__);

        $expected = <<<'EOF'
<?php
namespace PhpCsFixer\Tests\Fixer\Basic;
abstract class Psr4FixerTest {}
/* class foo */
EOF;
        $input = <<<'EOF'
<?php
namespace PhpCsFixer\Tests\Fixer\Basic;
abstract class blah {}
/* class foo */
EOF;

        $this->doTest($expected, $input, $file);
    }

    public function testFixFinalClassName()
    {
        $file = $this->getTestFile(__FILE__);

        $expected = <<<'EOF'
<?php
namespace PhpCsFixer\Tests\Fixer\Basic;
final class Psr4FixerTest {}
/* class foo */
EOF;
        $input = <<<'EOF'
<?php
namespace PhpCsFixer\Tests\Fixer\Basic;
final class blah {}
/* class foo */
EOF;

        $this->doTest($expected, $input, $file);
    }

    public function testFixClassNameWithComment()
    {
        $file = $this->getTestFile(__FILE__);

        $expected = <<<'EOF'
<?php
namespace /* namespace here */ PhpCsFixer\Fixer\Psr4;
class /* hi there */ Psr4FixerTest /* why hello */ {}
/* class foo */
EOF;
        $input = <<<'EOF'
<?php
namespace /* namespace here */ PhpCsFixer\Fixer\Psr4;
class /* hi there */ blah /* why hello */ {}
/* class foo */
EOF;

        $this->doTest($expected, $input, $file);
    }

    public function testHandlePartialNamespaces()
    {
        $file = $this->getTestFile(__DIR__.'/../../../src/Fixer/Basic/Psr4Fixer.php');

        $expected = <<<'EOF'
<?php
namespace Foo\Bar\Baz\FIXER\Basic;
class Psr4Fixer {}
EOF;
        $this->doTest($expected, null, $file);

        $expected = <<<'EOF'
<?php
namespace /* hi there */ Foo\Bar\Baz\FIXER\Basic;
class /* hi there */ Psr4Fixer {}
EOF;
        $this->doTest($expected, null, $file);

        $expected = <<<'EOF'
<?php
namespace Foo\Bar\Baz;
class Psr4Fixer {}
EOF;
        $this->doTest($expected, null, $file);
    }

    /**
     * @requires PHP 7.0
     */
    public function testIgnoreAnonymousClass()
    {
        $file = $this->getTestFile(__FILE__);

        $expected = <<<'EOF'
<?php
namespace PhpCsFixer\Tests\Fixer\Basic;
new class implements Countable {};
EOF;

        $this->doTest($expected, null, $file);

        $expected = <<<'EOF'
<?php
namespace PhpCsFixer\Tests\Fixer\Basic;
new class extends stdClass {};
EOF;

        $this->doTest($expected, null, $file);
    }

    /**
     * @param string $filename
     *
     * @dataProvider provideIgnoredCases
     */
    public function testIgnoreWrongNames($filename)
    {
        $file = $this->getTestFile($filename);

        $expected = <<<'EOF'
<?php
namespace Aaa;
class Bar {}
EOF;

        $this->doTest($expected, null, $file);
    }

    public function provideIgnoredCases()
    {
        $ignoreCases = [
            ['.php'],
            ['Foo.class.php'],
            ['4Foo.php'],
            ['$#.php'],
        ];

        foreach (['__halt_compiler', 'abstract', 'and', 'array', 'as', 'break', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'interface', 'isset', 'list', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'try', 'unset', 'use', 'var', 'while', 'xor'] as $keyword) {
            $ignoreCases[] = [$keyword.'.php'];
        }

        foreach (['__CLASS__', '__DIR__', '__FILE__', '__FUNCTION__', '__LINE__', '__METHOD__', '__NAMESPACE__'] as $magicConstant) {
            $ignoreCases[] = [$magicConstant.'.php'];
            $ignoreCases[] = [strtolower($magicConstant).'.php'];
        }

        foreach ([
            'T_CALLABLE' => 'callable',
            'T_FINALLY' => 'finally',
            'T_INSTEADOF' => 'insteadof',
            'T_TRAIT' => 'trait',
            'T_TRAIT_C' => '__TRAIT__',
        ] as $tokenType => $tokenValue) {
            if (\defined($tokenType)) {
                $ignoreCases[] = [$tokenValue.'.php'];
                $ignoreCases[] = [strtolower($tokenValue).'.php'];
            }
        }

        return $ignoreCases;
    }
}

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

namespace PhpCsFixer\Tests\Fixtures;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\Tokenizer\Tokens;

final class DeprecatedFixer extends AbstractFixer implements DeprecatedFixerInterface, ConfigurationDefinitionFixerInterface
{
    public function getDefinition()
    {
    }

    public function isCandidate(Tokens $tokens)
    {
    }

    public function doSomethingWithCreateConfigDefinition()
    {
        return $this->createConfigurationDefinition();
    }

    public function getSuccessorsNames()
    {
        return ['testA', 'testB'];
    }

    public function getName()
    {
        return 'Vendor4/foo';
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens)
    {
    }

    protected function createConfigurationDefinition()
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('foo', 'Foo.'))->getOption()
        ]);
    }
}

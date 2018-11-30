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

namespace PhpCsFixer\Tests\Console;

use PhpCsFixer\Console\Application;
use PhpCsFixer\Tests\TestCase;

/**
 * @author SpacePossum
 *
 * @internal
 *
 * @covers \PhpCsFixer\Console\Application
 */
final class ApplicationTest extends TestCase
{
    public function testApplication()
    {
        $app = new Application();
        $this->assertStringMatchesFormat('%s by <comment>Fabien Potencier</comment> and <comment>Dariusz Ruminski</comment>', $app->getLongVersion());
    }
}

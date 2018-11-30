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

namespace PhpCsFixer\Tests\Differ;

use PhpCsFixer\Differ\SebastianBergmannShortDiffer;

/**
 * @author Andreas Möller <am@localheinz.com>
 *
 * @internal
 *
 * @covers \PhpCsFixer\Differ\SebastianBergmannShortDiffer
 */
final class SebastianBergmannShortDifferTest extends AbstractDifferTestCase
{
    public function testDiffReturnsDiff()
    {
        $diff = '--- Original
+++ New
-    if (!array_key_exists("foo", $options)) {
+    if (!\array_key_exists("foo", $options)) {
';

        $differ = new SebastianBergmannShortDiffer();

        $this->assertSame($diff, $differ->diff($this->oldCode(), $this->newCode()));
    }
}

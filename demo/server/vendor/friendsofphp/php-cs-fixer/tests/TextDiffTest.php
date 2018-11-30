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

namespace PhpCsFixer\Tests;

use PhpCsFixer\Console\Command\FixCommand;
use PhpCsFixer\Report\ReporterFactory;
use PhpCsFixer\ToolInfo;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author SpacePossum
 *
 * @internal
 *
 * @coversNothing
 * @group covers-nothing
 */
final class TextDiffTest extends TestCase
{
    /**
     * @param string $expected
     * @param string $format
     * @param bool   $isDecorated
     *
     * @dataProvider provideDiffReportingCases
     */
    public function testDiffReportingDecorated($expected, $format, $isDecorated)
    {
        $command = new FixCommand(new ToolInfo());
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'path' => [__DIR__.'/Fixtures/FixCommand/TextDiffTestInput.php'],
                '--diff' => true,
                '--dry-run' => true,
                '--format' => $format,
                '--rules' => 'cast_spaces',
                '--using-cache' => 'no',
            ],
            [
                'decorated' => $isDecorated,
                'verbosity' => OutputInterface::VERBOSITY_NORMAL,
            ]
        );

        if ($isDecorated !== $commandTester->getOutput()->isDecorated()) {
            $this->markTestSkipped(sprintf('Output should %sbe decorated.', $isDecorated ? '' : 'not '));
        }

        if ($isDecorated !== $commandTester->getOutput()->getFormatter()->isDecorated()) {
            $this->markTestSkipped(sprintf('Formatter should %sbe decorated.', $isDecorated ? '' : 'not '));
        }

        $this->assertStringMatchesFormat($expected, $commandTester->getDisplay(false));
    }

    public function provideDiffReportingCases()
    {
        $expected = <<<'TEST'
%A$output->writeln('<error>'.(int)$output.'</error>');%A
%A$output->writeln('<error>'.(int) $output.'</error>');%A
TEST;
        $cases = [];
        foreach (['txt', 'xml', 'junit'] as $format) {
            $cases[] = [$expected, $format, true];
            $cases[] = [$expected, $format, false];
        }

        $expected = substr(json_encode($expected), 1, -1);
        $cases[] = [$expected, 'json', true];
        $cases[] = [$expected, 'json', false];

        return $cases;
    }

    /**
     * Test to make sure @see TextDiffTest::provideDiffReportingCases covers all formats.
     */
    public function testAllFormatsCovered()
    {
        $factory = ReporterFactory::create();
        $formats = $factory->registerBuiltInReporters()->getFormats();
        sort($formats);

        $this->assertSame(
            ['checkstyle', 'json', 'junit', 'txt', 'xml'],
            $formats
        );
    }
}

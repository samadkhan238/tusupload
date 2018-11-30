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

namespace PhpCsFixer\Tests\Report;

use PhpCsFixer\Report\ReporterInterface;
use PhpCsFixer\Report\ReportSummary;
use PhpCsFixer\Tests\TestCase;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
abstract class AbstractReporterTestCase extends TestCase
{
    /**
     * @var ReporterInterface
     */
    protected $reporter;

    protected function setUp()
    {
        parent::setUp();

        $this->reporter = $this->createReporter();
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->reporter = null;
    }

    final public function testGetFormat()
    {
        $this->assertSame(
            $this->getFormat(),
            $this->reporter->getFormat()
        );
    }

    /**
     * @param string        $expectedReport
     * @param ReportSummary $reportSummary
     *
     * @dataProvider provideGenerateCases
     */
    final public function testGenerate($expectedReport, ReportSummary $reportSummary)
    {
        $actualReport = $this->reporter->generate($reportSummary);

        $this->assertFormat($expectedReport, $actualReport);
    }

    /**
     * @return array
     */
    final public function provideGenerateCases()
    {
        return [
            'no errors' => [
                $this->createNoErrorReport(),
                new ReportSummary(
                    [],
                    0,
                    0,
                    false,
                    false,
                    false
                ),
            ],
            'simple' => [
                $this->createSimpleReport(),
                new ReportSummary(
                    [
                        'someFile.php' => [
                            'appliedFixers' => ['some_fixer_name_here'],
                        ],
                    ],
                    0,
                    0,
                    false,
                    false,
                    false
                ),
            ],
            'with diff' => [
                $this->createWithDiffReport(),
                new ReportSummary(
                    [
                        'someFile.php' => [
                            'appliedFixers' => ['some_fixer_name_here'],
                            'diff' => 'this text is a diff ;)',
                        ],
                    ],
                    0,
                    0,
                    false,
                    false,
                    false
                ),
            ],
            'with applied fixers' => [
                $this->createWithAppliedFixersReport(),
                new ReportSummary(
                    [
                        'someFile.php' => [
                            'appliedFixers' => ['some_fixer_name_here_1', 'some_fixer_name_here_2'],
                        ],
                    ],
                    0,
                    0,
                    true,
                    false,
                    false
                ),
            ],
            'with time and memory' => [
                $this->createWithTimeAndMemoryReport(),
                new ReportSummary(
                    [
                        'someFile.php' => [
                            'appliedFixers' => ['some_fixer_name_here'],
                        ],
                    ],
                    1234,
                    2.5 * 1024 * 1024,
                    false,
                    false,
                    false
                ),
            ],
            'complex' => [
                $this->createComplexReport(),
                new ReportSummary(
                    [
                        'someFile.php' => [
                            'appliedFixers' => ['some_fixer_name_here_1', 'some_fixer_name_here_2'],
                            'diff' => 'this text is a diff ;)',
                        ],
                        'anotherFile.php' => [
                            'appliedFixers' => ['another_fixer_name_here'],
                            'diff' => 'another diff here ;)',
                        ],
                    ],
                    1234,
                    2.5 * 1024 * 1024,
                    true,
                    true,
                    true
                ),
            ],
        ];
    }

    /**
     * @return ReporterInterface
     */
    abstract protected function createReporter();

    /**
     * @return string
     */
    abstract protected function getFormat();

    /**
     * @return string
     */
    abstract protected function createNoErrorReport();

    /**
     * @return string
     */
    abstract protected function createSimpleReport();

    /**
     * @return string
     */
    abstract protected function createWithDiffReport();

    /**
     * @return string
     */
    abstract protected function createWithAppliedFixersReport();

    /**
     * @return string
     */
    abstract protected function createWithTimeAndMemoryReport();

    /**
     * @return string
     */
    abstract protected function createComplexReport();

    /**
     * @param string $expected
     * @param string $input
     */
    abstract protected function assertFormat($expected, $input);
}

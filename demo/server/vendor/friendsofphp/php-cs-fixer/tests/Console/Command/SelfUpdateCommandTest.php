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

namespace PhpCsFixer\Tests\Console\Command;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamException;
use org\bovigo\vfs\vfsStreamWrapper;
use PhpCsFixer\Console\Application;
use PhpCsFixer\Console\Command\SelfUpdateCommand;
use PhpCsFixer\Console\SelfUpdate\NewVersionChecker;
use PhpCsFixer\Tests\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 *
 * @covers \PhpCsFixer\Console\Command\SelfUpdateCommand
 */
final class SelfUpdateCommandTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    protected function setUp()
    {
        parent::setUp();

        $this->root = vfsStream::setup();

        file_put_contents($this->getToolPath(), 'Current PHP CS Fixer.');

        file_put_contents("{$this->root->url()}/{$this->getNewMinorVersion()}.phar", 'New minor version of PHP CS Fixer.');
        file_put_contents("{$this->root->url()}/{$this->getNewMajorVersion()}.phar", 'New major version of PHP CS Fixer.');
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->root = null;

        try {
            vfsStreamWrapper::unregister();
        } catch (vfsStreamException $exception) {
            // ignored
        }
    }

    /**
     * @param string $name
     *
     * @dataProvider provideCommandNameCases
     */
    public function testCommandName($name)
    {
        $command = new SelfUpdateCommand(
            $this->prophesize(\PhpCsFixer\Console\SelfUpdate\NewVersionCheckerInterface::class)->reveal(),
            $this->createToolInfo(),
            $this->prophesize(\PhpCsFixer\PharCheckerInterface::class)->reveal()
        );

        $application = new Application();
        $application->add($command);

        $this->assertSame($command, $application->find($name));
    }

    public function provideCommandNameCases()
    {
        return [
            ['self-update'],
            ['selfupdate'],
        ];
    }

    /**
     * @param string $latestVersion
     * @param string $latestMinorVersion
     * @param array  $input
     * @param bool   $decorated
     * @param string $expectedFileContents
     * @param string $expectedDisplay
     *
     * @dataProvider provideExecuteCases
     */
    public function testExecute(
        $latestVersion,
        $latestMinorVersion,
        array $input,
        $decorated,
        $expectedFileContents,
        $expectedDisplay
    ) {
        $versionChecker = $this->prophesize(\PhpCsFixer\Console\SelfUpdate\NewVersionCheckerInterface::class);

        $versionChecker->getLatestVersion()->willReturn($latestVersion);

        $versionChecker
            ->getLatestVersionOfMajor($this->getCurrentMajorVersion())
            ->willReturn($latestMinorVersion)
        ;

        $actualVersionCheck = new NewVersionChecker(
            $this->prophesize(\PhpCsFixer\Console\SelfUpdate\GithubClientInterface::class)->reveal()
        );

        $versionChecker
            ->compareVersions(Argument::type('string'), Argument::type('string'))
            ->will(function (array $arguments) use ($actualVersionCheck) {
                return $actualVersionCheck->compareVersions($arguments[0], $arguments[1]);
            })
        ;

        $command = new SelfUpdateCommand(
            $versionChecker->reveal(),
            $this->createToolInfo(),
            $this->prophesize(\PhpCsFixer\PharCheckerInterface::class)->reveal()
        );

        $commandTester = $this->execute($command, $input, $decorated);

        $this->assertSame($expectedFileContents, file_get_contents($this->getToolPath()));
        $this->assertDisplay($expectedDisplay, $commandTester);
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function provideExecuteCases()
    {
        $minor = $this->getNewMinorVersion();
        $major = $this->getNewMajorVersion();

        $currentContents = 'Current PHP CS Fixer.';
        $minorContents = 'New minor version of PHP CS Fixer.';
        $majorContents = 'New major version of PHP CS Fixer.';

        $upToDateDisplay = "\033[32mphp-cs-fixer is already up to date.\033[39m\n";
        $newMinorDisplay = "\033[32mphp-cs-fixer updated\033[39m (\033[33m{$minor}\033[39m)\n";
        $newMajorDisplay = "\033[32mphp-cs-fixer updated\033[39m (\033[33m{$major}\033[39m)\n";
        $majorInfoNoMinorDisplay = <<<OUTPUT
\033[32mA new major version of php-cs-fixer is available\033[39m (\033[33m{$major}\033[39m)
\033[32mBefore upgrading please read\033[39m https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/{$major}/UPGRADE.md
\033[32mIf you are ready to upgrade run this command with\033[39m \033[33m-f\033[39m
\033[32mChecking for new minor/patch version...\033[39m
\033[32mNo minor update for php-cs-fixer.\033[39m

OUTPUT;
        $majorInfoNewMinorDisplay = <<<OUTPUT
\033[32mA new major version of php-cs-fixer is available\033[39m (\033[33m{$major}\033[39m)
\033[32mBefore upgrading please read\033[39m https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/{$major}/UPGRADE.md
\033[32mIf you are ready to upgrade run this command with\033[39m \033[33m-f\033[39m
\033[32mChecking for new minor/patch version...\033[39m
\033[32mphp-cs-fixer updated\033[39m (\033[33m{$minor}\033[39m)

OUTPUT;

        return [
            // no new version available
            [Application::VERSION, Application::VERSION, [], true, $currentContents, $upToDateDisplay],
            [Application::VERSION, Application::VERSION, [], false, $currentContents, $upToDateDisplay],
            [Application::VERSION, Application::VERSION, ['--force' => true], true, $currentContents, $upToDateDisplay],
            [Application::VERSION, Application::VERSION, ['-f' => true], false, $currentContents, $upToDateDisplay],
            [Application::VERSION, Application::VERSION, ['--force' => true], true, $currentContents, $upToDateDisplay],
            [Application::VERSION, Application::VERSION, ['-f' => true], false, $currentContents, $upToDateDisplay],

            // new minor version available
            [$minor, $minor, [], true, $minorContents, $newMinorDisplay],
            [$minor, $minor, ['--force' => true], true, $minorContents, $newMinorDisplay],
            [$minor, $minor, ['-f' => true], true, $minorContents, $newMinorDisplay],
            [$minor, $minor, [], false, $minorContents, $newMinorDisplay],
            [$minor, $minor, ['--force' => true], false, $minorContents, $newMinorDisplay],
            [$minor, $minor, ['-f' => true], false, $minorContents, $newMinorDisplay],

            // new major version available
            [$major, Application::VERSION, [], true, $currentContents, $majorInfoNoMinorDisplay],
            [$major, Application::VERSION, [], false, $currentContents, $majorInfoNoMinorDisplay],
            [$major, Application::VERSION, ['--force' => true], true, $majorContents, $newMajorDisplay],
            [$major, Application::VERSION, ['-f' => true], false, $majorContents, $newMajorDisplay],
            [$major, Application::VERSION, ['--force' => true], true, $majorContents, $newMajorDisplay],
            [$major, Application::VERSION, ['-f' => true], false, $majorContents, $newMajorDisplay],

            // new minor version and new major version available
            [$major, $minor, [], true, $minorContents, $majorInfoNewMinorDisplay],
            [$major, $minor, [], false, $minorContents, $majorInfoNewMinorDisplay],
            [$major, $minor, ['--force' => true], true, $majorContents, $newMajorDisplay],
            [$major, $minor, ['-f' => true], false, $majorContents, $newMajorDisplay],
            [$major, $minor, ['--force' => true], true, $majorContents, $newMajorDisplay],
            [$major, $minor, ['-f' => true], false, $majorContents, $newMajorDisplay],

            // weird/unexpected versions
            ['v0.1.0', 'v0.1.0', [], true, $currentContents, $upToDateDisplay],
            ['v0.1.0', 'v0.1.0', [], false, $currentContents, $upToDateDisplay],
            ['v0.1.0', 'v0.1.0', ['--force' => true], true, $currentContents, $upToDateDisplay],
            ['v0.1.0', 'v0.1.0', ['-f' => true], false, $currentContents, $upToDateDisplay],
            ['v0.1.0', 'v0.1.0', ['--force' => true], true, $currentContents, $upToDateDisplay],
            ['v0.1.0', 'v0.1.0', ['-f' => true], false, $currentContents, $upToDateDisplay],
            ['v0.1.0', null, [], true, $currentContents, $upToDateDisplay],
            ['v0.1.0', null, [], false, $currentContents, $upToDateDisplay],
            ['v0.1.0', null, ['--force' => true], true, $currentContents, $upToDateDisplay],
            ['v0.1.0', null, ['-f' => true], false, $currentContents, $upToDateDisplay],
            ['v0.1.0', null, ['--force' => true], true, $currentContents, $upToDateDisplay],
            ['v0.1.0', null, ['-f' => true], false, $currentContents, $upToDateDisplay],
            ['v0.1.0', Application::VERSION, [], true, $currentContents, $upToDateDisplay],
            ['v0.1.0', Application::VERSION, [], false, $currentContents, $upToDateDisplay],
            ['v0.1.0', Application::VERSION, ['--force' => true], true, $currentContents, $upToDateDisplay],
            ['v0.1.0', Application::VERSION, ['-f' => true], false, $currentContents, $upToDateDisplay],
            ['v0.1.0', Application::VERSION, ['--force' => true], true, $currentContents, $upToDateDisplay],
            ['v0.1.0', Application::VERSION, ['-f' => true], false, $currentContents, $upToDateDisplay],
            [Application::VERSION, 'v0.1.0', [], true, $currentContents, $upToDateDisplay],
            [Application::VERSION, 'v0.1.0', [], false, $currentContents, $upToDateDisplay],
            [Application::VERSION, 'v0.1.0', ['--force' => true], true, $currentContents, $upToDateDisplay],
            [Application::VERSION, 'v0.1.0', ['-f' => true], false, $currentContents, $upToDateDisplay],
            [Application::VERSION, 'v0.1.0', ['--force' => true], true, $currentContents, $upToDateDisplay],
            [Application::VERSION, 'v0.1.0', ['-f' => true], false, $currentContents, $upToDateDisplay],
        ];
    }

    /**
     * @param string $latestVersionSuccess
     * @param string $latestMinorVersionSuccess
     * @param array  $input
     * @param bool   $decorated
     *
     * @dataProvider provideExecuteWhenNotAbleToGetLatestVersionsCases
     */
    public function testExecuteWhenNotAbleToGetLatestVersions(
        $latestVersionSuccess,
        $latestMinorVersionSuccess,
        array $input,
        $decorated
    ) {
        $versionChecker = $this->prophesize(\PhpCsFixer\Console\SelfUpdate\NewVersionCheckerInterface::class);

        $newMajorVersion = $this->getNewMajorVersion();
        $versionChecker->getLatestVersion()->will(function () use ($latestVersionSuccess, $newMajorVersion) {
            if ($latestVersionSuccess) {
                return $newMajorVersion;
            }

            throw new \RuntimeException('Foo.');
        });

        $newMinorVersion = $this->getNewMinorVersion();
        $versionChecker
            ->getLatestVersionOfMajor($this->getCurrentMajorVersion())
            ->will(function () use ($latestMinorVersionSuccess, $newMinorVersion) {
                if ($latestMinorVersionSuccess) {
                    return $newMinorVersion;
                }

                throw new \RuntimeException('Foo.');
            })
        ;

        $command = new SelfUpdateCommand(
            $versionChecker->reveal(),
            $this->createToolInfo(),
            $this->prophesize(\PhpCsFixer\PharCheckerInterface::class)->reveal()
        );

        $commandTester = $this->execute($command, $input, $decorated);

        $this->assertDisplay(
            "\033[37;41mUnable to determine newest version: Foo.\033[39;49m\n",
            $commandTester
        );
        $this->assertSame(1, $commandTester->getStatusCode());
    }

    public function provideExecuteWhenNotAbleToGetLatestVersionsCases()
    {
        return [
            [false, false, [], true],
            [false, false, ['--force' => true], true],
            [false, false, ['-f' => true], true],
            [false, false, [], false],
            [false, false, ['--force' => true], false],
            [false, false, ['-f' => true], false],
            [true, false, [], true],
            [true, false, ['--force' => true], true],
            [true, false, ['-f' => true], true],
            [true, false, [], false],
            [true, false, ['--force' => true], false],
            [true, false, ['-f' => true], false],
            [false, true, [], true],
            [false, true, ['--force' => true], true],
            [false, true, ['-f' => true], true],
            [false, true, [], false],
            [false, true, ['--force' => true], false],
            [false, true, ['-f' => true], false],
        ];
    }

    /**
     * @param array $input
     * @param bool  $decorated
     *
     * @dataProvider provideExecuteWhenNotInstalledAsPharCases
     */
    public function testExecuteWhenNotInstalledAsPhar(array $input, $decorated)
    {
        $command = new SelfUpdateCommand(
            $this->prophesize(\PhpCsFixer\Console\SelfUpdate\NewVersionCheckerInterface::class)->reveal(),
            $this->createToolInfo(false),
            $this->prophesize(\PhpCsFixer\PharCheckerInterface::class)->reveal()
        );

        $commandTester = $this->execute($command, $input, $decorated);

        $this->assertDisplay(
            "\033[37;41mSelf-update is available only for PHAR version.\033[39;49m\n",
            $commandTester
        );
        $this->assertSame(1, $commandTester->getStatusCode());
    }

    public function provideExecuteWhenNotInstalledAsPharCases()
    {
        return [
            [[], true],
            [['--force' => true], true],
            [['-f' => true], true],
            [[], false],
            [['--force' => true], false],
            [['-f' => true], false],
        ];
    }

    private function execute(Command $command, array $input, $decorated)
    {
        $application = new Application();
        $application->add($command);

        $input = ['command' => $command->getName()] + $input;

        $commandTester = new CommandTester($command);

        $realPath = $_SERVER['argv'][0];
        $_SERVER['argv'][0] = $this->getToolPath();

        $commandTester->execute($input, ['decorated' => $decorated]);

        $_SERVER['argv'][0] = $realPath;

        return $commandTester;
    }

    private function assertDisplay($expectedDisplay, CommandTester $commandTester)
    {
        if (!$commandTester->getOutput()->isDecorated()) {
            $expectedDisplay = preg_replace("/\033\\[(\\d+;)*\\d+m/", '', $expectedDisplay);
        }

        // TODO drop preg_replace() usage when symfony/console is bumped
        $cleanDisplay = function ($display) {
            return preg_replace("/\033\\[39(;49)?m/", "\033[0m", $display);
        };

        $this->assertSame(
            $cleanDisplay($expectedDisplay),
            $cleanDisplay($commandTester->getDisplay(true))
        );
    }

    private function createToolInfo($isInstalledAsPhar = true)
    {
        $root = $this->root;

        $toolInfo = $this->prophesize(\PhpCsFixer\ToolInfoInterface::class);
        $toolInfo->isInstalledAsPhar()->willReturn($isInstalledAsPhar);
        $toolInfo
            ->getPharDownloadUri(Argument::type('string'))
            ->will(function (array $arguments) use ($root) {
                return "{$root->url()}/{$arguments[0]}.phar";
            })
        ;

        return $toolInfo->reveal();
    }

    private function getToolPath()
    {
        return "{$this->root->url()}/php-cs-fixer";
    }

    private function getCurrentMajorVersion()
    {
        return (int) preg_replace('/^v?(\d+).*$/', '$1', Application::VERSION);
    }

    private function getNewMinorVersion()
    {
        return "{$this->getCurrentMajorVersion()}.999.0";
    }

    private function getNewMajorVersion()
    {
        return ($this->getCurrentMajorVersion() + 1).'.0.0';
    }
}

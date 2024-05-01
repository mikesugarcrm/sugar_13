<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\PackageManager;

require_once 'include/SugarCache/SugarCache.php';

use Sugarcrm\Sugarcrm\PackageManager\Entity\PackageManifest;
use Sugarcrm\Sugarcrm\PackageManager\Exception\IncompatibleSugarFlavorException;
use Sugarcrm\Sugarcrm\PackageManager\Exception\IncompatibleSugarVersionException;
use Sugarcrm\Sugarcrm\PackageManager\Exception\InvalidPackageException;
use Sugarcrm\Sugarcrm\PackageManager\Exception\NotAcceptableTypeException;
use Sugarcrm\Sugarcrm\PackageManager\Exception\OnlyPackagePatchTypeAcceptableException;
use Sugarcrm\Sugarcrm\PackageManager\Exception\PackageExistsException;
use Sugarcrm\Sugarcrm\PackageManager\Exception\PackageManagerException;
use Sugarcrm\Sugarcrm\PackageManager\Exception\PackageNewerExistsException;
use Sugarcrm\Sugarcrm\PackageManager\Factory\UpgradeHistoryFactory;
use Sugarcrm\Sugarcrm\PackageManager\File\PackageZipFile;
use Sugarcrm\Sugarcrm\PackageManager\PackageManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ModuleScanner;
use UpgradeHistory;
use ModuleInstaller;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\PackageManager\PackageManager
 */
class PackageManagerTest extends TestCase
{
    /**
     * @var MockObject|PackageZipFile
     */
    protected $zipFile;

    /**
     * @var MockObject|ModuleScanner
     */
    protected $moduleScanner;

    /**
     * @var MockObject|ModuleInstaller
     */
    protected $moduleInstaller;

    /**
     * @var MockObject|PackageManifest
     */
    protected $manifest;

    /**
     * @var MockObject|UpgradeHistoryFactory
     */
    protected $upgradeHistoryFactory;

    /**
     * @var MockObject|UpgradeHistory
     */
    protected $upgradeHistory;

    protected function setUp(): void
    {
        $this->zipFile = $this->createMock(PackageZipFile::class);
        $this->moduleScanner = $this->createMock(ModuleScanner::class);
        $this->moduleInstaller = $this->createMock(ModuleInstaller::class);
        $this->manifest = $this->createMock(PackageManifest::class);
        $this->upgradeHistoryFactory = $this->createMock(UpgradeHistoryFactory::class);
        $this->upgradeHistory = $this->createMock(UpgradeHistory::class);

        $GLOBALS['log'] = $this->getMockBuilder(\LoggerManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $GLOBALS['sugar_config']['upload_dir'] = 'upload';
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['log']);
        unset($GLOBALS['mi_remove_tables']);
        unset($GLOBALS['mi_overwrite_files']);
        unset($GLOBALS['sugar_config']['upload_dir']);
    }

    /**
     * @covers ::uploadPackageFromFile
     */
    public function testUploadPackageFromFileModuleScannerPackageScanException()
    {
        $packageManager = new PackageManager();

        $packageManager->setIsPackageScanEnabled(true);
        $packageManager->setModuleScanner($this->moduleScanner);

        $this->zipFile->expects($this->once())->method('extractPackage');
        $this->zipFile->expects($this->once())->method('getPackageDir')->willReturn('upgrades/temp/xcv');

        $this->moduleScanner->expects($this->once())->method('scanPackage')->with('upgrades/temp/xcv');
        $this->moduleScanner->expects($this->once())->method('hasIssues')->willReturn(true);
        $this->moduleScanner->expects($this->once())->method('getFormattedIssues')->willReturn(['formatted errors']);

        $this->expectException(InvalidPackageException::class);

        $packageManager->uploadPackageFromFile($this->zipFile, PackageManifest::PACKAGE_TYPE_MODULE);
    }

    /**
     * @covers ::uploadPackageFromFile
     */
    public function testUploadPackageFromFileSugarVersionIsNotAcceptableException()
    {
        $packageManager = $this->createPartialMock(
            PackageManager::class,
            [
                'checkAndGetManifestFromFile',
            ]
        );

        $packageManager->setIsPackageScanEnabled(false);

        $this->zipFile->expects($this->once())
            ->method('getPackageManifestFile')
            ->willReturn('upgrades/temp/xcv/m.php');

        $packageManager->expects($this->once())
            ->method('checkAndGetManifestFromFile')
            ->with('upgrades/temp/xcv/m.php')
            ->willReturn($this->manifest);

        $this->manifest->expects($this->once())
            ->method('getAcceptableSugarVersions')
            ->willReturn([]);

        $this->expectException(IncompatibleSugarVersionException::class);

        $packageManager->uploadPackageFromFile($this->zipFile, PackageManifest::PACKAGE_TYPE_MODULE);
    }

    /**
     * @covers ::uploadPackageFromFile
     */
    public function testUploadPackageFromFileSugarFlavorIsNotAcceptableException()
    {
        $packageManager = $this->createPartialMock(
            PackageManager::class,
            [
                'checkAndGetManifestFromFile',
            ]
        );

        $packageManager->setIsPackageScanEnabled(false);
        $packageManager->setSugarVersion('10.1.0');
        $packageManager->setSugarFlavor('PRO');

        $this->zipFile->expects($this->once())
            ->method('getPackageManifestFile')
            ->willReturn('upgrades/temp/xcv/m.php');

        $packageManager->expects($this->once())
            ->method('checkAndGetManifestFromFile')
            ->with('upgrades/temp/xcv/m.php')
            ->willReturn($this->manifest);

        $this->manifest->expects($this->once())
            ->method('getAcceptableSugarVersions')
            ->willReturn(['exact_matches' => ['10.1.0']]);

        $this->manifest->expects($this->once())
            ->method('getManifestValue')
            ->with('acceptable_sugar_flavors', [])
            ->willReturn(['ENT']);

        $this->expectException(IncompatibleSugarFlavorException::class);

        $packageManager->uploadPackageFromFile($this->zipFile, PackageManifest::PACKAGE_TYPE_MODULE);
    }

    /**
     * @covers ::uploadPackageFromFile
     */
    public function testUploadPackageFromFileNotAcceptableTypeException()
    {
        $packageManager = $this->createPartialMock(
            PackageManager::class,
            [
                'checkAndGetManifestFromFile',
            ]
        );

        $packageManager->setIsPackageScanEnabled(false);
        $packageManager->setSugarVersion('10.1.0');
        $packageManager->setSugarFlavor('ENT');

        $this->zipFile->expects($this->once())
            ->method('getPackageManifestFile')
            ->willReturn('upgrades/temp/xcv/m.php');

        $packageManager->expects($this->once())
            ->method('checkAndGetManifestFromFile')
            ->with('upgrades/temp/xcv/m.php')
            ->willReturn($this->manifest);

        $this->manifest->expects($this->once())
            ->method('getAcceptableSugarVersions')
            ->willReturn(['regex_matches' => ['^10\.1\.([0-9]+)']]);

        $this->manifest->expects($this->once())
            ->method('getManifestValue')
            ->with('acceptable_sugar_flavors', [])
            ->willReturn(['ENT']);

        $this->manifest->expects($this->once())
            ->method('getPackageType')
            ->willReturn(PackageManifest::PACKAGE_TYPE_MODULE);

        $this->expectException(NotAcceptableTypeException::class);

        $packageManager->uploadPackageFromFile($this->zipFile, PackageManifest::PACKAGE_TYPE_PATCH);
    }

    /**
     * @covers ::uploadPackageFromFile
     */
    public function testUploadPackageFromFileOnlyPackagePatchTypeAcceptableException()
    {
        $packageManager = $this->createPartialMock(
            PackageManager::class,
            [
                'checkAndGetManifestFromFile',
            ]
        );

        $packageManager->setIsPackageScanEnabled(false);
        $packageManager->setSugarVersion('10.1.0');
        $packageManager->setSugarFlavor('ENT');

        $this->zipFile->expects($this->once())
            ->method('getPackageManifestFile')
            ->willReturn('upgrades/temp/xcv/m.php');

        $packageManager->expects($this->once())
            ->method('checkAndGetManifestFromFile')
            ->with('upgrades/temp/xcv/m.php')
            ->willReturn($this->manifest);

        $this->manifest->expects($this->once())
            ->method('getAcceptableSugarVersions')
            ->willReturn(['regex_matches' => ['^10\.1\.([0-9]+)']]);

        $this->manifest->expects($this->once())
            ->method('getManifestValue')
            ->with('acceptable_sugar_flavors', [])
            ->willReturn(['ENT']);

        $this->manifest->expects($this->once())
            ->method('getPackageType')
            ->willReturn(PackageManifest::PACKAGE_TYPE_PATCH);

        $this->expectException(OnlyPackagePatchTypeAcceptableException::class);

        $packageManager->uploadPackageFromFile($this->zipFile, 'default');
    }

    /**
     * @covers ::uploadPackageFromFile
     */
    public function testUploadPackageFromFileNameMatchPackageExistsException()
    {
        $packageManager = $this->createPartialMock(
            PackageManager::class,
            [
                'checkAndGetManifestFromFile',
            ]
        );

        $packageManager->setIsPackageScanEnabled(false);
        $packageManager->setSugarVersion('10.1.0');
        $packageManager->setSugarFlavor('ENT');
        $packageManager->setUpgradeHistoryFactory($this->upgradeHistoryFactory);

        $this->zipFile->expects($this->once())
            ->method('getPackageManifestFile')
            ->willReturn('upgrades/temp/xcv/m.php');

        $packageManager->expects($this->once())
            ->method('checkAndGetManifestFromFile')
            ->with('upgrades/temp/xcv/m.php')
            ->willReturn($this->manifest);

        $this->manifest->expects($this->once())
            ->method('getAcceptableSugarVersions')
            ->willReturn(['regex_matches' => ['^10\.1\.([0-9]+)']]);

        $this->manifest->expects($this->once())
            ->method('getManifestValue')
            ->with('acceptable_sugar_flavors', [])
            ->willReturn(['ENT']);

        $this->manifest->expects($this->once())
            ->method('getPackageType')
            ->willReturn(PackageManifest::PACKAGE_TYPE_MODULE);

        $this->zipFile->expects($this->once())
            ->method('getRelativeZipFilePath')
            ->willReturn('upgrades/temp/xcv/m.zip');

        $this->upgradeHistoryFactory->expects($this->once())
            ->method('createUpgradeHistory')
            ->with(
                $this->isInstanceOf(PackageManifest::class),
                'upgrades/temp/xcv/m.zip',
                UpgradeHistory::STATUS_STAGED
            )->willReturn($this->upgradeHistory);

        $nameMatchUpgradeHistory = $this->createMock(UpgradeHistory::class);
        $nameMatchUpgradeHistory->version = '101';

        $this->upgradeHistory->expects($this->once())
            ->method('checkForExisting')
            ->willReturn($nameMatchUpgradeHistory);
        $this->upgradeHistory->version = '100';

        $this->expectException(PackageNewerExistsException::class);
        $packageManager->uploadPackageFromFile($this->zipFile, PackageManifest::PACKAGE_TYPE_MODULE);
    }

    /**
     * @covers ::uploadPackageFromFile
     */
    public function testUploadPackageFromFile()
    {
        $packageManager = $this->createPartialMock(
            PackageManager::class,
            [
                'checkAndGetManifestFromFile',
                'getUpgradeTypeDir',
                'getUniquePackageFileName',
            ]
        );

        $packageManager->setIsPackageScanEnabled(false);
        $packageManager->setSugarVersion('10.1.0');
        $packageManager->setSugarFlavor('ENT');
        $packageManager->setUpgradeHistoryFactory($this->upgradeHistoryFactory);

        $this->zipFile->expects($this->once())
            ->method('getPackageManifestFile')
            ->willReturn('upgrades/temp/xcv/m.php');

        $packageManager->expects($this->once())
            ->method('checkAndGetManifestFromFile')
            ->with('upgrades/temp/xcv/m.php')
            ->willReturn($this->manifest);

        $this->manifest->expects($this->once())
            ->method('getAcceptableSugarVersions')
            ->willReturn(['regex_matches' => ['^10\.1\.([0-9]+)']]);

        $this->manifest->expects($this->once())
            ->method('getManifestValue')
            ->with('acceptable_sugar_flavors', [])
            ->willReturn(['ENT']);

        $this->manifest->expects($this->exactly(2))
            ->method('getPackageType')
            ->willReturn(PackageManifest::PACKAGE_TYPE_MODULE);

        $this->zipFile->expects($this->once())
            ->method('getRelativeZipFilePath')
            ->willReturn('upgrades/temp/xcv/m.zip');

        $this->upgradeHistoryFactory->expects($this->once())
            ->method('createUpgradeHistory')
            ->with(
                $this->isInstanceOf(PackageManifest::class),
                'upgrades/temp/xcv/m.zip',
                UpgradeHistory::STATUS_STAGED
            )->willReturn($this->upgradeHistory);

        $this->upgradeHistory->expects($this->once())
            ->method('checkForExisting')
            ->willReturn(null);

        $baseUpgradeTypeDir = 'upgrades/' . PackageManifest::PACKAGE_TYPE_MODULE;

        $packageManager->expects($this->once())
            ->method('getUpgradeTypeDir')
            ->with(PackageManifest::PACKAGE_TYPE_MODULE)
            ->willReturn($baseUpgradeTypeDir);

        $packageManager->expects($this->once())
            ->method('getUniquePackageFileName')
            ->with($this->upgradeHistory)
            ->willReturn('m.zip');

        $this->zipFile->expects($this->once())
            ->method('copyZipFileTo')
            ->with($baseUpgradeTypeDir . '/m.zip');

        $this->zipFile->expects($this->once())
            ->method('copyManifestFileTo')
            ->with($baseUpgradeTypeDir . '/m-manifest.php');

        $this->upgradeHistory->expects($this->once())->method('save');
        $this->upgradeHistory->deleted = 1;
        $this->upgradeHistory->expects($this->once())->method('mark_undeleted');

        $packageManager->uploadPackageFromFile($this->zipFile, PackageManifest::PACKAGE_TYPE_MODULE);
    }

    /**
     * @covers ::deletePackage
     */
    public function testDeletePackageNoPackageException()
    {
        $packageManager = $this->createPartialMock(PackageManager::class, ['createPackageZipFile']);
        $this->upgradeHistory->deleted = 1;

        $this->expectException(PackageManagerException::class);
        $packageManager->deletePackage($this->upgradeHistory);
    }

    /**
     * @covers ::deletePackage
     */
    public function testDeletePackageTryToRemoveInstalledPackageException()
    {
        $packageManager = $this->createPartialMock(PackageManager::class, ['createPackageZipFile']);
        $this->upgradeHistory->deleted = 0;
        $this->upgradeHistory->status = UpgradeHistory::STATUS_INSTALLED;

        $this->expectException(PackageManagerException::class);
        $packageManager->deletePackage($this->upgradeHistory);
    }

    /**
     * @covers ::deletePackage
     */
    public function testDeletePackage()
    {
        $packageManager = $this->createPartialMock(PackageManager::class, ['createPackageZipFile']);
        $packageManager->setBaseUpgradeDir('upgrades');

        $this->upgradeHistory->deleted = 0;
        $this->upgradeHistory->status = UpgradeHistory::STATUS_STAGED;
        $this->upgradeHistory
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn('upgrades/module/m.zip');

        $packageManager->expects($this->once())
            ->method('createPackageZipFile')
            ->with('upgrades/module/m.zip', 'upgrades')
            ->willReturn($this->zipFile);

        $this->zipFile->expects($this->once())->method('removeSelfWithMetadata');
        $this->upgradeHistory->expects($this->once())->method('mark_deleted');

        $packageManager->deletePackage($this->upgradeHistory);
    }

    /**
     * @covers ::installPackage
     */
    public function testInstallPackageNoPackageException()
    {
        $packageManager = new PackageManager();
        $this->upgradeHistory->deleted = 1;

        $this->expectException(PackageManagerException::class);
        $packageManager->installPackage($this->upgradeHistory);
    }

    /**
     * @covers ::installPackage
     */
    public function testInstallPackageAlreadyInstalledPackageException()
    {
        $packageManager = new PackageManager();
        $this->upgradeHistory->status = UpgradeHistory::STATUS_INSTALLED;

        $this->expectException(PackageManagerException::class);
        $packageManager->installPackage($this->upgradeHistory);
    }

    /**
     * @covers ::installPackage
     */
    public function testInstallPackageNoDependencyException()
    {
        $packageManager = $this->createPartialMock(PackageManager::class, [
            'validateManifest',
            'createPackageZipFile',
        ]);

        $this->upgradeHistory->status = UpgradeHistory::STATUS_STAGED;
        $this->upgradeHistory->expects($this->once())->method('getPackageManifest')->willReturn($this->manifest);

        $this->manifest->expects($this->once())
            ->method('getPackageType')
            ->willReturn(PackageManifest::PACKAGE_TYPE_MODULE);

        $packageManager->expects($this->once())
            ->method('validateManifest')
            ->with($this->isInstanceOf(PackageManifest::class), PackageManifest::PACKAGE_TYPE_MODULE);

        $this->upgradeHistory->expects($this->once())
            ->method('getListNotInstalledDependencies')
            ->willReturn(['not_installed_id_name']);

        $this->expectException(PackageManagerException::class);
        $packageManager->installPackage($this->upgradeHistory);
    }

    /**
     * @covers ::installPackage
     */
    public function testInstallPackageClearInstall()
    {
        $packageManager = $this->createPartialMock(PackageManager::class, [
            'validateManifest',
            'createPackageZipFile',
            'getModuleInstaller',
        ]);
        $packageManager->setBaseTempDir($baseTempDir = 'upgrades/temp');
        $packageManager->setSilent($silent = true);
        $packageManager->expects($this->once())->method('getModuleInstaller')->willReturn($this->moduleInstaller);

        $this->upgradeHistory->status = UpgradeHistory::STATUS_STAGED;
        $this->upgradeHistory->expects($this->once())->method('getPackageManifest')->willReturn($this->manifest);

        $this->manifest->expects($this->exactly(2))
            ->method('getPackageType')
            ->willReturn(PackageManifest::PACKAGE_TYPE_MODULE);

        $packageManager->expects($this->once())
            ->method('validateManifest')
            ->with($this->isInstanceOf(PackageManifest::class), PackageManifest::PACKAGE_TYPE_MODULE);

        $this->upgradeHistory->expects($this->once())
            ->method('getListNotInstalledDependencies')
            ->willReturn([]);

        $this->upgradeHistory->expects($this->once())
            ->method('getFileName')
            ->willReturn($fileName = 'upgrades/module/m.zip');

        $packageManager->expects($this->once())
            ->method('createPackageZipFile')
            ->with($fileName, $baseTempDir)
            ->willReturn($this->zipFile);

        $this->zipFile->expects($this->once())->method('extractPackage');
        $this->zipFile->expects($this->exactly(2))
            ->method('runPackageScript')
            ->withConsecutive(
                [PackageZipFile::PRE_INSTALL_FILE, $silent],
                [PackageZipFile::POST_INSTALL_FILE, $silent],
            );
        $this->upgradeHistory->expects($this->once())
            ->method('getPreviousInstalledVersion')
            ->willReturn(null);
        $this->upgradeHistory->expects($this->once())
            ->method('getPackagePatch')
            ->willReturn([]);

        $this->moduleInstaller->expects($this->once())->method('setPatch');

        $this->zipFile->expects($this->once())
            ->method('getPackageDir')
            ->willReturn($packageDir = $baseTempDir . '/xcv');

        $this->upgradeHistory->expects($this->once())->method('save');

        $history = $packageManager->installPackage($this->upgradeHistory);
        $this->assertEquals(UpgradeHistory::STATUS_INSTALLED, $history->status);
    }

    /**
     * @covers ::installPackage
     */
    public function testInstallPackageUpgradePackage()
    {
        $packageManager = $this->createPartialMock(PackageManager::class, [
            'validateManifest',
            'createPackageZipFile',
            'getModuleInstaller',
            'uninstallPackage',
            'backupSilentValue',
            'restoreSilentValue',
        ]);
        $packageManager->setBaseTempDir($baseTempDir = 'upgrades/temp');
        $packageManager->setSilent($silent = true);
        $packageManager->expects($this->once())->method('getModuleInstaller')->willReturn($this->moduleInstaller);

        $this->upgradeHistory->status = UpgradeHistory::STATUS_STAGED;
        $this->upgradeHistory->expects($this->once())->method('getPackageManifest')->willReturn($this->manifest);

        $this->manifest->expects($this->exactly(2))
            ->method('getPackageType')
            ->willReturn(PackageManifest::PACKAGE_TYPE_MODULE);

        $packageManager->expects($this->once())
            ->method('validateManifest')
            ->with($this->isInstanceOf(PackageManifest::class), PackageManifest::PACKAGE_TYPE_MODULE);

        $this->upgradeHistory->expects($this->once())
            ->method('getListNotInstalledDependencies')
            ->willReturn([]);

        $this->upgradeHistory->expects($this->once())
            ->method('getFileName')
            ->willReturn($fileName = 'upgrades/module/m.zip');

        $previousZipFile = $this->createMock(PackageZipFile::class);
        $previousZipFile->expects($this->once())->method('removeSelfWithMetadata');

        $packageManager->expects($this->exactly(2))
            ->method('createPackageZipFile')
            ->withConsecutive(
                [$fileName, $baseTempDir],
                ['previousFileName', $baseTempDir]
            )
            ->willReturnOnConsecutiveCalls($this->zipFile, $previousZipFile);

        $this->zipFile->expects($this->once())->method('extractPackage');
        $this->zipFile->expects($this->exactly(2))
            ->method('runPackageScript')
            ->withConsecutive(
                [PackageZipFile::PRE_INSTALL_FILE, $silent],
                [PackageZipFile::POST_INSTALL_FILE, $silent],
            );

        $previousUpgradeHistoryManifest = $this->createMock(PackageManifest::class);
        $previousUpgradeHistoryManifest->expects($this->once())->method('shouldTablesBeRemoved')->willReturn(true);

        $previousUpgradeHistory = $this->createMock(UpgradeHistory::class);
        $previousUpgradeHistory->version = 'previous';
        $previousUpgradeHistory->expects($this->once())->method('getFileName')->willReturn('previousFileName');
        $previousUpgradeHistory->expects($this->once())->method('mark_deleted');
        $previousUpgradeHistory->expects($this->once())
            ->method('getPackageManifest')
            ->willReturn($previousUpgradeHistoryManifest);

        $this->upgradeHistory->expects($this->once())
            ->method('getPreviousInstalledVersion')
            ->willReturn($previousUpgradeHistory);

        $this->upgradeHistory->expects($this->once())
            ->method('getPackagePatch')
            ->willReturn([]);
        $this->moduleInstaller->expects($this->once())->method('setPatch');

        $this->manifest->expects($this->once())
            ->method('getManifestValue')
            ->with('uninstall_before_upgrade', false)
            ->willReturn(true);

        $packageManager->expects($this->once())->method('backupSilentValue')->willReturnSelf();

        $packageManager->expects($this->once())
            ->method('uninstallPackage')
            ->with($previousUpgradeHistory, true);

        $packageManager->expects($this->once())->method('restoreSilentValue');

        $this->zipFile->expects($this->once())
            ->method('getPackageDir')
            ->willReturn($packageDir = $baseTempDir . '/xcv');
        $this->moduleInstaller->expects($this->once())->method('install')->with($packageDir, true, 'previous');

        $this->upgradeHistory->expects($this->once())->method('save');

        $history = $packageManager->installPackage($this->upgradeHistory);
        $this->assertEquals(UpgradeHistory::STATUS_INSTALLED, $history->status);
    }

    /**
     * @covers ::uninstallPackage
     */
    public function testUninstallPackageNoPackageException()
    {
        $packageManager = new PackageManager();
        $this->upgradeHistory->deleted = 1;

        $this->expectException(PackageManagerException::class);
        $packageManager->uninstallPackage($this->upgradeHistory, true);
    }

    /**
     * @covers ::uninstallPackage
     */
    public function testUninstallPackagePackageNotInstalledException()
    {
        $packageManager = new PackageManager();
        $this->upgradeHistory->status = UpgradeHistory::STATUS_STAGED;

        $this->expectException(PackageManagerException::class);
        $packageManager->uninstallPackage($this->upgradeHistory, true);
    }

    /**
     * @covers ::uninstallPackage
     */
    public function testUninstallPackagePackageIsUninstallableException()
    {
        $packageManager = new PackageManager();
        $this->upgradeHistory->status = UpgradeHistory::STATUS_INSTALLED;
        $this->upgradeHistory->expects($this->once())->method('getPackageManifest')->willReturn($this->manifest);
        $this->manifest->expects($this->once())->method('isPackageUninstallable')->willReturn(false);

        $this->expectException(PackageManagerException::class);
        $packageManager->uninstallPackage($this->upgradeHistory, true);
    }

    /**
     * @covers ::uninstallPackage
     */
    public function testUninstallPackage()
    {
        $packageManager = $this->createPartialMock(PackageManager::class, [
            'createPackageZipFile',
            'getModuleInstaller',
        ]);
        $packageManager->setBaseTempDir($baseTempDir = 'upgrades/temp');
        $packageManager->setSilent($silent = true);

        $this->upgradeHistory->status = UpgradeHistory::STATUS_INSTALLED;
        $this->upgradeHistory->expects($this->once())->method('getPackageManifest')->willReturn($this->manifest);
        $this->manifest->expects($this->once())->method('isPackageUninstallable')->willReturn(true);

        $this->upgradeHistory->expects($this->once())
            ->method('getFileName')
            ->willReturn($fileName = $baseTempDir . '/m.zip');

        $packageManager->expects($this->once())
            ->method('createPackageZipFile')
            ->with($fileName, $baseTempDir)
            ->willReturn($this->zipFile);

        $this->zipFile->expects($this->once())->method('extractPackage');

        $this->manifest->expects($this->exactly(2))
            ->method('getPackageType')
            ->willReturn(PackageManifest::PACKAGE_TYPE_PATCH);

        $this->zipFile->expects($this->exactly(2))
            ->method('runPackageScript')
            ->withConsecutive(
                [PackageZipFile::PRE_UNINSTALL_FILE, $silent],
                [PackageZipFile::POST_UNINSTALL_FILE, $silent],
            );
        $packageManager->expects($this->once())->method('getModuleInstaller')->willReturn($this->moduleInstaller);
        $this->upgradeHistory->expects($this->once())
            ->method('getPackagePatch')
            ->willReturn([]);
        $this->moduleInstaller->expects($this->once())->method('setPatch');

        $this->zipFile->expects($this->once())
            ->method('getPackageDir')
            ->willReturn($packageDir = $baseTempDir . '/xcv');

        $this->moduleInstaller->expects($this->once())->method('uninstall');

        $this->upgradeHistory->expects($this->once())->method('save');

        $history = $packageManager->uninstallPackage($this->upgradeHistory, true);
        $this->assertEquals(UpgradeHistory::STATUS_STAGED, $history->status);
    }

    /**
     * @covers ::enablePackage
     */
    public function testEnablePackageNoPackageException()
    {
        $packageManager = new PackageManager();
        $this->upgradeHistory->deleted = 1;

        $this->expectException(PackageManagerException::class);
        $packageManager->enablePackage($this->upgradeHistory, true);
    }

    /**
     * @covers ::enablePackage
     */
    public function testEnablePackageNotInstalledPackageException()
    {
        $packageManager = new PackageManager();
        $this->upgradeHistory->status = UpgradeHistory::STATUS_STAGED;

        $this->expectException(PackageManagerException::class);
        $packageManager->enablePackage($this->upgradeHistory, true);
    }

    /**
     * @covers ::enablePackage
     */
    public function testEnablePackageAlreadyEnabledPackageException()
    {
        $packageManager = new PackageManager();
        $this->upgradeHistory->status = UpgradeHistory::STATUS_INSTALLED;
        $this->upgradeHistory->expects($this->once())->method('isPackageEnabled')->willReturn(true);

        $this->expectException(PackageManagerException::class);
        $packageManager->enablePackage($this->upgradeHistory, true);
    }

    /**
     * @covers ::enablePackage
     */
    public function testEnablePackageNoModuleTypeException()
    {
        $packageManager = new PackageManager();
        $this->upgradeHistory->status = UpgradeHistory::STATUS_INSTALLED;
        $this->upgradeHistory->expects($this->once())->method('isPackageEnabled')->willReturn(false);

        $this->upgradeHistory->expects($this->once())
            ->method('getPackageManifest')
            ->willReturn($this->manifest);

        $this->manifest->expects($this->once())
            ->method('getPackageType')
            ->willReturn(PackageManifest::PACKAGE_TYPE_PATCH);

        $this->expectException(PackageManagerException::class);
        $packageManager->enablePackage($this->upgradeHistory, true);
    }

    /**
     * @covers ::enablePackage
     */
    public function testEnablePackage()
    {
        $packageManager = $this->createPartialMock(PackageManager::class, [
            'createPackageZipFile',
            'getModuleInstaller',
        ]);
        $packageManager->setBaseTempDir($baseTempDir = 'upgrades/temp');

        $this->upgradeHistory->status = UpgradeHistory::STATUS_INSTALLED;
        $this->upgradeHistory->expects($this->once())->method('isPackageEnabled')->willReturn(false);

        $this->upgradeHistory->expects($this->once())
            ->method('getPackageManifest')
            ->willReturn($this->manifest);

        $this->manifest->expects($this->once())
            ->method('getPackageType')
            ->willReturn(PackageManifest::PACKAGE_TYPE_MODULE);

        $this->upgradeHistory->expects($this->once())
            ->method('getFileName')
            ->willReturn($fileName = $baseTempDir . '/m.zip');

        $packageManager->expects($this->once())
            ->method('createPackageZipFile')
            ->with($fileName, $baseTempDir)
            ->willReturn($this->zipFile);
        $this->zipFile->expects($this->once())->method('extractPackage');

        $packageManager->expects($this->once())->method('getModuleInstaller')->willReturn($this->moduleInstaller);

        $this->upgradeHistory->expects($this->once())
            ->method('getPackagePatch')
            ->willReturn([]);
        $this->moduleInstaller->expects($this->once())->method('setPatch');

        $this->zipFile->expects($this->once())
            ->method('getPackageDir')
            ->willReturn($packageDir = $baseTempDir . '/xcv');

        $this->moduleInstaller->expects($this->once())
            ->method('enable')
            ->with($packageDir);
        $this->upgradeHistory->expects($this->once())->method('save');
        $history = $packageManager->enablePackage($this->upgradeHistory, true);
        $this->assertEquals(1, $history->enabled);
    }

    /**
     * @covers ::disablePackage
     */
    public function testDisablePackageNoPackageException()
    {
        $packageManager = new PackageManager();
        $this->upgradeHistory->deleted = 1;

        $this->expectException(PackageManagerException::class);
        $packageManager->disablePackage($this->upgradeHistory, true);
    }

    /**
     * @covers ::disablePackage
     */
    public function testDisablePackageNotInstalledPackageException()
    {
        $packageManager = new PackageManager();
        $this->upgradeHistory->status = UpgradeHistory::STATUS_STAGED;

        $this->expectException(PackageManagerException::class);
        $packageManager->disablePackage($this->upgradeHistory, true);
    }

    /**
     * @covers ::disablePackage
     */
    public function testDisablePackageAlreadyDisabledPackageException()
    {
        $packageManager = new PackageManager();
        $this->upgradeHistory->status = UpgradeHistory::STATUS_INSTALLED;
        $this->upgradeHistory->expects($this->once())->method('isPackageEnabled')->willReturn(false);

        $this->expectException(PackageManagerException::class);
        $packageManager->disablePackage($this->upgradeHistory, true);
    }

    /**
     * @covers ::disablePackage
     */
    public function testDisablePackageNoModuleTypeException()
    {
        $packageManager = new PackageManager();
        $this->upgradeHistory->status = UpgradeHistory::STATUS_INSTALLED;
        $this->upgradeHistory->expects($this->once())->method('isPackageEnabled')->willReturn(true);

        $this->upgradeHistory->expects($this->once())
            ->method('getPackageManifest')
            ->willReturn($this->manifest);

        $this->manifest->expects($this->once())
            ->method('getPackageType')
            ->willReturn(PackageManifest::PACKAGE_TYPE_PATCH);

        $this->expectException(PackageManagerException::class);
        $packageManager->disablePackage($this->upgradeHistory, true);
    }

    /**
     * @covers ::disablePackage
     */
    public function testDisablePackage()
    {
        $packageManager = $this->createPartialMock(PackageManager::class, [
            'createPackageZipFile',
            'getModuleInstaller',
        ]);
        $packageManager->setBaseTempDir($baseTempDir = 'upgrades/temp');

        $this->upgradeHistory->status = UpgradeHistory::STATUS_INSTALLED;
        $this->upgradeHistory->expects($this->once())->method('isPackageEnabled')->willReturn(true);

        $this->upgradeHistory->expects($this->once())
            ->method('getPackageManifest')
            ->willReturn($this->manifest);

        $this->manifest->expects($this->once())
            ->method('getPackageType')
            ->willReturn(PackageManifest::PACKAGE_TYPE_MODULE);

        $this->upgradeHistory->expects($this->once())
            ->method('getFileName')
            ->willReturn($fileName = $baseTempDir . '/m.zip');

        $packageManager->expects($this->once())
            ->method('createPackageZipFile')
            ->with($fileName, $baseTempDir)
            ->willReturn($this->zipFile);
        $this->zipFile->expects($this->once())->method('extractPackage');

        $packageManager->expects($this->once())->method('getModuleInstaller')->willReturn($this->moduleInstaller);

        $this->upgradeHistory->expects($this->once())
            ->method('getPackagePatch')
            ->willReturn([]);
        $this->moduleInstaller->expects($this->once())->method('setPatch');

        $this->zipFile->expects($this->once())
            ->method('getPackageDir')
            ->willReturn($packageDir = $baseTempDir . '/xcv');

        $this->moduleInstaller->expects($this->once())
            ->method('disable')
            ->with($packageDir);
        $this->upgradeHistory->expects($this->once())->method('save');
        $history = $packageManager->disablePackage($this->upgradeHistory, true);
        $this->assertEquals(0, $history->enabled);
    }
}

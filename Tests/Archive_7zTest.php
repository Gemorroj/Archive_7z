<?php
require_once 'Archive/7z.php';

class Archive_7zTest extends PHPUnit_Framework_TestCase
{
    protected $cliPath = 'c:\_SOFT_\Universal Extractor\bin\7z.exe';
    protected $tmpDir;

    /**
     * @var Archive_7z
     */
    protected $mock;

    protected function setUp()
    {
        $this->tmpDir = dirname(__FILE__) . '/tmp';
        $this->mock = $this->getMock('Archive_7z', null, array('fake.7z', $this->cliPath));
    }

    protected function tearDown()
    {
        $this->cleanDir($this->tmpDir);
        touch($this->tmpDir . '/index.html');
    }

    protected function cleanDir($dir)
    {
        $h = opendir($dir);
        while (($file = readdir($h)) !== false) {
            if ($file !== '.' && $file !== '..') {
                if (is_dir($dir . '/' . $file) === true) {
                    $this->cleanDir($dir . '/' . $file);
                    rmdir($dir . '/' . $file);
                } else {
                    unlink($dir . '/' . $file);
                }
            }
        }
        closedir($h);
    }

    public function testSetGetCli()
    {
        $result = $this->mock->setCli($this->cliPath);
        $this->assertInstanceOf('Archive_7z', $result);
        $this->assertEquals(realpath($this->cliPath), $this->mock->getCli());

    }

    public function testSetCliFail()
    {
        $this->setExpectedException('Archive_7z_Exception');
        $this->mock->setCli('./fake_path');
    }


    public function testSetGetFilename()
    {
        $filename = '/custom_path/test.7z';
        $result = $this->mock->setFilename($filename);
        $this->assertInstanceOf('Archive_7z', $result);
        $this->assertEquals($filename, $this->mock->getFilename());
    }

    public function testSetGetOutputDirectory()
    {
        $result = $this->mock->setOutputDirectory($this->tmpDir);
        $this->assertInstanceOf('Archive_7z', $result);
        $this->assertEquals(realpath($this->tmpDir), $this->mock->getOutputDirectory());
    }

    public function testSetGetOutputDirectoryFail()
    {
        $outputDirectory = '/fake_path/test';
        $this->setExpectedException('Archive_7z_Exception');
        $this->mock->setOutputDirectory($outputDirectory);
    }

    public function testSetGetPassword()
    {
        $password = 'passw';
        $result = $this->mock->setPassword($password);
        $this->assertInstanceOf('Archive_7z', $result);
        $this->assertEquals($password, $this->mock->getPassword());
    }

    public function testSetGetOverwriteMode()
    {
        $result = $this->mock->setOverwriteMode(Archive_7z::OVERWRITE_MODE_U);
        $this->assertInstanceOf('Archive_7z', $result);
        $this->assertEquals(Archive_7z::OVERWRITE_MODE_U, $this->mock->getOverwriteMode());
    }


    public function testExtract()
    {
        $obj = new Archive_7z(dirname(__FILE__) . '/test.7z', $this->cliPath);
        $obj->setOutputDirectory($this->tmpDir);
        $obj->extract();
    }

    public function testExtractPasswd()
    {
        $obj = new Archive_7z(dirname(__FILE__) . '/testPasswd.7z', $this->cliPath);
        $obj->setOutputDirectory($this->tmpDir);
        $obj->setPassword('123');
        $obj->extract();
    }

    public function testExtractOverwrite()
    {
        mkdir($this->tmpDir . '/test');
        $sourceFile = dirname(__FILE__) . '/test.txt';
        $targetFile = $this->tmpDir . '/test/test.txt';
        $archiveFile = dirname(__FILE__) . '/testArchive.txt';

        $obj = new Archive_7z(dirname(__FILE__) . '/test.7z', $this->cliPath);
        $obj->setOutputDirectory($this->tmpDir);


        $obj->setOverwriteMode(Archive_7z::OVERWRITE_MODE_A);
        copy($sourceFile, $targetFile);
        $obj->extract();
        $this->assertFileEquals($archiveFile, $targetFile);


        $obj->setOverwriteMode(Archive_7z::OVERWRITE_MODE_S);
        copy($sourceFile, $targetFile);
        $obj->extract();
        $this->assertFileEquals($sourceFile, $targetFile);


        $obj->setOverwriteMode(Archive_7z::OVERWRITE_MODE_T);
        copy($sourceFile, $targetFile);
        $obj->extract();
        $this->assertFileExists($this->tmpDir . '/test/test_1.txt');
        $this->assertFileEquals($archiveFile, $targetFile);
        $this->assertFileEquals($sourceFile, $this->tmpDir . '/test/test_1.txt');
        unlink($this->tmpDir . '/test/test_1.txt');


        $obj->setOverwriteMode(Archive_7z::OVERWRITE_MODE_U);
        copy($sourceFile, $targetFile);
        $obj->extract();
        $this->assertFileExists($this->tmpDir . '/test/test_1.txt');
        $this->assertFileEquals($sourceFile, $targetFile);
        $this->assertFileEquals($archiveFile, $this->tmpDir . '/test/test_1.txt');
        unlink($this->tmpDir . '/test/test_1.txt');
    }


    public function testExtractEntry()
    {
        $obj = new Archive_7z(dirname(__FILE__) . '/test.7z', $this->cliPath);
        $obj->setOutputDirectory($this->tmpDir);
        $obj->extractEntry('test/2.jpg');
    }

    public function testExtractEntryOverwrite()
    {
        mkdir($this->tmpDir . '/test');
        $sourceFile = dirname(__FILE__) . '/test.txt';
        $targetFile = $this->tmpDir . '/test/test.txt';
        $archiveFile = dirname(__FILE__) . '/testArchive.txt';

        $obj = new Archive_7z(dirname(__FILE__) . '/test.7z', $this->cliPath);
        $obj->setOutputDirectory($this->tmpDir);


        $obj->setOverwriteMode(Archive_7z::OVERWRITE_MODE_A);
        copy($sourceFile, $targetFile);
        $obj->extractEntry('test/test.txt');
        $this->assertFileEquals($archiveFile, $targetFile);


        $obj->setOverwriteMode(Archive_7z::OVERWRITE_MODE_S);
        copy($sourceFile, $targetFile);
        $obj->extractEntry('test/test.txt');
        $this->assertFileEquals($sourceFile, $targetFile);


        $obj->setOverwriteMode(Archive_7z::OVERWRITE_MODE_T);
        copy($sourceFile, $targetFile);
        $obj->extractEntry('test/test.txt');
        $this->assertFileExists($this->tmpDir . '/test/test_1.txt');
        $this->assertFileEquals($archiveFile, $targetFile);
        $this->assertFileEquals($sourceFile, $this->tmpDir . '/test/test_1.txt');
        unlink($this->tmpDir . '/test/test_1.txt');


        $obj->setOverwriteMode(Archive_7z::OVERWRITE_MODE_U);
        copy($sourceFile, $targetFile);
        $obj->extractEntry('test/test.txt');
        $this->assertFileExists($this->tmpDir . '/test/test_1.txt');
        $this->assertFileEquals($sourceFile, $targetFile);
        $this->assertFileEquals($archiveFile, $this->tmpDir . '/test/test_1.txt');
        unlink($this->tmpDir . '/test/test_1.txt');
    }


    public function testExtractEntryDos()
    {
        $obj = new Archive_7z(dirname(__FILE__) . '/test.7z', $this->cliPath);
        $obj->setOutputDirectory($this->tmpDir);
        $obj->extractEntry(iconv('UTF-8', 'CP866', 'чавес.jpg'));
    }

    public function testExtractEntryPasswd()
    {
        $obj = new Archive_7z(dirname(__FILE__) . '/testPasswd.7z', $this->cliPath);
        $obj->setOutputDirectory($this->tmpDir);
        $obj->setPassword('123');
        $obj->extractEntry('1.jpg');
    }

    public function testGetContentPasswd()
    {
        $obj = new Archive_7z(dirname(__FILE__) . '/testPasswd.7z', $this->cliPath);
        $obj->setPassword('123');
        $result = $obj->getContent('test/test.txt');

        $this->assertEquals(file_get_contents(dirname(__FILE__) . '/testArchive.txt'), $result);
    }

    public function testGetEntriesPasswd()
    {
        $obj = new Archive_7z(dirname(__FILE__) . '/testPasswd.7z', $this->cliPath);
        $obj->setPassword('123');
        $result = $obj->getEntries();

        $this->assertTrue(is_array($result));
        $this->assertCount(5, $result); // 4 file + 1 directory
        $this->assertInstanceOf('Archive_7z_Entry', $result[0]);
    }

    public function testGetEntryPasswd()
    {
        $obj = new Archive_7z(dirname(__FILE__) . '/testPasswd.7z', $this->cliPath);
        $obj->setPassword('123');
        $result = $obj->getEntry('test/test.txt');

        $this->assertInstanceOf('Archive_7z_Entry', $result);
    }

    public function testAddEntryFullPathPasswd()
    {
        //copy(dirname(__FILE__) . '/test.7z', $this->tmpDir . '/test.7z');
        copy(dirname(__FILE__) . '/test.txt', $this->tmpDir . '/file.txt');

        $obj = new Archive_7z($this->tmpDir . '/test.7z', $this->cliPath);
        $obj->setPassword('111');
        $obj->addEntry(realpath($this->tmpDir . '/file.txt'), false, false);
        $result = $obj->getEntry('file.txt');
        $this->assertInstanceOf('Archive_7z_Entry', $result);
        $this->assertEquals('file.txt', $result->getPath());

        $new = new Archive_7z($this->tmpDir . '/test.7z', $this->cliPath);
        $this->setExpectedException('Archive_7z_Exception');
        $new->getContent('file.txt');
    }

    public function testAddEntryFullPath()
    {
        //copy(dirname(__FILE__) . '/test.7z', $this->tmpDir . '/test.7z');
        copy(dirname(__FILE__) . '/test.txt', $this->tmpDir . '/file.txt');

        $obj = new Archive_7z($this->tmpDir . '/test.7z', $this->cliPath);
        $obj->addEntry(realpath($this->tmpDir . '/file.txt'), false, false);
        $result = $obj->getEntry('file.txt');
        $this->assertInstanceOf('Archive_7z_Entry', $result);
        $this->assertEquals('file.txt', $result->getPath());
    }

    public function testAddEntryLocalPath()
    {
        //copy(dirname(__FILE__) . '/test.7z', $this->tmpDir . '/test.7z');
        copy(dirname(__FILE__) . '/test.txt', $this->tmpDir . '/test.txt');
        $localPath = basename(dirname(__FILE__)) . '/' . basename($this->tmpDir) . '/test.txt';

        $obj = new Archive_7z($this->tmpDir . '/test.7z', $this->cliPath);
        $obj->addEntry($localPath, false, true);
        $result = $obj->getEntry($localPath);
        $this->assertInstanceOf('Archive_7z_Entry', $result);
        $this->assertEquals($localPath, $result->getPath());
    }

    public function testAddEntryLocalPathSubFiles()
    {
        mkdir($this->tmpDir . '/test');
        copy(dirname(__FILE__) . '/test.txt', $this->tmpDir . '/test/test.txt');
        $localPath = basename(dirname(__FILE__)) . '/' . basename($this->tmpDir);

        $obj = new Archive_7z($this->tmpDir . '/test.7z', $this->cliPath);
        $obj->addEntry($localPath, true, true);
        $result = $obj->getEntry($localPath);
        $this->assertInstanceOf('Archive_7z_Entry', $result);
        $this->assertEquals($localPath, $result->getPath());
    }

    public function testAddEntryFullPathSubFiles()
    {
        mkdir($this->tmpDir . '/test');
        copy(dirname(__FILE__) . '/test.txt', $this->tmpDir . '/test/test.txt');

        $obj = new Archive_7z($this->tmpDir . '/test.7z', $this->cliPath);
        $obj->addEntry(realpath($this->tmpDir), true, false);
        $result = $obj->getEntry(basename($this->tmpDir));
        $this->assertInstanceOf('Archive_7z_Entry', $result);
        $this->assertEquals(basename($this->tmpDir), $result->getPath());
    }

    public function testDelEntry()
    {
        copy(dirname(__FILE__) . '/test.7z', $this->tmpDir . '/test.7z');
        $obj = new Archive_7z($this->tmpDir . '/test.7z', $this->cliPath);
        $obj->delEntry('test/test.txt');
        $this->assertNull($obj->getEntry('test/test.txt'));
    }

    public function testDelEntryPasswd()
    {
        copy(dirname(__FILE__) . '/testPasswd.7z', $this->tmpDir . '/test.7z');
        $obj = new Archive_7z($this->tmpDir . '/test.7z', $this->cliPath);
        $obj->setPassword('123');
        $obj->delEntry('test/test.txt');
        $this->assertNull($obj->getEntry('test/test.txt'));
    }

    public function testDelEntryPasswdFail()
    {
        copy(dirname(__FILE__) . '/testPasswd.7z', $this->tmpDir . '/test.7z');
        $obj = new Archive_7z($this->tmpDir . '/test.7z', $this->cliPath);
        $this->setExpectedException('Archive_7z_Exception');
        $obj->delEntry('test/test.txt');
    }
}
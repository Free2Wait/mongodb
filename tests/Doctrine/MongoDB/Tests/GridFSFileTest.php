<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\GridFSFile;

class GridFSFileTest extends \Doctrine\ODM\MongoDB\Tests\BaseTest
{
    public function testSetAndGetGridFSFile()
    {
        $path = __DIR__.'/GridFSFileTest.php';
        $file = $this->getTestGridFSFile($path);
        $mockPHPGridFSFile = $this->getMockPHPGridFSFile();
        $file->setGridFSFile($mockPHPGridFSFile);
        $this->assertEquals($mockPHPGridFSFile, $file->getGridFSFile());
    }

    public function testIsDirty()
    {
        $file = $this->getTestGridFSFile();
        $this->assertFalse($file->isDirty());
        $file->isDirty(true);
        $this->assertTrue($file->isDirty());
        $file->isDirty(false);
        $this->assertFalse($file->isDirty());
    }

    public function testSetAndGetFilename()
    {
        $path = __DIR__.'/GridFSFileTest.php';
        $file = $this->getTestGridFSFile();
        $this->assertFalse($file->isDirty());
        $file->setFilename($path);
        $this->assertTrue($file->isDirty());
        $this->assertFalse($file->hasUnpersistedBytes());
        $this->assertTrue($file->hasUnpersistedFile());
        $this->assertEquals($path, $file->getFilename());
    }

    public function testSetBytes()
    {
        $file = $this->getTestGridFSFile();
        $file->setBytes('bytes');
        $this->assertTrue($file->isDirty());
        $this->assertTrue($file->hasUnpersistedBytes());
        $this->assertFalse($file->hasUnpersistedFile());
        $this->assertEquals('bytes', $file->getBytes());
    }

    public function testWriteWithSetBytes()
    {
        $file = $this->getTestGridFSFile();
        $file->setBytes('bytes');
        $path = '/tmp/doctrine'.__CLASS__.'_write_test';
        $file->write($path);
        $this->assertTrue(file_exists($path));
        $this->assertEquals('bytes', file_get_contents($path));
        unlink($path);
    }

    public function testWriteWithSetFilename()
    {
        $origPath = __DIR__.'/GridFSFileTest.php';
        $file = $this->getTestGridFSFile();
        $file->setFilename($origPath);
        $path = '/tmp/doctrine'.__CLASS__.'_write_test';
        $file->write($path);
        $this->assertTrue(file_exists($path));
        $this->assertEquals(file_get_contents($origPath), file_get_contents($path));
        unlink($path);
    }

    public function testGetSizeWithSetBytes()
    {
        $file = $this->getTestGridFSFile();
        $file->setBytes('bytes');
        $this->assertEquals(5, $file->getSize());
    }

    public function testGetSizeWithSetFilename()
    {
        $file = $this->getTestGridFSFile();
        $file->setFilename(__DIR__.'/Functional/file.txt');
        $this->assertEquals(22, $file->getSize());
    }

    public function testFunctional()
    {
        $path = __DIR__.'/Functional/file.txt';
        $db = $this->dm->getConnection()->selectDB('test_files');
        $gridFS = $db->getGridFS();
        $id = $gridFS->storeFile($path);
        $file = $gridFS->findOne(array('_id' => $id));
        $file = new GridFSFile($file);
        $this->assertFalse($file->isDirty());
        $this->assertEquals($path, $file->getFilename());
        $this->assertEquals(file_get_contents($path), $file->getBytes());
        $this->assertEquals(22, $file->getSize());

        $tmpPath = '/tmp/doctrine'.__CLASS__.'_write_test';
        $file->write($tmpPath);
        $this->assertTrue(file_exists($path));
        $this->assertEquals(file_get_contents($path), file_get_contents($tmpPath));
        unlink($tmpPath);
    }

    private function getMockPHPGridFSFile()
    {
        return $this->getMock('GridFSFile', array(), array(), '', false, false);
    }

    private function getTestGridFSFile($file = null)
    {
        return new GridFSFile($file);
    }
}
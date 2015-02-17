<?php

namespace Alcohol\PasteBundle\Tests\Entity;

use Alcohol\PasteBundle\Entity\Paste;
use Alcohol\PasteBundle\Entity\PasteManager;
use Alcohol\PasteBundle\Util\HashUtils;

class PasteManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @testdox Calling create() throws a StorageException if the data cannot be persisted to the redis storage.
     * @expectedException \Alcohol\PasteBundle\Exception\StorageException
     */
    public function testCreateThrowsStorageException()
    {
        $hash = new HashUtils();

        $redis = $this->getRedisMock();

        $redis
            ->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(0))
        ;

        $redis
            ->expects($this->once())
            ->method('set')
            ->will($this->returnValue(0))
        ;

        /**
         * @var \Predis\Client $redis
         * @var \Alcohol\PasteBundle\Util\HashUtils $hash
         */
        $manager = new PasteManager($redis, $hash);

        $manager->create('dummy');
    }

    /**
     * @testdox Calling create() attempts to persist the data to the redis storage.
     */
    public function testCreate()
    {
        $paste = new Paste('code', 'body', 'token');

        $hash = $this->getHashMock();

        $hash
            ->expects($this->at(0))
            ->method('generate')
            ->will($this->returnValue('code'))
        ;

        $hash
            ->expects($this->at(1))
            ->method('generate')
            ->with($this->equalTo(10))
            ->will($this->returnValue('token'))
        ;

        $redis = $this->getRedisMock();

        $redis
            ->expects($this->once())
            ->method('exists')
            ->with(
                $this->equalTo('paste:' . $paste->getCode())
            )
            ->will($this->returnValue(0))
        ;

        $redis
            ->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo('paste:' . $paste->getCode()),
                $this->equalTo(serialize($paste)),
                $this->equalTo('EX'),
                $this->anything(),
                $this->equalTo('NX')
            )
            ->will($this->returnValue(1))
        ;

        /**
         * @var \Predis\Client $redis
         * @var \Alcohol\PasteBundle\Util\HashUtils $hash
         */
        $manager = new PasteManager($redis, $hash);

        $this->assertInstanceOf('Alcohol\PasteBundle\Entity\Paste', $manager->create('body'));
    }

    /**
     * @testdox Calling update() throws a TokenException if the token is not valid.
     * @expectedException \Alcohol\PasteBundle\Exception\TokenException
     */
    public function testUpdateThrowsTokenException()
    {
        $paste = new Paste('code', 'body', 'token');

        $hash = $this->getHashMock();

        $hash
            ->expects($this->once())
            ->method('compare')
            ->will($this->returnValue(false))
        ;

        $redis = $this->getRedisMock();

        /**
         * @var \Predis\Client $redis
         * @var \Alcohol\PasteBundle\Util\HashUtils $hash
         */
        $manager = new PasteManager($redis, $hash);

        $manager->update($paste, 'dummy');
    }

    /**
     * @testdox Calling update() throws a StorageException if data cannot be persisted to the redis storage.
     * @expectedException \Alcohol\PasteBundle\Exception\StorageException
     */
    public function testUpdateThrowsStorageException()
    {
        $hash = $this->getHashMock();

        $hash
            ->expects($this->once())
            ->method('compare')
            ->will($this->returnValue(true))
        ;

        $redis = $this->getRedisMock();

        $redis
            ->expects($this->once())
            ->method('set')
            ->will($this->returnValue(0))
        ;

        $paste = new Paste('code', 'body', 'token');

        /**
         * @var \Predis\Client $redis
         * @var \Alcohol\PasteBundle\Util\HashUtils $hash
         */
        $manager = new PasteManager($redis, $hash);

        $manager->update($paste, 'token');
    }

    /**
     * @testdox Calling update() attempts to persist the data to the redis storage using the NX flag.
     */
    public function testUpdate()
    {
        $paste = new Paste('code', 'body', 'token');

        $hash = $this->getHashMock();

        $hash
            ->expects($this->once())
            ->method('compare')
            ->will($this->returnValue(true))
        ;

        $redis = $this->getRedisMock();

        $redis
            ->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo('paste:' . $paste->getCode()),
                $this->equalTo(serialize($paste)),
                $this->equalTo('EX'),
                $this->anything(),
                $this->equalTo('XX')
            )
            ->will($this->returnValue(1))
        ;

        /**
         * @var \Predis\Client $redis
         * @var \Alcohol\PasteBundle\Util\HashUtils $hash
         */
        $manager = new PasteManager($redis, $hash);

        $this->assertEquals($paste, $manager->update($paste, 'token'));
    }

    /**
     * @testdox Calling delete() throws a TokenException if the token is not valid.
     * @expectedException \Alcohol\PasteBundle\Exception\TokenException
     */
    public function testDeleteThrowsTokenException()
    {
        $hash = $this->getHashMock();

        $hash
            ->expects($this->once())
            ->method('compare')
            ->will($this->returnValue(false))
        ;

        $redis = $this->getRedisMock();

        $paste = new Paste('code', 'body', 'token');

        /**
         * @var \Predis\Client $redis
         * @var \Alcohol\PasteBundle\Util\HashUtils $hash
         */
        $manager = new PasteManager($redis, $hash);

        $manager->delete($paste, 'dummy');
    }

    /**
     * @testdox Calling delete() throws a StorageException if the data cannot be removed from the redis storage.
     * @expectedException \Alcohol\PasteBundle\Exception\StorageException
     */
    public function testDeleteThrowsStorageException()
    {
        $hash = $this->getHashMock();

        $hash
            ->expects($this->once())
            ->method('compare')
            ->will($this->returnValue(true))
        ;

        $redis = $this->getRedisMock();

        $redis
            ->expects($this->once())
            ->method('del')
            ->will($this->returnValue(0))
        ;

        $paste = new Paste('code', 'body', 'token');

        /**
         * @var \Predis\Client $redis
         * @var \Alcohol\PasteBundle\Util\HashUtils $hash
         */
        $manager = new PasteManager($redis, $hash);

        $manager->delete($paste, 'token');
    }

    /**
     * @testdox Calling delete() attempts to remove the data from the redis storage.
     */
    public function testDelete()
    {
        $paste = new Paste('code', 'body', 'token');

        $hash = $this->getHashMock();

        $hash
            ->expects($this->once())
            ->method('compare')
            ->will($this->returnValue(true))
        ;

        $redis = $this->getRedisMock();

        $redis
            ->expects($this->once())
            ->method('del')
            ->with($this->equalTo(array('paste:' . $paste->getCode())))
            ->will($this->returnValue(1))
        ;

        /**
         * @var \Predis\Client $redis
         * @var \Alcohol\PasteBundle\Util\HashUtils $hash
         */
        $manager = new PasteManager($redis, $hash);

        $manager->delete($paste, 'token');
    }

    /**
     * @testdox Calling get() throws a StorageException if the paste cannot be found.
     * @expectedException \Alcohol\PasteBundle\Exception\StorageException
     */
    public function testGetThrowsStorageException()
    {
        $hash = $this->getHashMock();

        $redis = $this->getRedisMock();

        $redis
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null))
        ;

        /**
         * @var \Predis\Client $redis
         * @var \Alcohol\PasteBundle\Util\HashUtils $hash
         */
        $manager = new PasteManager($redis, $hash);

        $manager->read('code');
    }

    /**
     * @testdox Calling get() returns a Paste from the redis storage.
     */
    public function testGet()
    {
        $hash = $this->getHashMock();

        $redis = $this->getRedisMock();

        $paste = new Paste('code', 'body', 'token');

        $redis
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(serialize($paste)))
        ;

        /**
         * @var \Predis\Client $redis
         * @var \Alcohol\PasteBundle\Util\HashUtils $hash
         */
        $manager = new PasteManager($redis, $hash);

        $this->assertEquals($paste, $manager->read('code'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getHashMock()
    {
        return $this
            ->getMockBuilder('Alcohol\PasteBundle\Util\HashUtils')
            ->disableOriginalConstructor()
            ->setMethods(['generate', 'compare'])
            ->getMock()
        ;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRedisMock()
    {
        return $this
            ->getMockBuilder('Predis\Client')
            ->disableOriginalConstructor()
            ->setMethods(['exists', 'set', 'get', 'del'])
            ->getMock()
        ;
    }
}
<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Option\Validator;

use Magento\Catalog\Model\Config\Source\Product\Options\Price;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Validator\File;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    /**
     * @var File
     */
    protected $validator;

    /**
     * @var MockObject
     */
    protected $valueMock;

    /**
     * @var MockObject
     */
    protected $localeFormatMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $configMock = $this->createMock(ConfigInterface::class);
        $storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $priceConfigMock = new Price($storeManagerMock);
        $this->localeFormatMock = $this->createMock(FormatInterface::class);

        $config = [
            [
                'label' => 'group label 1',
                'types' => [
                    [
                        'label' => 'label 1.1',
                        'name' => 'name 1.1',
                        'disabled' => false,
                    ],
                ],
            ],
            [
                'label' => 'group label 2',
                'types' => [
                    [
                        'label' => 'label 2.2',
                        'name' => 'name 2.2',
                        'disabled' => true,
                    ],
                ]
            ],
        ];
        $configMock->expects($this->once())->method('getAll')->will($this->returnValue($config));
        $methods = ['getTitle', 'getType', 'getPriceType', 'getPrice', 'getImageSizeX', 'getImageSizeY','__wakeup'];
        $this->valueMock = $this->createPartialMock(Option::class, $methods);
        $this->validator = new File(
            $configMock,
            $priceConfigMock,
            $this->localeFormatMock
        );
    }

    /**
     * @return void
     */
    public function testIsValidSuccess()
    {
        $this->valueMock->expects($this->once())->method('getTitle')->will($this->returnValue('option_title'));
        $this->valueMock->expects($this->exactly(2))->method('getType')->will($this->returnValue('name 1.1'));
        $this->valueMock->method('getPriceType')
            ->willReturn('fixed');
        $this->valueMock->method('getPrice')
            ->willReturn(10);
        $this->valueMock->expects($this->once())->method('getImageSizeX')->will($this->returnValue(10));
        $this->valueMock->expects($this->once())->method('getImageSizeY')->will($this->returnValue(15));
        $this->localeFormatMock->expects($this->at(0))
            ->method('getNumber')
            ->with($this->equalTo(10))
            ->will($this->returnValue(10));
        $this->localeFormatMock
            ->expects($this->at(2))
            ->method('getNumber')
            ->with($this->equalTo(15))
            ->will($this->returnValue(15));
        $this->assertEmpty($this->validator->getMessages());
        $this->assertTrue($this->validator->isValid($this->valueMock));
    }

    /**
     * @return void
     */
    public function testIsValidWithNegativeImageSize()
    {
        $this->valueMock->expects($this->once())->method('getTitle')->will($this->returnValue('option_title'));
        $this->valueMock->expects($this->exactly(2))->method('getType')->will($this->returnValue('name 1.1'));
        $this->valueMock->method('getPriceType')
            ->willReturn('fixed');
        $this->valueMock->method('getPrice')
            ->willReturn(10);
        $this->valueMock->expects($this->once())->method('getImageSizeX')->will($this->returnValue(-10));
        $this->valueMock->expects($this->never())->method('getImageSizeY');
        $this->localeFormatMock->expects($this->at(0))
            ->method('getNumber')
            ->with($this->equalTo(10))
            ->will($this->returnValue(10));
        $this->localeFormatMock
            ->expects($this->at(1))
            ->method('getNumber')
            ->with($this->equalTo(-10))
            ->will($this->returnValue(-10));

        $messages = [
            'option values' => 'Invalid option value',
        ];
        $this->assertFalse($this->validator->isValid($this->valueMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }

    /**
     * @return void
     */
    public function testIsValidWithNegativeImageSizeY()
    {
        $this->valueMock->expects($this->once())->method('getTitle')->will($this->returnValue('option_title'));
        $this->valueMock->expects($this->exactly(2))->method('getType')->will($this->returnValue('name 1.1'));
        $this->valueMock->method('getPriceType')
            ->willReturn('fixed');
        $this->valueMock->method('getPrice')
            ->willReturn(10);
        $this->valueMock->expects($this->once())->method('getImageSizeX')->will($this->returnValue(10));
        $this->valueMock->expects($this->once())->method('getImageSizeY')->will($this->returnValue(-10));
        $this->localeFormatMock->expects($this->at(0))
            ->method('getNumber')
            ->with($this->equalTo(10))
            ->will($this->returnValue(10));
        $this->localeFormatMock
            ->expects($this->at(2))
            ->method('getNumber')
            ->with($this->equalTo(-10))
            ->will($this->returnValue(-10));
        $messages = [
            'option values' => 'Invalid option value',
        ];
        $this->assertFalse($this->validator->isValid($this->valueMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }
}

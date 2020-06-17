<?php


namespace Youtrack;

use AFQ\Block\AndFilterBlock;
use AFQ\Comparison\Between;
use AFQ\Comparison\Equal;
use AFQ\Comparison\In;
use AFQ\Comparison\IsEmpty;
use AFQ\Comparison\IsOpen;
use AFQ\Comparison\NotEmpty;
use AFQ\Comparison\NotEqual;
use AFQ\Comparison\NotIn;
use AFQ\Comparison\WithOutTag;
use AFQ\Comparison\WithTag;
use AFQ\Converter\FilterBlockToYoutrackConverter;
use AFQ\FilterQuery;
use AFQ\Sorting\Sorting;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class FilterBlockToYoutrackConverterTest extends TestCase
{
    /** @var FilterBlockToYoutrackConverter */
    protected $filterBlockToYoutrackConverter;

    protected function setUp(): void
    {
        $this->filterBlockToYoutrackConverter = new FilterBlockToYoutrackConverter();
    }

    public function testConvertValue()
    {
        $method = new ReflectionMethod($this->filterBlockToYoutrackConverter, 'convertValue');
        $method->setAccessible(true);

        $this->assertEquals(
            'someValue',
            $method->invokeArgs($this->filterBlockToYoutrackConverter, ['someValue']),
            'Youtrack convert value'
        );

        $this->assertEquals(
            '{some separated value}',
            $method->invokeArgs($this->filterBlockToYoutrackConverter, ['some separated value']),
            'Youtrack convert value'
        );

        $this->assertEquals(
            'someValue1,someValue2',
            implode(',', $method->invokeArgs(
                $this->filterBlockToYoutrackConverter,
                [['someValue1', 'someValue2']]
            )),
            'Youtrack convert value'
        );

        $this->assertEquals(
            '{some separated value 1},{some separated value 2}',
            implode(',', $method->invokeArgs(
                $this->filterBlockToYoutrackConverter,
                [['some separated value 1', 'some separated value 2']]
            )),
            'Youtrack convert value'
        );
    }

    public function testBetweenOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(new AndFilterBlock([
                new Between('fieldKey', 0, 100)
            ]));

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        $this->assertEquals(
            '(fieldKey: 0 .. 100)',
            $filterQueryString,
            'Youtrack Between operation failed'
        );
    }

    public function testEqualOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(new AndFilterBlock([
                new Equal('fieldKey', 'value')
            ]));

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        $this->assertEquals(
            '(fieldKey: value)',
            $filterQueryString,
            'Youtrack Equal operation failed'
        );
    }

    public function testInOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(new AndFilterBlock([
                new In('fieldKey', ['value1', 'value2', 'value3']),
            ]));

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        $this->assertEquals(
            '(fieldKey: value1,value2,value3)',
            $filterQueryString,
            'Youtrack IN operation failed'
        );
    }

    public function testIsOpenOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(new AndFilterBlock([
                new IsOpen(true),
            ]));

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        $this->assertEquals(
            '(#Незавершенная)',
            $filterQueryString,
            'Youtrack IsOpen (true) operation failed'
        );


        $filterQuery = (new FilterQuery())
            ->setFilterBlock(new AndFilterBlock([
                new IsOpen(false),
            ]));

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        $this->assertEquals(
            '(#Завершенная)',
            $filterQueryString,
            'Youtrack IsOpen (false) operation failed'
        );
    }

    public function testNotEmptyOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(new AndFilterBlock([
                new NotEmpty('fieldKey'),
            ]));

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        $this->assertEquals(
            '(имеет: fieldKey)',
            $filterQueryString,
            'Youtrack NotEmpty operation failed'
        );
    }

    public function testIsEmptyOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(new AndFilterBlock([
                new IsEmpty('fieldKey'),
            ]));

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        $this->assertEquals(
            '(fieldKey: {Нет: fieldkey})',
            $filterQueryString,
            'Youtrack IsEmpty operation failed'
        );
    }

    public function testNotEqualOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(new AndFilterBlock([
                new NotEqual('fieldKey', 'value'),
            ]));

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        $this->assertEquals(
            '(fieldKey: -value)',
            $filterQueryString,
            'Youtrack NotEqual operation failed'
        );
    }

    public function testNotInOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(new AndFilterBlock([
                new NotIn('fieldKey', ['value1','value2']),
            ]));

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        $this->assertEquals(
            '(fieldKey: -value1,-value2)',
            $filterQueryString,
            'Youtrack NotIn operation failed'
        );
    }

    public function testWithOutTagOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(new AndFilterBlock([
                new WithOutTag(['tag1', 'tag2']),
            ]));

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        $this->assertEquals(
            '(тег: -tag1,-tag2)',
            $filterQueryString,
            'Youtrack WithOutTag operation failed'
        );
    }

    public function testWithTagOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(new AndFilterBlock([
                new WithTag(['tag1', 'tag2']),
            ]));

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        $this->assertEquals(
            '(тег: tag1,tag2)',
            $filterQueryString,
            'Youtrack WithTag operation failed'
        );
    }

    public function testSorting()
    {
        $method = new ReflectionMethod($this->filterBlockToYoutrackConverter, 'convertSoring');
        $method->setAccessible(true);

        $sorting = new Sorting([['someKey', Sorting::ASC]]);
        $this->assertEquals(
            'Сортировать: someKey по возр.',
            $method->invokeArgs($this->filterBlockToYoutrackConverter, [$sorting]),
            'Youtrack convert value'
        );

        $sorting = new Sorting([
            ['someKey', Sorting::DESC],
            ['someKey2', Sorting::ASC],
        ]);
        $this->assertEquals(
            'Сортировать: someKey по убыв.,someKey2 по возр.',
            $method->invokeArgs($this->filterBlockToYoutrackConverter, [$sorting]),
            'Youtrack convert value'
        );
    }
}

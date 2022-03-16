<?php

namespace Youtrack;

use AFQ\Block\AndFilterBlock;
use AFQ\Block\OrFilterBlock;
use AFQ\Comparison\Between;
use AFQ\Comparison\CloseDateBetween;
use AFQ\Comparison\CreateDateBetween;
use AFQ\Comparison\Equal;
use AFQ\Comparison\IdIn;
use AFQ\Comparison\IdNotIn;
use AFQ\Comparison\In;
use AFQ\Comparison\IsEmpty;
use AFQ\Comparison\IsOpen;
use AFQ\Comparison\NotEmpty;
use AFQ\Comparison\NotEqual;
use AFQ\Comparison\NotIn;
use AFQ\Comparison\ProjectIn;
use AFQ\Comparison\RawString;
use AFQ\Comparison\UpdateDateBetween;
use AFQ\Comparison\WithOutTag;
use AFQ\Comparison\WithTag;
use AFQ\Converter\FilterBlockToYoutrackConverter;
use AFQ\FilterQuery;
use AFQ\Sorting\Sorting;
use DateTimeImmutable;
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

        self::assertEquals(
            'someValue',
            $method->invokeArgs($this->filterBlockToYoutrackConverter, ['someValue']),
            'Youtrack convert value'
        );

        self::assertEquals(
            '{some separated value}',
            $method->invokeArgs($this->filterBlockToYoutrackConverter, ['some separated value']),
            'Youtrack convert value'
        );

        self::assertEquals(
            'someValue1,someValue2',
            implode(
                ',',
                $method->invokeArgs(
                    $this->filterBlockToYoutrackConverter,
                    [['someValue1', 'someValue2']]
                )
            ),
            'Youtrack convert value'
        );

        self::assertEquals(
            '{some separated value 1},{some separated value 2}',
            implode(
                ',',
                $method->invokeArgs(
                    $this->filterBlockToYoutrackConverter,
                    [['some separated value 1', 'some separated value 2']]
                )
            ),
            'Youtrack convert value'
        );
    }

    public function testBetweenOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new Between('fieldKey', 0, 100),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(fieldKey: 0 .. 100)',
            $filterQueryString,
            'Youtrack Between operation failed'
        );
    }

    public function testEqualOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new Equal('fieldKey', 'value'),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(fieldKey: value)',
            $filterQueryString,
            'Youtrack Equal operation failed'
        );
    }

    public function testInOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new In('fieldKey', ['value1', 'value2', 'value3']),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(fieldKey: value1,value2,value3)',
            $filterQueryString,
            'Youtrack IN operation failed'
        );
    }

    public function testIsOpenOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new IsOpen(true),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(#Незавершенная)',
            $filterQueryString,
            'Youtrack IsOpen (true) operation failed'
        );

        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new IsOpen(false),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(#Завершенная)',
            $filterQueryString,
            'Youtrack IsOpen (false) operation failed'
        );
    }

    public function testNotEmptyOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new NotEmpty('fieldKey'),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(имеет: fieldKey)',
            $filterQueryString,
            'Youtrack NotEmpty operation failed'
        );
    }

    public function testNotEmptyOperationWithHavingSpacesValue()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new NotEmpty('field key'),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(имеет: {field key})',
            $filterQueryString,
            'Youtrack NotEmpty operation failed'
        );
    }

    public function testIsEmptyOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new IsEmpty('fieldKey'),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(имеет: -{fieldkey})',
            $filterQueryString,
            'Youtrack IsEmpty operation failed'
        );
    }

    public function testNotEqualOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new NotEqual('fieldKey', 'value'),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(fieldKey: -value)',
            $filterQueryString,
            'Youtrack NotEqual operation failed'
        );
    }

    public function testNotInOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new NotIn('fieldKey', ['value1', 'value2']),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(fieldKey: -value1,-value2)',
            $filterQueryString,
            'Youtrack NotIn operation failed'
        );
    }

    public function testWithOutTagOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new WithOutTag(['tag1', 'tag2']),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(тег: -tag1,-tag2)',
            $filterQueryString,
            'Youtrack WithOutTag operation failed'
        );
    }

    public function testWithTagOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new WithTag(['tag1', 'tag2']),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(тег: tag1,tag2)',
            $filterQueryString,
            'Youtrack WithTag operation failed'
        );
    }

    public function testUpdateDateBetween()
    {
        $from = mktime(0, 0, 0, 1, 1, 2020);
        $to = mktime(2, 2, 2, 2, 2, 2022);
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new UpdateDateBetween(
                            (new DateTimeImmutable())->setTimestamp($from),
                            (new DateTimeImmutable())->setTimestamp($to)
                        ),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(обновлена: 2020-01-01_00:00 .. 2022-02-02_02:02)',
            $filterQueryString,
            'Youtrack update date between operation failed (from ... to)'
        );
    }

    public function testCreateDateBetween()
    {
        $from = mktime(0, 0, 0, 1, 1, 2020);
        $to = mktime(2, 2, 2, 2, 2, 2022);
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new CreateDateBetween(
                            (new DateTimeImmutable())->setTimestamp($from),
                            (new DateTimeImmutable())->setTimestamp($to)
                        ),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(создана: 2020-01-01_00:00 .. 2022-02-02_02:02)',
            $filterQueryString,
            'Youtrack create date between operation failed (from ... to)'
        );
    }

    public function testCloseDateBetween()
    {
        $from = mktime(0, 0, 0, 1, 1, 2020);
        $to = mktime(2, 2, 2, 2, 2, 2022);
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new CloseDateBetween(
                            (new DateTimeImmutable())->setTimestamp($from),
                            (new DateTimeImmutable())->setTimestamp($to)
                        ),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(дата завершения: 2020-01-01_00:00 .. 2022-02-02_02:02)',
            $filterQueryString,
            'Youtrack close date Between operation failed (from ... to)'
        );
    }

    public function testIdInOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new IdIn(['SITE-1234', 'SITE-4321']),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(id задачи: SITE-1234,SITE-4321)',
            $filterQueryString,
            'Youtrack WithTag operation failed'
        );
    }

    public function testIdNotInOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new IdNotIn(['SITE-1234', 'SITE-4321']),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(id задачи: -SITE-1234,-SITE-4321)',
            $filterQueryString,
            'Youtrack IdNotIn operation failed'
        );
    }

    public function testProjectInOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new ProjectIn(['someProject', 'someProject_2']),
                    ]
                )
            );
        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(проект: someProject,someProject_2)',
            $filterQueryString,
            'Youtrack ProjectIn operation failed'
        );
    }

    public function testRawStringOperation()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new RawString('some query string'),
                    ]
                )
            );
        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(some query string)',
            $filterQueryString,
            'Youtrack RawString operation failed'
        );
    }

    public function testAndFilterBlock()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new Equal('someKey1', 'someValue1'),
                        new Equal('someKey2', 'someValue2'),
                        new AndFilterBlock(
                            [
                                new Equal('someKey3', 'someValue3'),
                                new Equal('someKey4', 'someValue4'),
                            ]
                        ),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(someKey1: someValue1 и someKey2: someValue2 и (someKey3: someValue3 и someKey4: someValue4))',
            $filterQueryString,
            'Youtrack WithTag operation failed'
        );
    }

    public function testOrFilterBlock()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new OrFilterBlock(
                    [
                        new Equal('someKey1', 'someValue1'),
                        new Equal('someKey2', 'someValue2'),
                        new OrFilterBlock(
                            [
                                new Equal('someKey3', 'someValue3'),
                                new Equal('someKey4', 'someValue4'),
                            ]
                        ),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(someKey1: someValue1 или someKey2: someValue2 или (someKey3: someValue3 или someKey4: someValue4))',
            $filterQueryString,
            'Youtrack WithTag operation failed'
        );
    }

    public function testSorting()
    {
        $method = new ReflectionMethod($this->filterBlockToYoutrackConverter, 'convertSoring');
        $method->setAccessible(true);

        $sorting = new Sorting([['someKey', Sorting::ASC]]);
        self::assertEquals(
            'Сортировать: someKey по возр.',
            $method->invokeArgs($this->filterBlockToYoutrackConverter, [$sorting]),
            'Youtrack convert value'
        );

        $sorting = new Sorting(
            [
                ['someKey', Sorting::DESC],
                ['someKey2', Sorting::ASC],
            ]
        );
        self::assertEquals(
            'Сортировать: someKey по убыв.,someKey2 по возр.',
            $method->invokeArgs($this->filterBlockToYoutrackConverter, [$sorting]),
            'Youtrack convert value'
        );
    }

    public function testSortingWithFilter()
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new RawString('some query string'),
                    ]
                )
            )->setSorting(new Sorting(
                [
                    ['someKey', Sorting::DESC],
                    ['someKey2', Sorting::ASC],
                ]
            ))
        ;
        $filterQueryString = $this->filterBlockToYoutrackConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(some query string)  и Сортировать: someKey по убыв.,someKey2 по возр.',
            $filterQueryString,
            'Youtrack sorting with filter error'
        );
    }
}

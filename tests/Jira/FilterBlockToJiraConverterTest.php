<?php

namespace Jira;

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
use AFQ\Converter\FilterBlockToJiraConverter;
use AFQ\FilterQuery;
use AFQ\Sorting\Sorting;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class FilterBlockToJiraConverterTest extends TestCase
{
    /** @var FilterBlockToJiraConverter */
    protected $filterBlockToJiraConverter;

    protected function setUp(): void
    {
        $this->filterBlockToJiraConverter = new FilterBlockToJiraConverter();
    }

    public function testConvertValue(): void
    {
        $method = new ReflectionMethod($this->filterBlockToJiraConverter, 'convertValue');
        $method->setAccessible(true);

        self::assertEquals(
            'someValue',
            $method->invokeArgs($this->filterBlockToJiraConverter, ['someValue']),
            'Jira convert value'
        );

        self::assertEquals(
            '"some separated value"',
            $method->invokeArgs($this->filterBlockToJiraConverter, ['some separated value']),
            'Jira convert value'
        );

        self::assertEquals(
            'someValue1,someValue2',
            implode(
                ',',
                $method->invokeArgs(
                    $this->filterBlockToJiraConverter,
                    [['someValue1', 'someValue2']]
                )
            ),
            'Jira convert value'
        );

        self::assertEquals(
            '"some separated value 1","some separated value 2"',
            implode(
                ',',
                $method->invokeArgs(
                    $this->filterBlockToJiraConverter,
                    [['some separated value 1', 'some separated value 2']]
                )
            ),
            'Jira convert value'
        );
    }

    public function testBetweenOperation(): void
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new Between('fieldKey', 0, 100),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(fieldKey >= 0 and fieldKey <= 100)',
            $filterQueryString,
            'Jira Between operation failed'
        );
    }

    public function testEqualOperation(): void
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new Equal('fieldKey', 'value'),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(fieldKey = value)',
            $filterQueryString,
            'Jira Equal operation failed'
        );
    }

    public function testInOperation(): void
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new In('fieldKey', ['value1', 'value2', 'value3']),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(fieldKey in (value1,value2,value3))',
            $filterQueryString,
            'Jira IN operation failed'
        );
    }

    public function testIsOpenOperation(): void
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new IsOpen(true),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(resolved is empty)',
            $filterQueryString,
            'Jira IsOpen (true) operation failed'
        );

        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new IsOpen(false),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(resolved is not empty)',
            $filterQueryString,
            'Jira IsOpen (false) operation failed'
        );
    }

    public function testNotEmptyOperation(): void
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new NotEmpty('fieldKey'),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(fieldKey is not empty)',
            $filterQueryString,
            'Jira NotEmpty operation failed'
        );
    }

    public function testIsEmptyOperation(): void
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new IsEmpty('fieldKey'),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(fieldKey is empty)',
            $filterQueryString,
            'Jira IsEmpty operation failed'
        );
    }

    public function testNotEqualOperation(): void
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new NotEqual('fieldKey', 'value'),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(fieldKey != value)',
            $filterQueryString,
            'Jira NotEqual operation failed'
        );
    }

    public function testNotInOperation(): void
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new NotIn('fieldKey', ['value1', 'value2']),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(fieldKey not in (value1,value2))',
            $filterQueryString,
            'Jira NotIn operation failed'
        );
    }

    public function testWithOutTagOperation(): void
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new WithOutTag(['tag1', 'tag2']),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(labels not in (tag1,tag2))',
            $filterQueryString,
            'Jira WithOutTag operation failed'
        );
    }

    public function testWithTagOperation(): void
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new WithTag(['tag1', 'tag2']),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(labels in (tag1,tag2))',
            $filterQueryString,
            'Jira WithTag operation failed'
        );
    }

    public function testUpdateDateBetween(): void
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

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(updated >= "2020-01-01 00:00" and updated <= "2022-02-02 02:02")',
            $filterQueryString,
            'Jira update date between operation failed (from ... to)'
        );
    }

    public function testCreateDateBetween(): void
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

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(created >= "2020-01-01 00:00" and created <= "2022-02-02 02:02")',
            $filterQueryString,
            'Jira create date between operation failed (from ... to)'
        );
    }

    public function testCloseDateBetween(): void
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

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(resolved >= "2020-01-01 00:00" and resolved <= "2022-02-02 02:02")',
            $filterQueryString,
            'Jira close date Between operation failed (from ... to)'
        );
    }

    public function testIdInOperation(): void
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new IdIn(['SITE-1234', 'SITE-4321']),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(issueKey in (SITE-1234,SITE-4321))',
            $filterQueryString,
            'Jira WithTag operation failed'
        );
    }

    public function testIdNotInOperation(): void
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new IdNotIn(['SITE-1234', 'SITE-4321']),
                    ]
                )
            );

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(issueKey not in (SITE-1234,SITE-4321))',
            $filterQueryString,
            'Jira IdNotIn operation failed'
        );
    }

    public function testProjectInOperation(): void
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new ProjectIn(['someProject', 'someProject_2']),
                    ]
                )
            );
        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(project in (someProject,someProject_2))',
            $filterQueryString,
            'Jira ProjectIn operation failed'
        );
    }

    public function testRawStringOperation(): void
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock(
                new AndFilterBlock(
                    [
                        new RawString('some query string'),
                    ]
                )
            );
        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(some query string)',
            $filterQueryString,
            'Jira RawString operation failed'
        );
    }

    public function testAndFilterBlock(): void
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

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(someKey1 = someValue1 and someKey2 = someValue2 and (someKey3 = someValue3 and someKey4 = someValue4))',
            $filterQueryString,
            'Jira AndFilterBlock operation failed'
        );
    }

    public function testOrFilterBlock(): void
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

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '(someKey1 = someValue1 or someKey2 = someValue2 or (someKey3 = someValue3 or someKey4 = someValue4))',
            $filterQueryString,
            'Jira WithTag operation failed'
        );
    }

    public function testSorting(): void
    {
        $method = new ReflectionMethod($this->filterBlockToJiraConverter, 'convertSoring');
        $method->setAccessible(true);

        $sorting = new Sorting([['someKey', Sorting::ASC]]);
        self::assertEquals(
            'order by someKey asc',
            $method->invokeArgs($this->filterBlockToJiraConverter, [$sorting]),
            'Jira convert value'
        );

        $sorting = new Sorting(
            [
                ['someKey', Sorting::DESC],
                ['someKey2', Sorting::ASC],
            ]
        );
        self::assertEquals(
            'order by someKey desc,someKey2 asc',
            $method->invokeArgs($this->filterBlockToJiraConverter, [$sorting]),
            'Jira sorting'
        );
    }

    public function testSomeComplexVariants(): void
    {
        $filterQuery = (new FilterQuery())
            ->setFilterBlock
            (
                new OrFilterBlock(
                    [
                        new AndFilterBlock(
                            [
                                new ProjectIn(['SIT']),
                                new UpdateDateBetween(new DateTimeImmutable('2020-01-02'), new DateTimeImmutable('2020-01-20')),
                                new IsOpen(true),
                            ]
                        ),
                        new AndFilterBlock(
                            [
                                new ProjectIn(['SIT']),
                                new CloseDateBetween(new DateTimeImmutable('2020-01-02'), new DateTimeImmutable('2020-01-20')),
                                new IsOpen(false),
                            ]
                        ),
                    ]
                )
            )
            ->setSorting(
                new Sorting(
                    [
                        ['updated', Sorting::DESC],
                        ['resolved', Sorting::ASC],
                    ]
                )
            )
        ;

        $filterQueryString = $this->filterBlockToJiraConverter->convertFilterQuery($filterQuery);
        self::assertEquals(
            '((project in (SIT) and updated >= "2020-01-02 00:00" and updated <= "2020-01-20 00:00" and resolved is empty) or (project in (SIT) and resolved >= "2020-01-02 00:00" and resolved <= "2020-01-20 00:00" and resolved is not empty)) order by updated desc,resolved asc',
            $filterQueryString
        );
    }
}

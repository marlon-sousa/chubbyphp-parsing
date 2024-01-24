<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\StringSchema;
use Chubbyphp\Tests\Parsing\Unit\AbstractTestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\StringSchema
 *
 * @internal
 */
final class StringSchemaTest extends AbstractTestCase
{
    public function testImmutability(): void
    {
        $schema = new StringSchema();

        self::assertNotSame($schema, $schema->transform(static fn (string $output) => $output));
        self::assertNotSame($schema, $schema->default(42));
        self::assertNotSame($schema, $schema->catch(static fn (string $output, ParserErrorException $e) => $output));
        self::assertNotSame($schema, $schema->nullable());
    }

    public function testParseSuccess(): void
    {
        $input = 'test';

        $schema = new StringSchema();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->default($input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new StringSchema())->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new StringSchema();

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.type',
                    'template' => 'Type should be "string", "{{given}}" given',
                    'variables' => [
                        'given' => 'NULL',
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseSuccessWithTransform(): void
    {
        $input = '1';

        $schema = (new StringSchema())->transform(static fn (string $output) => (int) $output);

        self::assertSame((int) $input, $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = (new StringSchema())
            ->catch(function (mixed $input, ParserErrorException $parserErrorException) {
                self::assertNull($input);
                self::assertSame([
                    [
                        'code' => 'string.type',
                        'template' => 'Type should be "string", "{{given}}" given',
                        'variables' => [
                            'given' => 'NULL',
                        ],
                    ],
                ], $this->errorsToSimpleArray($parserErrorException->getErrors()));

                return 'catched';
            })
        ;

        self::assertSame('catched', $schema->parse(null));
    }

    public function testSafeParseSuccess(): void
    {
        $input = 'test';

        $schema = new StringSchema();

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new StringSchema();

        self::assertSame([
            [
                'code' => 'string.type',
                'template' => 'Type should be "string", "{{given}}" given',
                'variables' => [
                    'given' => 'NULL',
                ],
            ],
        ], $this->errorsToSimpleArray($schema->safeParse(null)->exception->getErrors()));
    }

    public function testParseWithValidMin(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->min(4);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidMin(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->min(5);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.min',
                    'template' => 'Min length {{min}}, {{given}} given',
                    'variables' => [
                        'min' => 5,
                        'given' => \strlen($input),
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidMax(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->max(4);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidMax(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->max(3);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.max',
                    'template' => 'Max length {{max}}, {{given}} given',
                    'variables' => [
                        'max' => 3,
                        'given' => \strlen($input),
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidLength(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->length(4);

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidLength(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->length(5);

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.length',
                    'template' => 'Length {{length}}, {{given}} given',
                    'variables' => [
                        'length' => 5,
                        'given' => \strlen($input),
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidContains(): void
    {
        $input = 'example';

        $schema = (new StringSchema())->contains('amp');

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidContains(): void
    {
        $input = 'example';

        $schema = (new StringSchema())->contains('lee');

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.contains',
                    'template' => '"{{given}}" does not contain "{{contain}}"',
                    'variables' => [
                        'given' => $input,
                        'contain' => 'lee',
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidStartsWith(): void
    {
        $input = 'example';

        $schema = (new StringSchema())->startsWith('exa');

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidStartsWith(): void
    {
        $input = 'example';

        $schema = (new StringSchema())->startsWith('xam');

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.startsWith',
                    'template' => '"{{given}}" does not starts with "{{startsWith}}"',
                    'variables' => [
                        'given' => $input,
                        'startsWith' => 'xam',
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidEndsWith(): void
    {
        $input = 'example';

        $schema = (new StringSchema())->endsWith('ple');

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidEndsWith(): void
    {
        $input = 'example';

        $schema = (new StringSchema())->endsWith('mpl');

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.endsWith',
                    'template' => '"{{given}}" does not ends with "{{endsWith}}"',
                    'variables' => [
                        'given' => $input,
                        'endsWith' => 'mpl',
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithRegexWithInvalidPattern(): void
    {
        try {
            (new StringSchema())->regex('test');

            throw new \Exception('code should not be reached');
        } catch (\InvalidArgumentException $e) {
            self::assertSame('Invalid regex "test" given', $e->getMessage());
        }
    }

    public function testParseWithValidRegex(): void
    {
        $input = 'aBcDeFg';

        $schema = (new StringSchema())->regex('/^[a-z]+$/i');

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidRegex(): void
    {
        $input = 'a1B2C3d4';

        $schema = (new StringSchema())->regex('/^[a-z]+$/i');

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.regex',
                    'template' => '"{{given}}" does not regex "{{regex}}"',
                    'variables' => [
                        'given' => $input,
                        'regex' => '/^[a-z]+$/i',
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidEmail(): void
    {
        $input = 'john.doe@example.com';

        $schema = (new StringSchema())->email();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidEmail(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->email();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.email',
                    'template' => 'Invalid email "{{given}}"',
                    'variables' => [
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidIpV4(): void
    {
        $input = '192.168.1.1';

        $schema = (new StringSchema())->ipV4();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidIpV4(): void
    {
        $input = '256.202.56.89';

        $schema = (new StringSchema())->ipV4();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.ip',
                    'template' => 'Invalid ip {{version}} "{{given}}"',
                    'variables' => [
                        'version' => 'v4',
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidIpV6(): void
    {
        $input = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';

        $schema = (new StringSchema())->ipV6();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidIpV6(): void
    {
        $input = '2001:0db8:85a3:0000:0000:8a2e:0370:733g';

        $schema = (new StringSchema())->ipV6();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.ip',
                    'template' => 'Invalid ip {{version}} "{{given}}"',
                    'variables' => [
                        'version' => 'v6',
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidUrl(): void
    {
        $input = 'https://localhost';

        $schema = (new StringSchema())->url();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidUrl(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->url();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.url',
                    'template' => 'Invalid url "{{given}}"',
                    'variables' => [
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidUuidV4(): void
    {
        $input = '960b0533-da17-42d8-a0a4-dd2ab7213caf';

        $schema = (new StringSchema())->uuidV4();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidUuidV4(): void
    {
        $input = '960b0533-da17-52d8-a0a4-dd2ab7213caf';

        $schema = (new StringSchema())->uuidV4();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.uuid',
                    'template' => 'Invalid uuid {{version}} "{{given}}"',
                    'variables' => [
                        'version' => 'v4',
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidUuidV5(): void
    {
        $input = '960b0533-da17-52d8-a0a4-dd2ab7213caf';

        $schema = (new StringSchema())->uuidV5();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseWithInvalidUuidV5(): void
    {
        $input = '960b0533-da17-42d8-a0a4-dd2ab7213caf';

        $schema = (new StringSchema())->uuidV5();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.uuid',
                    'template' => 'Invalid uuid {{version}} "{{given}}"',
                    'variables' => [
                        'version' => 'v5',
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithTrim(): void
    {
        $input = '   test ';

        $schema = (new StringSchema())->trim();

        self::assertSame(trim($input), $schema->parse($input));
    }

    public function testParseWithLower(): void
    {
        $input = 'TEST';

        $schema = (new StringSchema())->lower();

        self::assertSame(strtolower($input), $schema->parse($input));
    }

    public function testParseWithUpper(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->upper();

        self::assertSame(strtoupper($input), $schema->parse($input));
    }

    public function testParseWithValidtoInt(): void
    {
        $input = '42';

        $schema = (new StringSchema())->toInt();

        self::assertSame((int) $input, $schema->parse($input));
    }

    public function testParseWithInvalidtoInt(): void
    {
        $input = '42cars';

        $schema = (new StringSchema())->toInt();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.int',
                    'template' => 'Invalid int "{{given}}"',
                    'variables' => [
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithValidToDateTime(): void
    {
        $input = '2024-01-20T09:15:00+00:00';

        $schema = (new StringSchema())->toDateTime();

        self::assertEquals(new \DateTimeImmutable($input), $schema->parse($input));
    }

    public function testParseWithInvalidToDateTimeWithInvalidMonth(): void
    {
        $input = '2017-13-01';

        $schema = (new StringSchema())->toDateTime();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.datetime',
                    'template' => 'Invalid datetime "{{given}}"',
                    'variables' => [
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithInvalidToDateTimeWithInvalidDay(): void
    {
        $input = '2017-02-31';

        $schema = (new StringSchema())->toDateTime();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.datetime',
                    'template' => 'Invalid datetime "{{given}}"',
                    'variables' => [
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithInvalidToDateTimeWithAllZero(): void
    {
        $input = '0000-00-00';

        $schema = (new StringSchema())->toDateTime();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.datetime',
                    'template' => 'Invalid datetime "{{given}}"',
                    'variables' => [
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }

    public function testParseWithInvalidToDateTimeWithText(): void
    {
        $input = 'test';

        $schema = (new StringSchema())->toDateTime();

        try {
            $schema->parse($input);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertSame([
                [
                    'code' => 'string.datetime',
                    'template' => 'Invalid datetime "{{given}}"',
                    'variables' => [
                        'given' => $input,
                    ],
                ],
            ], $this->errorsToSimpleArray($parserErrorException->getErrors()));
        }
    }
}

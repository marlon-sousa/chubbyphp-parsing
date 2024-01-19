<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Parsing\Unit\Schema;

use Chubbyphp\Parsing\ParserErrorException;
use Chubbyphp\Parsing\Schema\FloatSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Parsing\Schema\AbstractSchema
 * @covers \Chubbyphp\Parsing\Schema\FloatSchema
 *
 * @internal
 */
final class FloatSchemaTest extends TestCase
{
    public function testParseSuccess(): void
    {
        $input = 1.5;

        $schema = new FloatSchema();

        self::assertSame($input, $schema->parse($input));
    }

    public function testParseSuccessWithDefault(): void
    {
        $input = 1.5;

        $schema = (new FloatSchema())->default($input);

        self::assertSame($input, $schema->parse(null));
    }

    public function testParseSuccessWithNullAndNullable(): void
    {
        $schema = (new FloatSchema())->nullable();

        self::assertNull($schema->parse(null));
    }

    public function testParseFailedWithNull(): void
    {
        $schema = new FloatSchema();

        try {
            $schema->parse(null);

            throw new \Exception('code should not be reached');
        } catch (ParserErrorException $parserErrorException) {
            self::assertEquals(['Type should be "float" "NULL" given'], $parserErrorException->getErrors());
        }
    }

    public function testParseSuccessWithTransform(): void
    {
        $input = 1.5;

        $schema = (new FloatSchema())->transform(static fn (float $input) => (string) $input);

        self::assertSame((string) $input, $schema->parse($input));
    }

    public function testParseFailedWithCatch(): void
    {
        $schema = new FloatSchema();

        $schema->catch(static function (mixed $input, ParserErrorException $parserErrorException) {
            self::assertNull($input);
            self::assertSame(['Type should be "float" "NULL" given'], $parserErrorException->getErrors());

            return 'catched';
        })->parse(null);

        self::assertSame('catched', $schema->parse(null));
    }

    public function testSafeParseSuccess(): void
    {
        $input = 1.5;

        $schema = new FloatSchema();

        self::assertSame($input, $schema->safeParse($input)->data);
    }

    public function testSafeParseFailed(): void
    {
        $schema = new FloatSchema();

        self::assertSame(['Type should be "float" "NULL" given'], $schema->safeParse(null)->exception->getErrors());
    }
}

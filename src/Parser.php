<?php

declare(strict_types=1);

namespace Chubbyphp\Parsing;

use Chubbyphp\Parsing\Schema\ArraySchema;
use Chubbyphp\Parsing\Schema\BoolSchema;
use Chubbyphp\Parsing\Schema\DateTimeSchema;
use Chubbyphp\Parsing\Schema\DiscriminatedUnionSchema;
use Chubbyphp\Parsing\Schema\FloatSchema;
use Chubbyphp\Parsing\Schema\IntSchema;
use Chubbyphp\Parsing\Schema\LiteralSchema;
use Chubbyphp\Parsing\Schema\ObjectSchema;
use Chubbyphp\Parsing\Schema\SchemaInterface;
use Chubbyphp\Parsing\Schema\StringSchema;
use Chubbyphp\Parsing\Schema\UnionSchema;

final class Parser
{
    public function array(SchemaInterface $itemSchema): ArraySchema
    {
        return new ArraySchema($itemSchema);
    }

    public function bool(): BoolSchema
    {
        return new BoolSchema();
    }

    public function dateTime(): DateTimeSchema
    {
        return new DateTimeSchema();
    }

    /**
     * @param array<SchemaInterface> $objectSchemas
     */
    public function discriminatedUnion(array $objectSchemas, string $discriminatorFieldName): DiscriminatedUnionSchema
    {
        return new DiscriminatedUnionSchema($objectSchemas, $discriminatorFieldName);
    }

    public function float(): FloatSchema
    {
        return new FloatSchema();
    }

    public function int(): IntSchema
    {
        return new IntSchema();
    }

    public function literal(string $literal): LiteralSchema
    {
        return new LiteralSchema($literal);
    }

    /**
     * @param array<string, SchemaInterface> $fieldSchemas
     * @param class-string                   $classname
     */
    public function object(array $fieldSchemas, string $classname = \stdClass::class): ObjectSchema
    {
        return new ObjectSchema($fieldSchemas, $classname);
    }

    public function string(): StringSchema
    {
        return new StringSchema();
    }

    /**
     * @param array<SchemaInterface> $schemas
     */
    public function union(array $schemas): UnionSchema
    {
        return new UnionSchema($schemas);
    }
}

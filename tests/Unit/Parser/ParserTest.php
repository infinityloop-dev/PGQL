<?php

declare(strict_types=1);

namespace Graphpinator\Tests\Unit\Parser;

final class ParserTest extends \PHPUnit\Framework\TestCase
{
    public function testQuery() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName {}');

        self::assertCount(0, $result->getFragments());
        self::assertCount(0, $result->getOperation()->getVariables());
        self::assertCount(0, $result->getOperation()->getFields());
        self::assertSame('query', $result->getOperation()->getType());
        self::assertSame('queryName', $result->getOperation()->getName());
    }

    public function testMutation() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('mutation mutName {}');

        self::assertCount(0, $result->getFragments());
        self::assertCount(0, $result->getOperation()->getVariables());
        self::assertCount(0, $result->getOperation()->getFields());
        self::assertSame('mutation', $result->getOperation()->getType());
        self::assertSame('mutName', $result->getOperation()->getName());
    }

    public function testSubscription() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('subscription subName {}');

        self::assertCount(0, $result->getFragments());
        self::assertCount(0, $result->getOperation()->getVariables());
        self::assertCount(0, $result->getOperation()->getFields());
        self::assertSame('subscription', $result->getOperation()->getType());
        self::assertSame('subName', $result->getOperation()->getName());
    }

    public function testQueryNoName() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query {}');

        self::assertCount(0, $result->getFragments());
        self::assertCount(0, $result->getOperation()->getVariables());
        self::assertCount(0, $result->getOperation()->getFields());
        self::assertSame('query', $result->getOperation()->getType());
        self::assertNull($result->getOperation()->getName());
    }

    public function testQueryShorthand() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('{}');

        self::assertCount(0, $result->getFragments());
        self::assertCount(0, $result->getOperation()->getVariables());
        self::assertCount(0, $result->getOperation()->getFields());
        self::assertSame('query', $result->getOperation()->getType());
        self::assertNull($result->getOperation()->getName());
    }

    public function testQueryMultiple() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query {} mutation {}');

        // TODO
        self::assertCount(0, $result->getFragments());
    }

    public function testDirective() : void
    {
        $this->expectException(\Exception::class);

        $result = \Graphpinator\Parser\Parser::parseString('query { field @directive(arg1: 123) }');

        // TODO
        self::assertCount(0, $result->getFragments());
    }


    public function testFragment() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('fragment fragmentName on TypeName {} query queryName {}');

        self::assertCount(1, $result->getFragments());
        self::assertArrayHasKey('fragmentName', $result->getFragments());
        self::assertSame('fragmentName', $result->getFragments()->offsetGet('fragmentName')->getName());
        self::assertSame('TypeName', $result->getFragments()->offsetGet('fragmentName')->getTypeCond()->getName());
        self::assertCount(0, $result->getFragments()->offsetGet('fragmentName')->getFields());
        self::assertCount(0, $result->getFragments()->offsetGet('fragmentName')->getFields());
        self::assertCount(0, $result->getOperation()->getVariables());
        self::assertCount(0, $result->getOperation()->getFields());
        self::assertSame('query', $result->getOperation()->getType());
        self::assertSame('queryName', $result->getOperation()->getName());
    }

    public function testNamedFragmentSpread() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query { ... fragmentName } ');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperation()->getFields()->getFragmentSpreads());
        self::assertArrayHasKey(0, $result->getOperation()->getFields()->getFragmentSpreads());
        self::assertInstanceOf(\Graphpinator\Parser\FragmentSpread\NamedFragmentSpread::class, $result->getOperation()->getFields()->getFragmentSpreads()[0]);
        self::assertSame('fragmentName', $result->getOperation()->getFields()->getFragmentSpreads()[0]->getName());
        self::assertCount(0, $result->getOperation()->getFields());
    }

    public function testTypeFragmentSpread() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query { ... on TypeName {} }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperation()->getFields()->getFragmentSpreads());
        self::assertArrayHasKey(0, $result->getOperation()->getFields()->getFragmentSpreads());
        self::assertInstanceOf(\Graphpinator\Parser\FragmentSpread\TypeFragmentSpread::class , $result->getOperation()->getFields()->getFragmentSpreads()[0]);
        self::assertSame('TypeName', $result->getOperation()->getFields()->getFragmentSpreads()[0]->getTypeCond()->getName());
        self::assertCount(0, $result->getOperation()->getFields());
    }

    public function testVariable() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName ($varName: Int) {}');

        self::assertCount(0, $result->getFragments());
        self::assertCount(0, $result->getOperation()->getFields());
        self::assertCount(1, $result->getOperation()->getVariables());
        self::assertArrayHasKey('varName', $result->getOperation()->getVariables());
        self::assertSame('varName', $result->getOperation()->getVariables()->offsetGet('varName')->getName());
        self::assertInstanceOf(\Graphpinator\Parser\TypeRef\NamedTypeRef::class, $result->getOperation()->getVariables()->offsetGet('varName')->getType());
        self::assertSame('Int', $result->getOperation()->getVariables()->offsetGet('varName')->getType()->getName());
        self::assertNull($result->getOperation()->getVariables()->offsetGet('varName')->getDefault());
    }

    public function testVariableDefault() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName ($varName: Int = 3) {}');

        self::assertCount(0, $result->getFragments());
        self::assertCount(0, $result->getOperation()->getFields());
        self::assertCount(1, $result->getOperation()->getVariables());
        self::assertArrayHasKey('varName', $result->getOperation()->getVariables());
        self::assertSame('varName', $result->getOperation()->getVariables()->offsetGet('varName')->getName());
        self::assertInstanceOf(\Graphpinator\Parser\TypeRef\NamedTypeRef::class, $result->getOperation()->getVariables()->offsetGet('varName')->getType());
        self::assertSame('Int', $result->getOperation()->getVariables()->offsetGet('varName')->getType()->getName());
        self::assertSame(3, $result->getOperation()->getVariables()->offsetGet('varName')->getDefault()->getRawValue());
    }

    public function testVariableComplexType() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName ($varName: [Int!]!) {}');

        self::assertCount(0, $result->getFragments());
        self::assertCount(0, $result->getOperation()->getFields());
        self::assertCount(1, $result->getOperation()->getVariables());
        self::assertArrayHasKey('varName', $result->getOperation()->getVariables());
        self::assertSame('varName', $result->getOperation()->getVariables()->offsetGet('varName')->getName());
        self::assertInstanceOf(\Graphpinator\Parser\TypeRef\NotNullRef::class, $result->getOperation()->getVariables()->offsetGet('varName')->getType());
        self::assertInstanceOf(\Graphpinator\Parser\TypeRef\ListTypeRef::class, $result->getOperation()->getVariables()->offsetGet('varName')->getType()->getInnerRef());
        self::assertInstanceOf(\Graphpinator\Parser\TypeRef\NotNullRef::class, $result->getOperation()->getVariables()->offsetGet('varName')->getType()->getInnerRef()->getInnerRef());
        self::assertInstanceOf(\Graphpinator\Parser\TypeRef\NamedTypeRef::class, $result->getOperation()->getVariables()->offsetGet('varName')->getType()->getInnerRef()->getInnerRef()->getInnerRef());
        self::assertSame('Int', $result->getOperation()->getVariables()->offsetGet('varName')->getType()->getInnerRef()->getInnerRef()->getInnerRef()->getName());
    }

    public function testVariableMultiple() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName ($varName: Boolean = true, $varName2: Boolean!) {}');

        self::assertCount(0, $result->getFragments());
        self::assertCount(0, $result->getOperation()->getFields());
        self::assertCount(2, $result->getOperation()->getVariables());
        self::assertArrayHasKey('varName', $result->getOperation()->getVariables());
        self::assertArrayHasKey('varName2', $result->getOperation()->getVariables());
        self::assertSame('varName', $result->getOperation()->getVariables()->offsetGet('varName')->getName());
        self::assertSame('varName2', $result->getOperation()->getVariables()->offsetGet('varName2')->getName());
        self::assertInstanceOf(\Graphpinator\Parser\TypeRef\NamedTypeRef::class, $result->getOperation()->getVariables()->offsetGet('varName')->getType());
        self::assertSame('Boolean', $result->getOperation()->getVariables()->offsetGet('varName')->getType()->getName());
        self::assertTrue($result->getOperation()->getVariables()->offsetGet('varName')->getDefault()->getRawValue());
        self::assertInstanceOf(\Graphpinator\Parser\TypeRef\NotNullRef::class, $result->getOperation()->getVariables()->offsetGet('varName2')->getType());
        self::assertInstanceOf(\Graphpinator\Parser\TypeRef\NamedTypeRef::class, $result->getOperation()->getVariables()->offsetGet('varName2')->getType()->getInnerRef());
        self::assertSame('Boolean', $result->getOperation()->getVariables()->offsetGet('varName2')->getType()->getInnerRef()->getName());
        self::assertNull($result->getOperation()->getVariables()->offsetGet('varName2')->getDefault());
    }

    public function testVariableDefaultList() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName ($varName: [Bool] = [true, false]) {}');

        self::assertCount(0, $result->getFragments());
        self::assertCount(0, $result->getOperation()->getFields());
        self::assertCount(1, $result->getOperation()->getVariables());
        self::assertArrayHasKey('varName', $result->getOperation()->getVariables());
        self::assertSame('varName', $result->getOperation()->getVariables()->offsetGet('varName')->getName());
        self::assertInstanceOf(\Graphpinator\Parser\TypeRef\ListTypeRef::class, $result->getOperation()->getVariables()->offsetGet('varName')->getType());
        self::assertInstanceOf(\Graphpinator\Parser\TypeRef\NamedTypeRef::class, $result->getOperation()->getVariables()->offsetGet('varName')->getType()->getInnerRef());
        self::assertSame('Bool', $result->getOperation()->getVariables()->offsetGet('varName')->getType()->getInnerRef()->getName());
        self::assertSame([true, false], $result->getOperation()->getVariables()->offsetGet('varName')->getDefault()->getRawValue());
    }

    public function testVariableDefaultObject() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName ($varName: InputType = {fieldName: null, fieldName2: {}}) {}');

        self::assertCount(0, $result->getFragments());
        self::assertCount(0, $result->getOperation()->getFields());
        self::assertCount(1, $result->getOperation()->getVariables());
        self::assertArrayHasKey('varName', $result->getOperation()->getVariables());
        self::assertSame('varName', $result->getOperation()->getVariables()->offsetGet('varName')->getName());
        self::assertInstanceOf(\Graphpinator\Parser\TypeRef\NamedTypeRef::class, $result->getOperation()->getVariables()->offsetGet('varName')->getType());
        self::assertSame('InputType', $result->getOperation()->getVariables()->offsetGet('varName')->getType()->getName());
        self::assertSame(['fieldName' => null, 'fieldName2' => []], $result->getOperation()->getVariables()->offsetGet('varName')->getDefault()->getRawValue());
    }

    public function testField() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName { fieldName }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperation()->getFields());
        self::assertCount(0, $result->getOperation()->getVariables());
        self::assertArrayHasKey('fieldName', $result->getOperation()->getFields());
        self::assertSame('fieldName', $result->getOperation()->getFields()->offsetGet('fieldName')->getName());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getAlias());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getArguments());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getFields());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getTypeCondition());
    }

    public function testFieldArguments() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName { fieldName(argName: "argVal") }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(0, $result->getOperation()->getVariables());
        self::assertCount(1, $result->getOperation()->getFields());
        self::assertArrayHasKey('fieldName', $result->getOperation()->getFields());
        self::assertSame('fieldName', $result->getOperation()->getFields()->offsetGet('fieldName')->getName());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getAlias());
        self::assertInstanceOf(\Graphpinator\Parser\Value\NamedValueSet::class, $result->getOperation()->getFields()->offsetGet('fieldName')->getArguments());
        self::assertCount(1, $result->getOperation()->getFields()->offsetGet('fieldName')->getArguments());
        self::assertArrayHasKey('argName', $result->getOperation()->getFields()->offsetGet('fieldName')->getArguments());
        self::assertSame('argVal', $result->getOperation()->getFields()->offsetGet('fieldName')->getArguments()->offsetGet('argName')->getRawValue());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getTypeCondition());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getFields());
    }

    public function testFieldSubfield() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName { fieldName { innerField } }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(0, $result->getOperation()->getVariables());
        self::assertCount(1, $result->getOperation()->getFields());
        self::assertArrayHasKey('fieldName', $result->getOperation()->getFields());
        self::assertSame('fieldName', $result->getOperation()->getFields()->offsetGet('fieldName')->getName());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getAlias());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getArguments());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getTypeCondition());
        self::assertInstanceOf(\Graphpinator\Parser\FieldSet::class, $result->getOperation()->getFields()->offsetGet('fieldName')->getFields());
        self::assertCount(1, $result->getOperation()->getFields()->offsetGet('fieldName')->getFields());
        self::assertArrayHasKey('innerField', $result->getOperation()->getFields()->offsetGet('fieldName')->getFields());
        self::assertSame('innerField', $result->getOperation()->getFields()->offsetGet('fieldName')->getFields()->offsetGet('innerField')->getName());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getFields()->offsetGet('innerField')->getAlias());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getFields()->offsetGet('innerField')->getArguments());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getFields()->offsetGet('innerField')->getTypeCondition());
    }

    public function testFieldAlias() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName { aliasName: fieldName }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperation()->getFields());
        self::assertCount(0, $result->getOperation()->getVariables());
        self::assertArrayHasKey('fieldName', $result->getOperation()->getFields());
        self::assertSame('fieldName', $result->getOperation()->getFields()->offsetGet('fieldName')->getName());
        self::assertSame('aliasName', $result->getOperation()->getFields()->offsetGet('fieldName')->getAlias());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getArguments());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getFields());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getTypeCondition());
    }

    public function testFieldAll() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName { aliasName: fieldName(argName: "argVal") { innerField(argName: 12.34) }}');

        self::assertCount(0, $result->getFragments());
        self::assertCount(0, $result->getOperation()->getVariables());
        self::assertCount(1, $result->getOperation()->getFields());
        self::assertArrayHasKey('fieldName', $result->getOperation()->getFields());
        self::assertSame('fieldName', $result->getOperation()->getFields()->offsetGet('fieldName')->getName());
        self::assertSame('aliasName', $result->getOperation()->getFields()->offsetGet('fieldName')->getAlias());
        self::assertInstanceOf(\Graphpinator\Parser\Value\NamedValueSet::class, $result->getOperation()->getFields()->offsetGet('fieldName')->getArguments());
        self::assertCount(1, $result->getOperation()->getFields()->offsetGet('fieldName')->getArguments());
        self::assertArrayHasKey('argName', $result->getOperation()->getFields()->offsetGet('fieldName')->getArguments());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getTypeCondition());
        self::assertInstanceOf(\Graphpinator\Parser\FieldSet::class, $result->getOperation()->getFields()->offsetGet('fieldName')->getFields());
        self::assertCount(1, $result->getOperation()->getFields()->offsetGet('fieldName')->getFields());
        self::assertArrayHasKey('innerField', $result->getOperation()->getFields()->offsetGet('fieldName')->getFields());
        self::assertSame('innerField', $result->getOperation()->getFields()->offsetGet('fieldName')->getFields()->offsetGet('innerField')->getName());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getFields()->offsetGet('innerField')->getAlias());
        self::assertInstanceOf(\Graphpinator\Parser\Value\NamedValueSet::class, $result->getOperation()->getFields()->offsetGet('fieldName')->getFields()->offsetGet('innerField')->getArguments());
        self::assertCount(1, $result->getOperation()->getFields()->offsetGet('fieldName')->getFields()->offsetGet('innerField')->getArguments());
        self::assertNull($result->getOperation()->getFields()->offsetGet('fieldName')->getFields()->offsetGet('innerField')->getTypeCondition());
    }

    public function invalidDataProvider() : array
    {
        return [
            // empty
            [''],
            ['$var'],
            // no operation
            ['fragment fragmentName on TypeName {}'],
            // no type condition
            ['fragment fragmentName {}'],
            // missing operation type
            ['queryName {}'],
            // missing operation name
            ['query ($var: Int) {}'],
            // invalid variable syntax
            ['query queryName [$var: Int] {}'],
            // invalid fragment spread
            ['query queryName { ... {} }'],
            ['query queryName { ... on {} }'],
            // invalid variable value
            ['query queryName ($var: Int = @dir) {}'],
            ['query queryName ($var: Int = $var2) {}'],
            // missing variable type
            ['query queryName ($var = 123) {}'],
            ['query queryName ($var: = 123) {}'],
            // missing variable name
            ['query queryName (Int = 5) {}'],
            ['query queryName (:Int = 5) {}'],
            // invalid selection set
            ['query queryName { $var }'],
            // missing argument name
            ['query queryName { fieldName(123) }'],
            ['query queryName { fieldName(: 123) }'],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testInvalid(string $input) : void
    {
        $this->expectException(\Exception::class);

        $result = \Graphpinator\Parser\Parser::parseString($input);
    }
}

<?php
namespace DemandwareXml\Test;

use DemandwareXml\Parser;
use DemandwareXml\Parser\{
    AssignmentNodeParser,
    BundleNodeParser,
    BundleSimpleNodeParser,
    CategoryNodeParser,
    CategorySimpleNodeParser,
    ProductNodeParser,
    ProductSimpleNodeParser,
    SetNodeParser,
    SetSimpleNodeParser,
    VariationNodeParser,
    VariationSimpleNodeParser
};
use PHPUnit\Framework\TestCase;
use stdClass;

// note: don't need to parse variants, so no test for those!
// rebuild fixtures: file_put_contents(__DIR__ . '/fixtures/categories.json', json_encode($parser->categories(), JSON_PRETTY_PRINT) . PHP_EOL);
class ParserTest extends TestCase
{
    use FixtureHelper;

    /**
     * @expectedException              \DemandwareXml\XmlException
     * @expectedExceptionMessageRegExp /Fatal: xmlParseEntityRef: no name in invalid-products.xml/
     */
    public function testParserValidateInvalidXml(): void
    {
        (new Parser(__DIR__ . '/fixtures/invalid-products.xml'))->validate();
    }

    /**
     * @expectedException              \DemandwareXml\XmlException
     * @expectedExceptionMessageRegExp /Error: Element '{.*}upc': This element is not expected. Expected is one of \( {.*}step-quantity, {.*}display-name, {.*}short-description, {.*}long-description, {.*}store-receipt-name, {.*}store-tax-class, {.*}store-force-price-flag, {.*}store-non-inventory-flag, {.*}store-non-revenue-flag, {.*}store-non-discountable-flag \). in invalid-schema-products.xml/
     */
    public function testParserValidateInvalidSchemaXml(): void
    {
        (new Parser(__DIR__ . '/fixtures/invalid-schema-products.xml'))->validate();
    }

    /**
     * @expectedException        \DemandwareXml\XMLException
     * @expectedExceptionMessage XML file does not exist: fake-products.xml
     */
    public function testParserValidateFileDoesNotExist(): void
    {
        (new Parser(__DIR__ . '/fixtures/fake-products.xml'))->validate();
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Node parser class "stdClass" must implement DemandwareXml\Parser\NodeParserInterface
     */
    public function testParserInvalidClass(): void
    {
        $parser = new Parser(__DIR__ . '/fixtures/mixed.xml');
        $parser->parse(stdClass::class)->next();
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Node parser class "stdClass" must implement DemandwareXml\Parser\NodeParserInterface
     */
    public function testArrayParserInvalidClass(): void
    {
        $parser = new Parser(__DIR__ . '/fixtures/mixed.xml');
        $parser->parseToArray(['FOOBAR' => stdClass::class]);
    }

    public function testParserValidate(): void
    {
        $this->assertTrue((new Parser(__DIR__ . '/fixtures/products.xml'))->validate());
    }

    public function testEmptyParser(): void
    {
        $parser = new Parser(__DIR__ . '/fixtures/empty.xml');

        $this->assertEmpty(iterator_to_array($parser->parse(AssignmentNodeParser::class)));
        $this->assertEmpty(iterator_to_array($parser->parse(BundleNodeParser::class)));
        $this->assertEmpty(iterator_to_array($parser->parse(CategoryNodeParser::class)));
        $this->assertEmpty(iterator_to_array($parser->parse(ProductNodeParser::class)));
        $this->assertEmpty(iterator_to_array($parser->parse(SetNodeParser::class)));
        $this->assertEmpty(iterator_to_array($parser->parse(VariationNodeParser::class)));
    }

    public function testArrayParser(): void
    {
        $parser  = new Parser(__DIR__ . '/fixtures/mixed.xml');
        $results = $parser->parseToArray([
            'products'    => ProductSimpleNodeParser::class,
            'categories'  => CategorySimpleNodeParser::class,
            'assignments' => AssignmentNodeParser::class,
        ], ['assignments']);

        $this->assertCount(3, $results);
        $this->assertSame($this->loadJsonFixture('mixed-products.json'), $results['products']);
        $this->assertSame($this->loadJsonFixture('mixed-categories.json'), $results['categories']);
        $this->assertSame($this->loadJsonFixture('mixed-assignments.json'), $results['assignments']);
    }

    public function testAssignmentsParser(): void
    {
        $parser = new Parser(__DIR__ . '/fixtures/assignments.xml');

        $assignments = [];

        foreach ($parser->parse(AssignmentNodeParser::class) as $productId => $assignment) {
            $assignments[$productId][] = $assignment;
        }

        $this->assertSame(
            $this->loadJsonFixture('assignments.json'),
            $assignments
        );
    }

    public function testBundlesParser(): void
    {
        $parser = new Parser(__DIR__ . '/fixtures/products.xml');

        $this->assertSame(
            $this->loadJsonFixture('bundles.json'),
            iterator_to_array($parser->parse(BundleNodeParser::class))
        );
    }

    public function testSimpleBundlesParser(): void
    {
        $parser = new Parser(__DIR__ . '/fixtures/products.xml');

        $this->assertSame(
            $this->loadJsonFixture('bundles-simple.json'),
            iterator_to_array($parser->parse(BundleSimpleNodeParser::class))
        );
    }

    public function testCategoriesParser(): void
    {
        $parser = new Parser(__DIR__ . '/fixtures/categories.xml');

        $this->assertSame(
            $this->loadJsonFixture('categories.json'),
            iterator_to_array($parser->parse(CategoryNodeParser::class))
        );
    }

    public function testSimpleCategoriesParser(): void
    {
        $parser = new Parser(__DIR__ . '/fixtures/categories.xml');

        $this->assertSame(
            $this->loadJsonFixture('categories-simple.json'),
            iterator_to_array($parser->parse(CategorySimpleNodeParser::class))
        );
    }

    public function testProductsParser(): void
    {
        $parser = new Parser(__DIR__ . '/fixtures/products.xml');

        $this->assertSame(
            $this->loadJsonFixture('products.json'),
            iterator_to_array($parser->parse(ProductNodeParser::class))
        );
    }

    public function testSimpleProductsParser(): void
    {
        $parser = new Parser(__DIR__ . '/fixtures/products.xml');

        $this->assertSame(
            $this->loadJsonFixture('products-simple.json'),
            iterator_to_array($parser->parse(ProductSimpleNodeParser::class))
        );
    }

    public function testSetsParser(): void
    {
        $parser = new Parser(__DIR__ . '/fixtures/products.xml');

        $this->assertSame(
            $this->loadJsonFixture('sets.json'),
            iterator_to_array($parser->parse(SetNodeParser::class))
        );
    }

    public function testSimpleSetsParser(): void
    {
        $parser = new Parser(__DIR__ . '/fixtures/products.xml');

        $this->assertSame(
            $this->loadJsonFixture('sets-simple.json'),
            iterator_to_array($parser->parse(SetSimpleNodeParser::class))
        );
    }

    public function testVariationsParser(): void
    {
        $parser = new Parser(__DIR__ . '/fixtures/products.xml');

        $this->assertSame(
            $this->loadJsonFixture('variations.json'),
            iterator_to_array($parser->parse(VariationNodeParser::class))
        );
    }

    public function testSimpleVariationsParser(): void
    {
        $parser = new Parser(__DIR__ . '/fixtures/products.xml');

        $this->assertSame(
            $this->loadJsonFixture('variations-simple.json'),
            iterator_to_array($parser->parse(VariationSimpleNodeParser::class))
        );
    }
}

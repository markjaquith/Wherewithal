<?php

namespace Unit;

use MarkJaquith\Wherewithal\{Parser, Config, Structure};
use MarkJaquith\Wherewithal\Contracts\{ParserContract, ConfigContract, StructureContract};
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase {
	private Parser $parser;

	public function setUp(): void {
		$config = (new Config())
			->addOperators('<', '>', '/')
			->addColumn('foo')
			->addColumn('baz');
		$this->parser = new Parser($config);
	}

	public function test_parser_yields_structure() {
		$structure = $this->parser->parse('foo > 0');

		$this->assertInstanceOf(StructureContract::class, $structure);
	}

	public function test_normalizing_queries() {

		$this->assertEquals('foo > 3 and (bar < 2) or (bar > 0)', $this->parser->normalizeQuery('foo > 3 and(bar < 2) or(bar > 0)'));
	}

	public function test_tokens_are_parsed() {
		$this->assertEquals([
			'foo < 0',
			'and',
			'(',
			'bar > 0',
			'or',
			'foo/baz < -22',
			')',
		], $this->parser->splitConjunctions(' foo < 0 and(   bar > 0 or foo/baz < -22   )  '));

	}

	public function getConditions() {
		$results = ['foo', '<', '0'];
		return [
			[...$results, 'foo < 0'],
			[...$results, 'foo< 0'],
			[...$results, 'foo <0'],
			[...$results, 'foo<0'],
		];
	}

	/**
	 * @dataProvider getConditions
	 *
	 * @param $left
	 * @param $comparator
	 * @param $right
	 * @param $input
	 */
	public function test_parses_conditions($left, $comparator, $right, $input) {
		$this->assertEquals([$left, $comparator, $right], $this->parser->parseCondition($input));
	}

	public function test_converts_to_tokens() {
		$this->assertEquals([
			['type' => Parser::TOKEN_COLUMN, 'value' => 'foo'],
			['type' => Parser::TOKEN_OPERATOR, 'value' => '<'],
			['type' => Parser::TOKEN_VALUE, 'value' => '0'],
			['type' => Parser::TOKEN_AND],
			['type' => Parser::TOKEN_GROUP_START],
			['type' => Parser::TOKEN_VALUE, 'value' => 'bar'],
			['type' => Parser::TOKEN_OPERATOR, 'value' => '>'],
			['type' => Parser::TOKEN_VALUE, 'value' => '0'],
			['type' => Parser::TOKEN_OR],
			['type' => Parser::TOKEN_COLUMN, 'value' => 'foo'],
			['type' => Parser::TOKEN_OPERATOR, 'value' => '/'],
			['type' => Parser::TOKEN_COLUMN, 'value' => 'baz'],
			['type' => Parser::TOKEN_OPERATOR, 'value' => '<'],
			['type' => Parser::TOKEN_VALUE, 'value' => '-22'],
			['type' => Parser::TOKEN_GROUP_END],
		], $this->parser->parse('foo < 0 and ( bar > 0 or foo/baz < -22)')->toArray());
	}
}

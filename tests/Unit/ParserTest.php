<?php

namespace Unit;

use MarkJaquith\Wherewithal\{Parser, Config, Structure};
use MarkJaquith\Wherewithal\Contracts\{ParserContract, ConfigContract, StructureContract};
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase {
	public function test_parser_yields_structure() {
		$config = new Config();
		$parser = new Parser($config);
		$structure = $parser->parse('foo > 0');

		$this->assertInstanceOf(StructureContract::class, $structure);
	}

	public function test_normalizing_queries() {
		$config = new Config();
		$config->addComparisons('<', '>');
		$parser = new Parser($config);

		$this->assertEquals('foo > 3 and (bar < 2) or (bar > 0)', $parser->normalizeQuery('foo > 3 and(bar < 2) or(bar > 0)'));
	}

	public function test_tokens_are_parsed() {
		$config = (new Config())
			->addComparisons('<', '>')
			->addColumn('foo')
			->addColumn('baz');
		$parser = new Parser($config);

		$this->assertEquals([
			'foo < 0',
			'and',
			'(',
			'bar > 0',
			'or',
			'foo/baz < -22',
			')',
		], $parser->tokenize(' foo < 0 and(   bar > 0 or foo/baz < -22   )  '));

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
		$config = (new Config())
			->addComparisons('<', '>')
			->addColumn('foo')
			->addColumn('baz');
		$parser = new Parser($config);
		$this->assertEquals([$left, $comparator, $right], $parser->parseComparison($input));
	}
}

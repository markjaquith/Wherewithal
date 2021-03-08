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
		$config = new Config();
		$config->addComparisons('<', '>');
		$parser = new Parser($config);

		$this->assertEquals([
			'foo',
			'<',
			'0',
			'and',
			'(',
			'bar',
			'>',
			'0',
			'or',
			'bar',
			'<',
			'-22',
			')',
		], $parser->tokenize(' foo < 0 and(   bar > 0 or bar < -22   )  '));
	}
}

<?php

namespace Unit;

use MarkJaquith\Wherewithal\{Parser, Config, Token};
use MarkJaquith\Wherewithal\Exceptions\{AdjacentOperatorException,
	EmptyGroupException,
	InvalidConjunctionPlacementException,
	ParenthesesMismatchException};
use MarkJaquith\Wherewithal\Contracts\StructureContract;
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
			new Token(Token::COLUMN, 'foo'),
			new Token(Token::OPERATOR, '<'),
			new Token(Token::VALUE, '0'),
			new Token(Token::AND),
			new Token(Token::GROUP_START),
			new Token(Token::VALUE, 'bar'),
			new Token(Token::OPERATOR, '>'),
			new Token(Token::VALUE, '0'),
			new Token(Token::OR),
			new Token(Token::COLUMN, 'foo'),
			new Token(Token::OPERATOR, '/'),
			new Token(Token::COLUMN, 'baz'),
			new Token(Token::OPERATOR, '<'),
			new Token(Token::VALUE, '-22'),
			new Token(Token::GROUP_END),
		], $this->parser->parse('foo < 0 and ( bar > 0 or foo/baz < -22)')->toArray());
	}

	public function test_throws_parentheses_mismatch_exception() {
		$this->expectException(ParenthesesMismatchException::class);
		$this->parser->parse('foo < 0 and ( ( baz > 0 )');
	}

		public function test_throws_parentheses_mismatch_exception2() {
		$this->expectException(ParenthesesMismatchException::class);
		$this->parser->parse('foo < 0 and ( baz))(( > 0 )');
	}

	public function test_throws_adjacent_operator_exception() {
		$this->expectException(AdjacentOperatorException::class);
		$this->parser->parse('foo < < 0 and baz < 0');
	}

	public function test_throws_empty_group_exception() {
		$this->expectException(EmptyGroupException::class);
		$this->parser->parse('foo < 0 and ()');
	}
}

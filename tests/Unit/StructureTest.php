<?php

namespace Unit;

use MarkJaquith\Wherewithal\Token;
use MarkJaquith\Wherewithal\Structure;
use MarkJaquith\Wherewithal\TokenList;
use MarkJaquith\Wherewithal\Contracts\{TokenContract, StructureContract};
use PHPUnit\Framework\TestCase;

class StructureTest extends TestCase {
	/**
	 * @var TokenContract[]
	 */
	private array $tokens;
	private StructureContract $structure;

	public function setUp(): void {
		$this->tokens = [
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
		];
		$this->structure = new Structure($this->tokens);
}
	public function test_structure_generates_query_and_bindings() {
		$this->assertEquals($this->tokens, $this->structure->toArray());

		$this->assertEquals('foo < ? and ( ? > ? or foo / baz < ? )', (string) $this->structure);
		$this->assertEquals('foo < ? and ( ? > ? or foo / baz < ? )', $this->structure->toString());
		$this->assertEquals(['0', 'bar', '0', '-22'], $this->structure->getBindings());
	}

	public function test_mapping_columns() {
		$fn = fn($col) => ['foo' => 'fooColumn / blahColumn'][$col] ?? $col;
		$this->assertEquals('(fooColumn / blahColumn) < ? and ( ? > ? or (fooColumn / blahColumn) / baz < ? )', $this->structure->mapColumns($fn)->toString());
	}
}

<?php

namespace Unit;

use MarkJaquith\Wherewithal\Token;
use MarkJaquith\Wherewithal\Structure;
use PHPUnit\Framework\TestCase;

class StructureTest extends TestCase {
	public function test_structure_generates_query_and_bindings() {
		$input = [
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
		$structure = new Structure($input);

		$this->assertEquals($input, $structure->toArray());

		$this->assertEquals("foo < ? and ( ? > ? or foo / baz < ? )", (string) $structure);
		$this->assertEquals("foo < ? and ( ? > ? or foo / baz < ? )", $structure->toString());
		$this->assertEquals(['0', 'bar', '0', '-22'], $structure->getBindings());
	}
}

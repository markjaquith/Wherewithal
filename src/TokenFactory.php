<?php

namespace MarkJaquith\Wherewithal;

use MarkJaquith\Wherewithal\Contracts\ConfigContract;

class TokenFactory {
	/**
	 * @var ConfigContract
	 */
	private ConfigContract $config;

	const CONJUNCTIONS = ['(', ')', 'and', 'or'];

	public function __construct(ConfigContract $config) {
		$this->config = $config;
	}

	public function make(string $value): Token {
		$column = $this->config->getColumn(strtolower($value));
		if ($column) {
			return new Token(Token::COLUMN, $column);
		} elseif ($this->config->isOperator($value)) {
			return new Token(Token::OPERATOR, $value);
		} elseif ($this->isConjunction($value)) {
			switch (strtolower($value)) {
				case '(':
					return new Token(Token::GROUP_START);
				case ')':
					return new Token(Token::GROUP_END);
				case 'and':
					return new Token(Token::AND);
				case 'or':
					return new Token(Token::OR);
			}
		}

		return new Token(Token::VALUE, $value);
	}

	public function isConjunction(string $input): bool {
		return in_array(strtolower($input), self::CONJUNCTIONS);
	}
}
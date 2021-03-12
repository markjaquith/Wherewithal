<?php

namespace MarkJaquith\Wherewithal;

use MarkJaquith\Wherewithal\Contracts\ConfigContract;

class TokenFactory {
	/**
	 * @var ConfigContract
	 */
	private ConfigContract $config;

	const CONJUNCTIONS = [
		'(' => Token::GROUP_START,
		')' => Token::GROUP_END,
		'and' => Token::AND,
		'or' => Token::OR,
	];

	public function __construct(ConfigContract $config) {
		$this->config = $config;
	}

	public function make(string $value): Token {
		$value = strtolower($value);
		$column = $this->config->getColumn($value);

		if ($column) {
			return new Token(Token::COLUMN, $column);
		} elseif ($this->config->isOperator($value)) {
			return new Token(Token::OPERATOR, $value);
		} elseif ($this->isConjunction($value)) {
			return new Token(self::CONJUNCTIONS[$value]);
		}

		return new Token(Token::VALUE, $value);
	}

	public function isConjunction(string $input): bool {
		return in_array(strtolower($input), array_keys(self::CONJUNCTIONS));
	}
}
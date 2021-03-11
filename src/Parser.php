<?php

namespace MarkJaquith\Wherewithal;

use MarkJaquith\Wherewithal\Contracts\{ConfigContract, ParserContract, StructureContract, TokenContract};
use MarkJaquith\Wherewithal\Exceptions\{
	AdjacentColumnException,
	AdjacentOperatorException,
	EmptyGroupException,
	ParenthesesMismatchException,
};

class Parser implements ParserContract {
	const PREG_DELIMITER = '#';
	private ConfigContract $config;
	private TokenFactory $tokenFactory;

	/**
	 * Parser constructor.
	 *
	 * @param ConfigContract $config
	 */
	public function __construct(ConfigContract $config) {
		$this->config = $config;
		$this->tokenFactory = new TokenFactory($config);
	}

	public function normalizeQuery(string $query): string {
		return preg_replace('# (and|or)\(#i', ' $1 (', $query);
	}

	/**
	 * Split a string by any of an array of delimiters.
	 * 
	 * The delimiters will also be in the resulting array.
	 *
	 * @param string $input
	 * @param array $delimiters
	 *
	 * @return string[]
	 */
	private function splitByDelimiters(string $input, array $delimiters): array {
		$splits = array_map(fn($delimiter) => preg_quote($delimiter, self::PREG_DELIMITER), $delimiters);
		$matches = preg_split(sprintf('%s(%s)%si', self::PREG_DELIMITER, join('|', $splits), self::PREG_DELIMITER), $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$matches = array_map('trim', $matches);

		return array_values(array_filter($matches, fn($match) => strlen($match) > 0));
	}

	public function splitConjunctions(string $query): array {
		$query = $this->normalizeQuery($query);

		return $this->splitByDelimiters($query, ['(', ')', ' and ', ' or ']);
	}

	public function isConjunction(string $input): bool {
		return $this->tokenFactory->isConjunction($input);
	}

	public function parse(string $query): StructureContract {
		$out = [];
		$parts = $this->splitConjunctions($query);
		foreach ($parts as $part) {
			if ($this->isConjunction($part)) {
				$out[] = $this->tokenFactory->make($part);
			} else {
				// It's a condition.
				foreach ($this->parseCondition($part) as $conditionPart) {
					$out[] = $this->tokenFactory->make($conditionPart);
				}
			}
		}

		// Sanity checks.

		// Parentheses do not match.
		$parenLevel = 0;
		foreach($out as $token) {
			if ($token->isType(Token::GROUP_START)) {
				$parenLevel++;
			} elseif ($token->isType(Token::GROUP_END)) {
				$parenLevel--;
			}

			if ($parenLevel < 0) {
				throw new ParenthesesMismatchException;
			}
		}

		if ($parenLevel !== 0) {
			throw new ParenthesesMismatchException;
		}

		array_reduce($out, function (TokenContract $previous, TokenContract $current): TokenContract {
			switch([$previous->getType(), $current->getType()]) {
				case [Token::GROUP_START, Token::GROUP_END]:
					throw new EmptyGroupException;
				case [0, Token::GROUP_END]:
					throw new ParenthesesMismatchException;
				case [Token::OPERATOR, Token::OPERATOR]:
					throw new AdjacentOperatorException;
				case [Token::COLUMN, Token::COLUMN];
					throw new AdjacentColumnException;
			}

			return $current;
		}, new Token(Token::PATTERN_START));

		return new Structure($out);
	}

	public function parseCondition(string $input): array {
		return $this->splitByDelimiters($input, $this->config->getOperators());
	}
}

<?php

namespace MarkJaquith\Wherewithal;

use MarkJaquith\Wherewithal\Contracts\{
	ConfigContract,
	ParserContract,
	StructureContract,
	TokenContract,
};
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
		$splits = array_map(fn(string $delimiter): string => preg_quote($delimiter, self::PREG_DELIMITER), $delimiters);
		$matches = preg_split(sprintf('%s(%s)%si', self::PREG_DELIMITER, join('|', $splits), self::PREG_DELIMITER), $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$matches = array_map('trim', $matches);

		return array_values(array_filter($matches, fn($match) => strlen($match) > 0));
	}

	/**
	 * Splits the query into chunks containing conjunctions or conditions.
	 *
	 * @param string $query
	 * @return string[]
	 */
	public function splitConjunctions(string $query): array {
		$query = $this->normalizeQuery($query);

		return $this->splitByDelimiters($query, ['(', ')', ' and ', ' or ']);
	}

	/**
	 * Parse a query into a Structure (a collection of tokens).
	 *
	 * @param string $query
	 * @return StructureContract
	 */
	public function parse(string $query): StructureContract {
		$tokens = new Structure;

		$parts = $this->splitConjunctions($query);
		foreach ($parts as $part) {
			if ($this->tokenFactory->isConjunction($part)) {
				$tokens->append($this->tokenFactory->make($part));
			} else {
				// It's a condition.
				foreach ($this->parseCondition($part) as $conditionPart) {
					$tokens->append($this->tokenFactory->make($conditionPart));
				}
			}
		}

		$this->scanForExceptions($tokens);

		return $tokens;
	}

	/**
	 * Scans an array of tokens and maybe throws an exception.
	 *
	 * @param StructureContract $tokens
	 * @return void
	 */
	private function scanForExceptions(StructureContract $tokens): void {
		$this->scanForParenthesesMismatch($tokens);
		$this->scanForAdjacentTokenExceptions($tokens);
	}

	/**
	 * Scans an array of tokens for adjacent token issues.
	 *
	 * @param StructureContract $tokens
	 * @return void
	 */
	private function scanForAdjacentTokenExceptions(StructureContract $tokens): void {
		array_reduce($tokens->toArray(), function (TokenContract $previous, TokenContract $current): TokenContract {
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
	}

	/**
	 * Scans an array of tokens for parenthetical issues.
	 *
	 * @param StructureContract $tokens
	 * @return void
	 */
	private function scanForParenthesesMismatch(StructureContract $tokens): void {
		$parenLevel = 0;
		foreach($tokens as $token) {
			assert($token instanceof TokenContract);
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
	}

	/**
	 * Splits a condition into its values, columns, and operators.
	 *
	 * @param string $input
	 * @return string[]
	 */
	public function parseCondition(string $input): array {
		return $this->splitByDelimiters($input, $this->config->getOperators());
	}
}

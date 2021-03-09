<?php

namespace MarkJaquith\Wherewithal;

use MarkJaquith\Wherewithal\Contracts\{ConfigContract, ParserContract, StructureContract};
use MarkJaquith\Wherewithal\Exceptions\{AdjacentColumnException,
	AdjacentOperatorException,
	EmptyGroupException,
	MissingOperatorException,
	ParenthesesMismatchException,
	InvalidConjunctionPlacementException};

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

	private function splitByDelimiters(string $input, array $delimiters) {
		$splits = array_map(fn($delimiter) => preg_quote($delimiter, self::PREG_DELIMITER), $delimiters);
		$matches = preg_split(sprintf('%s(%s)%si', self::PREG_DELIMITER, join('|', $splits), self::PREG_DELIMITER), $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$matches = array_map('trim', $matches);

		return array_filter($matches, fn($match) => strlen($match) > 0);
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
		$leftParen = count(array_filter($out, fn($token) => $token->isType(Token::GROUP_START)));
		$rightParen = count(array_filter($out, fn($token) => $token->isType(Token::GROUP_END)));

		if ($leftParen !== $rightParen) {
			throw new ParenthesesMismatchException;
		}

		array_reduce($out, function ($previous, $current) {
			switch([$previous->getType(), $current->getType()]) {
				case [Token::GROUP_START, Token::GROUP_END]:
					throw new EmptyGroupException;
				case [0, Token::GROUP_END]:
					throw new ParenthesesMismatchException;
				case [Token::OPERATOR, Token::OPERATOR]:
					throw new AdjacentOperatorException;
				case [Token::COLUMN, Token::COLUMN];
					throw new AdjacentColumnException;
				case [Token::VALUE, Token::COLUMN]:
				case [Token::COLUMN, Token::VALUE]:
					throw new MissingOperatorException;
			}

			return $current;
		}, new Token(Token::PATTERN_START));

		return new Structure($out);
	}

	public function parseCondition(string $input): array {
		return $this->splitByDelimiters($input, $this->config->getOperators());
	}
}

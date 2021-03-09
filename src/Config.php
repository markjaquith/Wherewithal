<?php

namespace MarkJaquith\Wherewithal;

class Config implements Contracts\ConfigContract {
	private array $operators = [];
	private array $columns = [];

	public function addOperator(string $operator): self {
		$this->operators[$operator] = true;

		return $this;
	}

	public function addOperators(string ...$operators): self {
		foreach($operators as $operator) {
			$this->addOperator($operator);
		}

		return $this;
	}

	public function getOperators(): array {
		return array_keys($this->operators);
	}

	public function addColumn(string $column, string ...$aliases): self {
		array_unshift($aliases, $column);

		foreach (array_unique($aliases) as $alias) {
			$this->columns[$alias] = $column;
		}

		return $this;
	}

	public function getColumns(): array {
		return $this->columns;
	}

	public function getColumn(string $column): ?string {
		return $this->getColumns()[$column] ?? null;
	}

	public function isOperator(string $operator): bool {
		return isset($this->operators[$operator]);
	}
}

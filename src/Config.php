<?php

namespace MarkJaquith\Wherewithal;

class Config implements Contracts\ConfigContract {
	private array $comparisons = [];
	private array $columns = [];

	public function addComparison(string $comparison): self {
		$this->comparisons[$comparison] = true;

		return $this;
	}

	public function addComparisons(string ...$comparisons): self {
		foreach($comparisons as $comparison) {
			$this->addComparison($comparison);
		}

		return $this;
	}

	public function getComparisons(): array {
		return array_keys($this->comparisons);
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
}

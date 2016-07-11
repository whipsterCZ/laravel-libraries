<?php

/**
 * @author: Daniel Kouba
 *
 * This should replace original `Rutorika/SortableTrait`
 * It allows to override sortableField
 * It allows to prepare or repair initial sorting
 * It should be used in Eloquent Model
 */


namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class SortableTrait.
 *
 * @traitUses \Illuminate\Database\Eloquent\Model
 */
trait Sortable
{


	/**
	 * Adds position to model on creating event.
	 */
	public static function bootSortable()
	{
		static::creating(
			function ($model) {
				$sortableGroupField = $model->getSortableGroupField();
				$sortableField = $model->getSortableField();

				if ($sortableGroupField) {
					$maxPosition = static::where($sortableGroupField, $model->$sortableGroupField)->max($sortableField);
				} else {
					$maxPosition = static::max($sortableField);
				}

				$model->{$sortableField} = $maxPosition + 1;
			}
		);
	}

	/**
	 * @param \Illuminate\Database\Query\Builder $query
	 *
	 * @return \Illuminate\Database\Query\Builder
	 */
	public function scopeSorted($query)
	{
		return $query->orderBy($this->getSortableField());
	}

	/**
	 * moves $this model after $entity model (and rearrange all entities).
	 *
	 * @param \Illuminate\Database\Eloquent\Model $entity
	 *
	 * @throws \Exception
	 */
	public function moveAfter($entity)
	{
		$sortableGroupField = $this->getSortableGroupField();
		if ($sortableGroupField && $this->$sortableGroupField !== $entity->$sortableGroupField) {
			throw new SortableException($this->$sortableGroupField, $entity->$sortableGroupField);
		}

		/** @var \Illuminate\Database\Connection $connection */
		$connection = $this->getConnection();

		$this->_transaction(function () use ($connection, $entity) {
			/** @var \Illuminate\Database\Eloquent\Builder $query */
			$query = $connection->table($this->getTable());
			$query = $this->_applySortableGroup($query);

			if ($this->{$this->getSortableField()} > $entity->{$this->getSortableField()}) {
				$query
					->where($this->getSortableField(), '>', $entity->{$this->getSortableField()})
					->where($this->getSortableField(), '<', $this->{$this->getSortableField()})
					->increment($this->getSortableField());

				$this->{$this->getSortableField()} = $entity->{$this->getSortableField()} + 1;
			} elseif ($this->{$this->getSortableField()} < $entity->{$this->getSortableField()}) {
				$query
					->where($this->getSortableField(), '<=', $entity->{$this->getSortableField()})
					->where($this->getSortableField(), '>', $this->{$this->getSortableField()})
					->decrement($this->getSortableField());

				$this->{$this->getSortableField()} = $entity->{$this->getSortableField()};
				$entity->{$this->getSortableField()} = $entity->{$this->getSortableField()} - 1;
			}

			$this->save();
		});
	}

	/**
	 * moves $this model before $entity model (and rearrange all entities).
	 *
	 * @param \Illuminate\Database\Eloquent\Model $entity
	 *
	 * @throws SortableException
	 */
	public function moveBefore($entity)
	{
		$sortableGroupField = $this->getSortableGroupField();
		if ($sortableGroupField && $this->$sortableGroupField !== $entity->$sortableGroupField) {
			throw new SortableException($this->$sortableGroupField, $entity->$sortableGroupField);
		}

		/** @var \Illuminate\Database\Connection $connection */
		$connection = $this->getConnection();

		$this->_transaction(function () use ($connection, $entity) {
			$query = $connection->table($this->getTable());
			$query = $this->_applySortableGroup($query);

			if ($this->{$this->getSortableField()} > $entity->{$this->getSortableField()}) {
				$query
					->where($this->getSortableField(), '>=', $entity->{$this->getSortableField()})
					->where($this->getSortableField(), '<', $this->{$this->getSortableField()})
					->increment($this->getSortableField());

				$this->{$this->getSortableField()} = $entity->{$this->getSortableField()};

				$entity->{$this->getSortableField()} = $entity->{$this->getSortableField()} + 1;
			} elseif ($this->{$this->getSortableField()} < $entity->{$this->getSortableField()}) {
				$query
					->where($this->getSortableField(), '<', $entity->{$this->getSortableField()})
					->where($this->getSortableField(), '>', $this->{$this->getSortableField()})
					->decrement($this->getSortableField());

				$this->{$this->getSortableField()} = $entity->{$this->getSortableField()} - 1;
			}

			$this->save();
		});
	}

	/**
	 * @param int $limit
	 *
	 * @return Builder
	 */
	public function previous($limit = 0)
	{
		/** @var Builder $query */
		$query = $this->newQuery();
		$query = $this->_applySortableGroup($query);
		$query->where($this->getSortableField(), '<', $this->{$this->getSortableField()});
		$query->orderBy($this->getSortableField(), 'desc');
		$query->limit($limit);

		return $query;
	}

	/**
	 * @param int $limit
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public function getPrevious($limit = 0)
	{
		return $this->previous($limit)->get()->reverse();
	}

	/**
	 * @param int $limit
	 *
	 * @return Builder
	 */
	public function next($limit = 0)
	{
		/** @var Builder $query */
		$query = $this->newQuery();
		$query = $this->_applySortableGroup($query);
		$query->where($this->getSortableField(), '>', $this->{$this->getSortableField()} );
		$query->orderBy($this->getSortableField(), 'asc');
		$query->limit($limit);

		return $query;
	}

	/**
	 * @param int $limit
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public function getNext($limit = 0)
	{
		return $this->next($limit)->get();
	}

	/**
	 * @param callable|\Closure $callback
	 *
	 * @return mixed
	 */
	protected function _transaction(\Closure $callback)
	{
		return $this->getConnection()->transaction($callback);
	}

	/**
	 * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
	 *
	 * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
	 */
	protected function _applySortableGroup($query)
	{
		$sortableGroupField = $this->getSortableGroupField();
		if ($sortableGroupField) {
			$query->where($sortableGroupField, '=', $this->$sortableGroupField);
		}

		return $query;
	}

	/**
	 * @return string|null
	 */
	public static function getSortableGroupField()
	{
		return isset(static::$sortableGroupField) ? static::$sortableGroupField : null;
	}

	/**
	 * @return string|null
	 */
	public static function getSortableField()
	{
		return isset(static::$sortableField) ? static::$sortableField : 'position';
	}


	/**
	 * prepare or repair initial sorting
	 */
	public static function _prepareSortable() {
		$data = [];
		$sortableGroupField = static::getSortableGroupField();
		$sortableField = static::getSortableField();
		foreach(static::all() as $row) {
			if ($sortableGroupField) {
				$data[$row->{$sortableGroupField}][] = $row;
			} else {
				$data[0][$row];
			}
		}

		foreach ($data as $group => $groupData ) {
			$i = 0;
			foreach ($groupData as $row) {
				$row->{$sortableField} = ++$i;
				$row->save();
				echo sprintf("row %d was orderer<br>\n",$row->id);
			}
		}
	}
}

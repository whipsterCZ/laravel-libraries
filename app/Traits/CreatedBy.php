<?php

/**
 * @author: Daniel Kouba
 *
 * Trait with creation date and user helpers
 * It should be used in EloquentModel
 */

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait CreatedBy
{

	public function createdAt($format = null){
		$columnName = self::CREATED_AT;
		if ( isset($this->{$columnName}) && $this->{$columnName} ) {
			if (is_null($format)) {
				$format = defined('DATE_HUMAN') ? DATE_HUMAN : 'd.m.Y';
			}
			return $this->{$columnName}->format( $format);
		}
		return '';
	}

	public function createdBy(){
		$columnName = $this->creatorIdField();
		if ( isset($this->{$columnName}) && $this->{$columnName} ) {
			return $this->creator->name;
		}
		return '';
	}

	public function createdByWithDate($prefix = "Created by ", $separator = " | at ",$format = null ){
		$columnName = $this->creatorIdField();
		return $prefix . implode($separator, [$this->createdBy($columnName), $this->createdAt($format)]);
	}

	public static function bootCreatedBy() {
		$columnName = self::creatorIdField();
		static::creating(function($model) use ($columnName){
			if ( auth()->check() ) {
				$model->{$columnName} = auth()->user()->id;
			}
		});
	}

	public function creator(){
		return $this->belongsTo('App\Models\User', $this->creatorIdField()  );
	}

	public static function creatorIdField()	{
		return isset(static::$creator_id_field) ? static::$creator_id_field : 'creator_id';
	}

}
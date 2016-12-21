<?php

/**
 * @author: Daniel Kouba
 *
 * This is helper for handling publishable models.
 * It should be used in EloquentModel
 */


namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class PublishableTrait.
 *
 * @traitUses \Illuminate\Database\Eloquent\Model
 */
trait Publishable {

	public function isPublished($timestamp = null) {
		if ( !isset($timestamp)) {
			$timestamp = time();
		}
		$fromField = $this->getPublishedFromField();
		$toField = $this->getPublishedToField();
		$dateFromPass = true;
		$dateToPass = true;

        if ( isset($this->{$fromField}) && $this->{$fromField} ) {
			$dateFromPass = date('Y-m-d',strtotime($this->{$fromField})) <= date('Y-m-d',$timestamp);
		}
        if ( isset($this->{$toField}) && $this->{$toField} ) {
			$dateToPass = date('Y-m-d',strtotime($this->{$toField})) >= date('Y-m-d',$timestamp);
		}
		return $this->isActive() && $dateFromPass && $dateToPass;
	}

	public function isActive(){
        $activeField = $this->getActiveField();
		return isset($this->{$activeField}) ? $this->{$activeField} : true;
	}

	public function publishedFrom($format = null){
		$field = $this->getPublishedFromField();
		$this->dates[] = $field;
		if ( isset($this->{$field}) && $this->{$field} ) {
			if ( is_string($this->{$field} ) ) {
				throw new \Exception(sprintf('To use publishable trait add your `%s` field to models `$dates[]` property.',$field));
			}
			if (is_null($format)) {
				$format = defined('DATE_HUMAN') ? DATE_HUMAN : 'd.m.Y';
			}
			return  $this->{$field}->format($format);
		}
		return '';
	}

	public function publishedTo($format = null){
		$field = $this->getPublishedToField();
		if ( isset($this->{$field}) && $this->{$field} ) {
			if ( is_string($this->{$field} ) ) {
				throw new \Exception(sprintf('To use publishable trait add your `%s` field to models `$dates[]` property.',$field));
			}
			if (is_null($format)) {
				$format = defined('DATE_HUMAN') ? DATE_HUMAN : 'd.m.Y';
			}
			return  $this->{$field}->format($format);
		}
		return '';
	}

	public static function getPublishedFromField()	{
		return isset(static::$publishedFromField) ? static::$publishedFromField : 'published_from';
	}

	public static function getPublishedToField()	{
		return isset(static::$publishedToField) ? static::$publishedToField : 'published_to';
	}

    public static function getActiveField()	{
        return isset(static::$activeField) ? static::$activeField : 'active';
    }

	/**
	 * Scope a query to only include active users.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopePublished($query)	{
        $activeField = $this->getActiveField();
        $fromField = $this->getPublishedFromField();
        $toField = $this->getPublishedToField();

		if ( $activeField ) {
			$query->where($activeField,1);
		}
		$now = date('Y-m-d');
		if ( $fromField ) {
			$query->whereRaw("($fromField IS NULL OR DATE($fromField) <= '$now')");
		}
		if ( $toField ) {
			$query->whereRaw("($toField IS NULL OR DATE($toField) >= '$now')");
		}
		return $query;

	}



}

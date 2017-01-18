<?php

namespace Beacon;

use Illuminate\Database\Eloquent\Model;

class TypesPlates extends Model
{

	public $timestamps = false;
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
			'name', 'description', 'language_id',
	];
    
    public function language()
    {
        return $this->hasOne(Language::class, 'language_id');
    }
    
    public function plates()
    {
        return $this->hasMany(Plate::class, 'type_plate_id', 'id');
    }
}

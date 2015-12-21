<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $table = 'cities';
	
	public function vacancy() {
		return $this->hasMany('App\Vacancy');
	}
	
}


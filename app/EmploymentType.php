<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmploymentType extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $table = 'employment_types';
	
	public function vacancy() {
		return $this->belongsToMany('App\Vacancy', 'vacancy_employments', 'employment_id', 'vacancy_id');
	}
	
	
}


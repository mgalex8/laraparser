<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $table = 'vacancy';
		
	
	public function city() {
		return $this->hasOne('App\City');
	}
	
	public function employmentTypes() {
		return $this->belongsToMany('App\EmploymentType', 'vacancy_employments', 'vacancy_id', 'employment_id');
	}
		
	
	public function maxId()
	{
		return $this->max('id');
	}
	
	
	public function minId()
	{
		$vacancy = DB::table($this->table)->select('min(id)')->first();
		if ($vacancy) {
			return $vacancy->min;
		}
		else {
			return null;
		}		
	}
	
	
	public function getHtmlDom($link) 
	{		
		try {
			$html = new \Htmldom;
			$html->file_get_html($link);
		}
		catch (\Exception $e) {		
			return false;	
		}		
		return $html;		
	}
			

	public function parse($html, $hhId, $link = null) 
	{
		if (!$html) {
			$error = 'Parse error';
		}	
		
		$vacancy = new Vacancy;
		if ($vacancy->where('hh_id','=',$hhId)->first()) {
			$error = 'Vacancy found in database';
		}
		
		
		//name
		if ($obj = $html->find('.b-vacancy-title', 0)) {
			$title = $obj->plaintext;		
		}
		
		//description
		if ($obj = $html->find('.b-vacancy-desc-wrapper', 0)) {
			$description = $obj->innertext;
		}
		
		//address
		if ($obj = $html->find('.vacancy-address-text', 0)) {
			$address = $obj->innertext;		
		}
		
		//company_name
		if ($obj = $html->find('.companyname', 0)) {
			$companyName = $obj->plaintext;		
		}

		//employmentMode
		$employmentMode = array();
		foreach ($html->find('.b-vacancy-employmentmode span') as $span) {
			$employmentMode[] = $span->innertext;
		}
		
		$head = $html->find('.b-vacancy-info .l-content-3colums', 0)->innertext;		
		if ($res = $html->str_get_html($head)) {		
			
			//content
			if ($obj = $res->find('.l-content-colum-1', 1)) {
				if ($tag = $obj->find('.l-paddings meta',0)) {
					$currency = $tag->content;
				}
			}
			
			//salary							
			if ($obj = $res->find('.l-content-colum-1', 1)) {
				if ($tag = $obj->find('.l-paddings',0)) {
					$salaryText = $tag->plaintext;
				}
			}			
			if (!empty($salaryText)) {
				if (!preg_match('/[\d]{1}/i', $salaryText)) {
					$salary_from = 0;
					$salary_to = 0;
				}
				else {
					$salary = explode('до', $salaryText);	
					
					if (isset($salary[0]) && strlen( trim( $salary[0])) > 0) 
					{
						preg_match_all('/[\w]{2}(.*)[\w\s\.]{1,4}/', $salary[0], $matches);							
						$str = str_replace(chr(194).chr(160), '', $matches[0][0]);
						$str = str_replace(' ', '', $str);
						$salary_from = intval($str);
					}
					if (isset($salary[1]) && strlen( trim( $salary[1])) > 0)
					{
						preg_match_all('/[\w\s]{1,2}(.*)[\w\s]{1,3}/', $salary[1], $matches);				
						$str = str_replace(chr(194).chr(160), '', $matches[0][0]);
						$str = str_replace(' ', '', $str);
						$salary_to = intval($str);
					}					
				}
			}
			
			//cityName
			if ($obj = $res->find('.l-content-colum-2', 1)) {
				if ($tag = $obj->find('.l-paddings',0)) {
					$cityName = $tag->plaintext;
				}
			}
			
			//metro
			if ($obj = $res->find('.l-content-colum-2', 1)) {
				if ($tag = $obj->find('.metro-station',0)) {
					$metro = $tag->plaintext;
				}
			}
			if (isset($metro)) {
				$cityName = str_replace(',', '', $cityName);
				$cityName = str_replace(',', '', $metro);
				$cityName = trim($cityName);
			}
			
			//experience
			if ($obj = $res->find('.l-content-colum-3', 1)) {
				if ($tag = $obj->find('.l-paddings',0)) {
					$experience = $tag->plaintext;
				}
			}
		}


		// Save Vacancy
		$vacancy = new Vacancy;
		
		if (isset($title)) {
			$vacancy->name = $title;
		}
		if (isset($companyName)) {
			$vacancy->company_name = $companyName;
		}
		if (isset($hhId)) {
			$vacancy->hh_id = $hhId;
		}
		if (isset($link)) {
			$vacancy->hh_link = $link;
		}
		if (isset($salary_from)) {
			$vacancy->salary_from = $salary_from;
		}
		if (isset($salary_to)) {
			$vacancy->salary_to = $salary_to;
		}
		if (isset($currency)) {
			$vacancy->currency = $currency;
		}
		if (isset($cityName)) {
			$cities = new City;
			if ($city = $cities->where('name','=',$cityName)->first()) {
				$vacancy->city_id = $city->id;
			}
		}
		if (isset($experience)) {
			$vacancy->experience = $experience;
		}
		if (isset($description)) {
			$vacancy->description = $description;
		}
		if (isset($address)) {
			$vacancy->address = $address;
		}
				
		// save	
		if (!$vacancy->save()) {
			$error = 'Vacancy not saved';
		}
		
		// Employment Types Save
		if (count($employmentMode) > 0) {
			$empType = new \App\EmploymentType;				
			foreach ($employmentMode as $emp) {
				if ($find = $empType->where('name','LIKE','%'.$emp.'%')->first()) {
					$empIds[] = $find->id; 
				}
				else {
					$employ = new EmploymentType;
					$employ->name = $emp;
					$employ->save();
					$empIds[] = $employ->id;						
				}
			}
			$empType = new \App\EmploymentType;
			$vacancy->employmentTypes()->attach($empIds);				
		}
		
		if (!empty($error)) {
			return $error;
		} else {
			return true;
		}
	}	
	
}


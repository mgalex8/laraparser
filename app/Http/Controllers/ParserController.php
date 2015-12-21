<?php namespace App\Http\Controllers;

use App\Vacancy;
use Illuminate\Http\Request;

class ParserController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/
	
	private $link_tpl = 'http://rostov.hh.ru/vacancy/';
	
	private $default_hh_id = '15383870';

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('guest');
	}

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function index(Request $request)	
	{
		if ($request->has('count')) {
			$count = $request->input('count');
		}
		else {
			$count = 10;
		}
		
		// Parsing
		$success = false; 
		for ($i=0; $i<$count; $i++) 
		{
			$ret = $this->parseNext();
			if ($ret) {
				$success = true;
				$links[] = $ret;
			}
			else {
				$errors[] = $ret;
			}
		}			
		
		if ($success == true) {
			return json_encode(array(
				'success' => 1,
				'links' => $links,
			));	
		}
		else {
			$json['success'] = 0;
			if (isset($errors) && count($errors) > 0) {
				$json['errors'] = $errors;
			}
			return json_encode($json);
		}
	}
	
	
	public function count() 
	{
		$vacancy = new Vacancy;
		$count = $vacancy->count();
		
		return json_encode(array(
			'count'=>$count,
		));
	}
	
	
	protected function parseNext() 
	{	
		//get hh id
		$vacancy = new Vacancy;
		$lastId = $vacancy->maxId();
		if ($lastId) {
			$lastVacancy = $vacancy->find($lastId);
			$hhId = $lastVacancy->hh_id;					
		}
				
		if (isset($hhId)) {
			$hhId = intval($hhId) + 1;
			$link = $this->link_tpl . $hhId;				
		}
		else {
			$hhId = $this->default_hh_id;
			$link = $this->link_tpl . $hhId;			
		}		
		
		
		// Simple HTML Dom
		//Ищем страницу вакансии
		//если есть в базе вычисляем новый Id
		//Парсинг страниц ведется сначала в большую сторону
		//если превышен лимит, меняем направление
		$index = 0;
		$maxLoadIndex = 10;
		$operand = '+';
		$html = $vacancy->getHtmlDom($link);
		while(!$html) {
			if ($operand == '+') {
				$hhId = intval($hhId) + 1;
			}
			else {
				$hhId = intval($hhId) - 1;
			}		
			
			if ($vacancy->where('hh_id','=',$hhId)->first()) {
				continue;
			}			
			if ($index++ > $maxLoadIndex) {
				$operand = '-';				
				$maxLoadIndex = 100;
				$index = 0;
				$hhId = $vacancy->minId();
			}
			
			$link = $this->link_tpl . $hhId;
			$html = $vacancy->getHtmlDom($link);				
		}	
		
		if ($ret = $vacancy->parse($html, $hhId, $link)) {
			return $link;
						
		}
		else {
			return $ret;
		}
		
	}
	
	

}

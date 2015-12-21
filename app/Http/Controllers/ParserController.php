<?php namespace App\Http\Controllers;

use App\Vacancy;
use Illuminate\Http\Request;

class ParserController extends Controller {

	
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
	public function index()
	{
		return view('parser.index');
	}
	
	public function parse(Request $request)	
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
			//парсим следующую вакансию
			$ret = $this->parseNext();
			if ($ret) {
				$success = true;
				$links = $ret;
				echo $links.'</br>';
			}
			else {
				$errors = $ret;
				echo $error.'</br>';
			}
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
		//Получаем последний hh id из базы и увеличиваем на 1
		//если база пустая берем значение по умолчанию
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
		//Получение страниц ведется сначала в большую сторону
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
		
		// парсинг полученной страницы вакансии
		// и запись в базу
		$ret = $vacancy->parseAndSave($html, $hhId, $link);
		if ($ret === true) {
			return $link;
						
		}
		else {
			return $ret;
		}
		
	}
	
	

}

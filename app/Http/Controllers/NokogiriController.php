<?php namespace App\Http\Controllers;

use App\Vacancy;
use Illuminate\Http\Request;
use nokogiri;

class NokogiriController extends Controller {

	
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
                
                $count = 1;
		
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
        
        public function ParsePage() {
                $hhId = $this->getNextHHId();
                $link = $this->createHHLink($hhId);
                
                $html = $this->getHtmlFromPage($link);
                
                $this->nokogiriParseAndSave($html, $hhId, $link);
        }      
        
        
        // Функция парсит код html и записывает данные в базу
	//
	public function nokogiriParseAndSave($html, $hhId, $link = null) 
	{
		if (!$html) {
			return 'Parse error';
                }
		
		$vacancy = new Vacancy;
		if ($vacancy->where('hh_id','=',$hhId)->first()) {
			return 'Vacancy '.$hhId.' found in database';
		}
		
		
                $saw = new nokogiri($html);
                
                $sv['title'] = $saw->get('h1.b-vacancy-title')->toText();
                $sv['descr'] = $saw->get('.b-vacancy-desc-wrapper')->toTextArray();
		$sv['addr'] = $saw->get('.vacancy-address-text')->toText();
		$sv['company'] = $saw->get('.companyname')->toText();
                
                dd($sv);
                
                /*
                $vacancy = new Vacancy;
                $vacancy->name = $title;            
                
                // save	
		if (!$vacancy->save()) {
			return 'Vacancy not saved';
		}
                */
                
		
		return true;		
	}
	
	
	public function count() 
	{
		$vacancy = new Vacancy;
		$count = $vacancy->count();
		
		return json_encode(array(
			'count'=>$count,
		));
	}
        
        protected function getHtmlFromPage($link) 
	{		
		return file_get_contents($link);
	}
        
        // Получаем последний hh id из базы и увеличиваем на 1
        // если база пустая берем значение по умолчанию
        //
        protected function getNextHHId () {
                
		$vacancy = new Vacancy;
		$lastId = $vacancy->maxId();
		if ($lastId) {
			$lastVacancy = $vacancy->find($lastId);
			$hhId = $lastVacancy->hh_id;					
		}
				
		if (isset($hhId)) {
			return intval($hhId) + 1;			
		}
		else {
			return $this->default_hh_id;			
		}
        }
        
        protected function createHHLink($hhId) {
            return $this->link_tpl . $hhId;
        }
	
	
	protected function parseNext() 
	{			
                $hhId = $this->getNextHHId();
                $link = $this->createHHLink($hhId);
		
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
			$html = $vacancy->getNokogiriHtmlDom($link);				
		}	
		
		// парсинг полученной страницы вакансии
		// и запись в базу
		$ret = $vacancy->nokogiriParseAndSave($html, $hhId, $link);
		if ($ret === true) {
			return $link;
						
		}
		else {
			return $ret;
		}
		
	}
	
	

}

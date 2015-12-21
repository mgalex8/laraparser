@extends('app')

@section('content')
	<div class="panel-heading">{{ $vacancy->name }}</div>
	<div class="panel-body">
		<div class="vacancy-info">
			<div><span>HH id: </span><span>{{ $vacancy->hh_id }}</span></div>			
			<div><span>Название: </span><span>{{ $vacancy->name }}</span></div>
			<div><span>Город: </span><span>{{ $vacancy->city['name'] }} </span></div>
			<div><span>Адрес: </span><span>{{ $vacancy->address }}</span></div>
			<div>
				<span>Зарплата: </span>
				<span>
					<?
						$salary = '';
						if ($vacancy->salary_from > 0) {
							$salary .= 'от '.$vacancy->salary_from.' ';
						}
						if ($vacancy->salary_to > 0) {
							$salary .= 'до '.$vacancy->salary_to.' ';
						}
						if (!empty($vacancy->currency)) {
							$salary .= $vacancy->currency;
						}
						echo $salary;
					?>
				</span>
			</div>
			<div><span>Компания: </span><span>{{ $vacancy->company_name }}</span></div>
			<div><span>Опыт работы: </span><span>{{ $vacancy->experience }}</span></div>
			<div>
				<span>Занятость: </span>
				<ul>
				@foreach ($vacancy->employmentTypes as $empType)
					<li>{{ $empType->name }}</li>
				@endforeach
				</ul>
			</div>			
			<div><? echo html_entity_decode($vacancy->description) ?></div>
			<div><span>Ссылка: </span><span><a href="{{ $vacancy->hh_link }}" target="_blank">{{ $vacancy->hh_link }}</a></span></div>
			<div>
				<a href="/vacancy/?page={{ $backpage }}"><input type="button" class="btn btn-info" value="Назад"></a>
			</div>
		</div>
	</div>
@endsection
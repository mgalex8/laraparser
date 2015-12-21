@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">Вакансии</div>
				<div class="panel-body">
					<table class="table">
						<thead>
							<tr>
								<td>Название</td>
								<td>Ссылка на HH</td>
								<td>Зарплата</td>
								<td>Компания</td>
								<td>Опыт работы</td>								
								<td>Город</td>
								<td>Адрес</td>						
							</tr>
						</thead>
						<tbody>
							@foreach ($vacancies as $vacancy)
							<tr>
								<td><a href="/vacancy/view/{{ $vacancy->id }}/?backpage={{ $vacancies->currentPage() }}">{{ $vacancy->name }}</td>
								<td>@if (!empty($vacancy->hh_link)) 
										<a href="{{ $vacancy->hh_link }}" target="_blank">{{ $vacancy->hh_id }}</a>
									@else
										{{ $vacancy->hh_id }}
									@endif
								</td>
								<td><?
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
								</td>
								<td>{{ $vacancy->company_name }}</td>
								<td>{{ $vacancy->experience }}</td>								
								<td>{{ $vacancy->city['name'] }}</td>																
								<td>{{ $vacancy->address }}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
					
					<?
						//page navigation 
						echo $vacancies->render(); 
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="{{ asset('/js/parsing.js') }}"></script>
@endsection
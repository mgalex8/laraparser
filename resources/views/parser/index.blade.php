@extends('app')

@section('content')				
	<div class="panel-heading">Parser</div>
	<div class="panel-body">
		<div class="loading" data-count="0">
			<div class="loading-bar"></div>
		</div>
		<div class="loading-url"></div>
		<div>
			<form method="get" action="/parser/parse2/">
				<label for="count">Количество:</label>
				<input type="input" id="count" name="count" value="500" />									
				<input type="submit" id="parse_btn" class="btn btn-default"  value="Парсинг" />
			</form>
		</div>
	</div>
@endsection



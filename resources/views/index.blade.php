@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Home</div>
				<div class="panel-body">
					<div class="loading" data-count="0">
						<div class="loading-bar"></div>
					</div>
					<div class="loading-url"></div>
					<input type="button" id="parse_btn" class="btn btn-default"  value="Парсинг" />
				</div>
			</div>
		</div>
	</div>
</div>
<script src="{{ asset('/js/parsing.js') }}"></script>
@endsection



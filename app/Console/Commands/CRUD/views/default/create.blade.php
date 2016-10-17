@extends('admin.layout')

@set('header',trans('@messages.create'))

@section('content')

	@embed('inspinia._box')
		@section('title',isset($header) ? $header : $title)
		@section('content')
			@include('admin.@entities._form')
		@endsection()
	@endembed

@endsection

@push('scripts')
	<script>
//		app.@entities.initForm();
	</script>
@endpush
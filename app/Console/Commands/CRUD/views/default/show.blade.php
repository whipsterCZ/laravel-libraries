@extends('admin.layout')

@set('header',trans('@messages.entity'). ": ".$@entity->name )

@section('content')

	@embed('inspinia._box')
		@section('title',isset($header) ? $header : $title)
		@section('content')
			{{ d($@entity) }}
		@endsection()
	@endembed

@endsection

@push('scripts')
	<script>
//		app.@entities.initDetail();
	</script>
@endpush
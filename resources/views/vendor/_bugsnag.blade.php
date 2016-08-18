@if( config('bugsnag.api_key_js') && App::environment( config('bugsnag.notify_release_stages') ) )
	<script src="//d2wy8f7a9ursnm.cloudfront.net/bugsnag-2.min.js" data-apikey="{{ config('bugsnag.api_key_js') }}"></script>
	@if(false)
	{{-- Integrate Bugsnag - verifying error message --}}
	<script>Bugsnag.notify("ErrorName", "Test Error");</script>
	@endif
@endif
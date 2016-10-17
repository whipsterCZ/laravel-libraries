{!! AppForm::open([
	'model' => $@entity,
	'store' => 'admin.@entities.store',
	'update' => 'admin.@entities.update',
	'ajax' => false,
	'files' => true,
	//'left_column_class'  => 'col-sm-2 col-md-2',
	//'right_column_class' => 'col-sm-10 col-md-10',
	//'left_column_offset_class' => 'col-sm-offset-2 col-md-offset-2',
]) !!}

{!! AppForm::errors() !!}

{!! AppForm::text('name',trans('messages.name')) !!}

{!! AppForm::select('type',trans('messages.type'), $@entity->types(), null,['class'=>'chosen'] ) !!}


{!! AppForm::checkbox('check') !!}

{{-- It send 0 ig not checked--}}
{!! AppForm::checkboxBool('check_boolean') !!}

{!! AppForm::select('@relation_ids[]',['placeholder'=>'', 'multiple'=>true, 'class'=>'chosen']
, \App\Models\User::lists('name','id')) !!}

{!! AppForm::select('@relation_id', ['placeholder'=>'','class'=>'chosen']	, @ModelNamespace\@Relation::lists('name','id')) !!}

{!! AppForm::textarea('text',trans('messages.text')) !!}

{!! AppForm::textarea('text',trans('messages.text'), null,['class'=>'summernote']) !!}

{!! AppForm::text('price',trans('messages.price'),null, ['appendAddon'=>'Kč']) !!}

<div class="hr-line-dashed"></div>

{!! AppForm::month('month',['prependAddon'=>'fa-calendar'], $@entity->monthFormatted() ) !!}

{!! AppForm::date('date',trans('messages.date')) !!}


{!! AppForm::date('published_from',trans('messages.published_from')) !!}
{!! AppForm::date('published_to',trans('messages.published_to')) !!}

{{-- ------------------------- DATE FROM TO SINGLE CONTROL  ------------------------  --}}
<div class="row">
	<label class="control-label col-md-2" for="published_from">Date started:</label>
	<div class="col-md-10">
		<div class="input-daterange input-group">
			<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
			{!! Form::text('date_started', $@entity->publishedFrom(), ['class'=>"form-control date-picker"])  !!}
			<span class="input-group-addon">to</span>
			{!! Form::text('date_ended',$@entity->publishedTo(), ['class'=>"form-control date-picker"])  !!}
		</div>
	</div>
</div>

<div class="hr-line-dashed"></div>

{{-- ------------------------- FILE UPLOAD  ------------------------  --}}
<div class="row"><div class="col-md-offset-2 col-md-10">
		@if ($@entity->exists && $@entity->upload->size())  {{ trans('messages.uploaded_file') }}
		{!! link_to_asset( $@entity->upload->url(), $@entity->upload->originalFilename(),['target'=>'_blank','title'=>trans('messages.uploaded_image')] ) !!}
		@endif
	</div></div>
{!! AppForm::file('upload', trans('messages.file')) !!}

{{-- ------------------------- IMAGE UPLOAD - Stapler ------------------------  --}}
<div class="row"><div class="col-md-offset-2 col-md-10">
		@if ($@entity->exists && $@entity->image->size())
			<a href="{{ $@entity->image->url()  }}" target="_blank" title="{{ trans('messages.uploaded_image') }}">
				<img src="{{ $@entity->image->url('thumb') }}" class="m-b-sm" alt="{{ trans('messages.uploaded_image') }}">
			</a>
		@endif
	</div></div>
{!! AppForm::file('image', trans('messages.image')) !!}


<div class="hr-line-dashed"></div>

{!! AppForm::submit(trans('@messages.save')) !!}

{!! AppForm::close() !!}
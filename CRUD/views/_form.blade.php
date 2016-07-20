{!! AppForm::open([
	'model' => $item,
	'store' => 'admin.items.store',
	'update' => 'admin.items.update',
	'ajax' => false,
	'files' => true,
	//'left_column_class'  => 'col-sm-2 col-md-2',
	//'right_column_class' => 'col-sm-10 col-md-10',
	//'left_column_offset_class' => 'col-sm-offset-2 col-md-offset-2',
]) !!}

{{--{!! AppForm::errors() !!}--}}

{!! AppForm::text('name',trans('messages.name')) !!}

{!! AppForm::select('type',trans('messages.type'), $item->types(), null,['class'=>'chosen'] ) !!}


{!! AppForm::checkbox('check') !!}

{{-- It send 0 ig not checked--}}
{!! AppForm::checkboxBool('check_boolean') !!}

{!! AppForm::select('users[]',['placeholder'=>'', 'multiple'=>true, 'class'=>'chosen']
, \App\Models\User::lists('name','id'), $item->users->pluck('id')) !!}

{!! AppForm::text('price',trans('messages.price'),null, ['appendAddon'=>'Kč']) !!}


{!! AppForm::select('user_id', ['placeholder'=>'','class'=>'chosen']	, \App\Models\User::lists('name','id')) !!}

{!! AppForm::textarea('text',trans('messages.text')) !!}

{!! AppForm::textarea('text',trans('messages.text'), null,['class'=>'summernote']) !!}
<div class="hr-line-dashed"></div>

{!! AppForm::month('month',['prependAddon'=>'fa-calendar'], $item->monthFormatted() ) !!}

{!! AppForm::date('date',trans('messages.date')) !!}


{!! AppForm::date('published_from',trans('messages.published_from')) !!}
{!! AppForm::date('published_to',trans('messages.published_to')) !!}

{{-- ------------------------- DATE FROM TO SINGLE CONTROL  ------------------------  --}}
<div class="row">
	<label class="control-label col-md-2" for="published_from">Date started:</label>
	<div class="col-md-10">
		<div class="input-daterange input-group">
			<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
			{!! Form::text('published_from', $item->publishedFrom(), ['class'=>"form-control date-picker"])  !!}
			<span class="input-group-addon">to</span>
			{!! Form::text('published_to',$item->publishedTo(), ['class'=>"form-control date-picker"])  !!}
		</div>
	</div>
</div>

<div class="hr-line-dashed"></div>

{{-- ------------------------- FILE UPLOAD  ------------------------  --}}
<div class="row"><div class="col-md-offset-2 col-md-10">
		@if ($item->exists && $item->upload->size())  {{ trans('messages.uploaded_file') }}
		{!! link_to_asset( $item->upload->url(), $item->upload->originalFilename(),['target'=>'_blank','title'=>trans('messages.uploaded_image')] ) !!}
		@endif
	</div></div>
{!! AppForm::file('upload', trans('messages.file')) !!}

{{-- ------------------------- IMAGE UPLOAD - Stapler ------------------------  --}}
<div class="row"><div class="col-md-offset-2 col-md-10">
		@if ($item->exists && $item->image->size())
			<a href="{{ $item->image->url()  }}" target="_blank" title="{{ trans('messages.uploaded_image') }}">
				<img src="{{ $item->image->url('thumb') }}" class="m-b-sm" alt="{{ trans('messages.uploaded_image') }}">
			</a>
		@endif
	</div></div>
{!! AppForm::file('image', trans('messages.image')) !!}


<div class="hr-line-dashed"></div>

{!! AppForm::submit(trans('messages.items.save')) !!}

{!! AppForm::close() !!}
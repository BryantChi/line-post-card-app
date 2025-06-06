<!-- Id Field -->
<div class="col-sm-12">
    {!! Form::label('id', 'Id:') !!}
    <p>{{ $cardTemplate->id }}</p>
</div>

<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $cardTemplate->name }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $cardTemplate->description }}</p>
</div>

<!-- Preview Image Field -->
<div class="col-sm-12">
    {!! Form::label('preview_image', 'Preview Image:') !!}
    <p>{{ $cardTemplate->preview_image }}</p>
</div>

<!-- Template Schema Field -->
<div class="col-sm-12">
    {!! Form::label('template_schema', 'Template Schema:') !!}
    <p>{{ $cardTemplate->template_schema }}</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $cardTemplate->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $cardTemplate->updated_at }}</p>
</div>


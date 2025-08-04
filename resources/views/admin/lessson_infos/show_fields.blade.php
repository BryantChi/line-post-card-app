<!-- Id Field -->
<div class="col-sm-12">
    {!! Form::label('id', 'Id:') !!}
    <p>{{ $lesssonInfo->id }}</p>
</div>

<!-- Title Field -->
<div class="col-sm-12">
    {!! Form::label('title', 'Title:') !!}
    <p>{{ $lesssonInfo->title }}</p>
</div>

<!-- Content Field -->
<div class="col-sm-12">
    {!! Form::label('content', 'Content:') !!}
    <p>{{ $lesssonInfo->content }}</p>
</div>

<!-- Image Field -->
<div class="col-sm-12">
    {!! Form::label('image', 'Image:') !!}
    <p>{{ $lesssonInfo->image }}</p>
</div>

<!-- Views Field -->
<div class="col-sm-12">
    {!! Form::label('views', 'Views:') !!}
    <p>{{ $lesssonInfo->views }}</p>
</div>

<!-- Num Field -->
<div class="col-sm-12">
    {!! Form::label('num', 'Num:') !!}
    <p>{{ $lesssonInfo->num }}</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $lesssonInfo->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $lesssonInfo->updated_at }}</p>
</div>


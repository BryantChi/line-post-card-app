<!-- Id Field -->
<div class="col-sm-12">
    {!! Form::label('id', 'Id:') !!}
    <p>{{ $businessCards->id }}</p>
</div>

<!-- User Id Field -->
<div class="col-sm-12">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $businessCards->user_id }}</p>
</div>

<!-- Template Id Field -->
<div class="col-sm-12">
    {!! Form::label('template_id', 'Template Id:') !!}
    <p>{{ $businessCards->template_id }}</p>
</div>

<!-- Title Field -->
<div class="col-sm-12">
    {!! Form::label('title', 'Title:') !!}
    <p>{{ $businessCards->title }}</p>
</div>

<!-- Subtitle Field -->
<div class="col-sm-12">
    {!! Form::label('subtitle', 'Subtitle:') !!}
    <p>{{ $businessCards->subtitle }}</p>
</div>

<!-- Profile Image Field -->
<div class="col-sm-12">
    {!! Form::label('profile_image', 'Profile Image:') !!}
    <p>{{ $businessCards->profile_image }}</p>
</div>

<!-- Content Field -->
<div class="col-sm-12">
    {!! Form::label('content', 'Content:') !!}
    <p>{{ $businessCards->content }}</p>
</div>

<!-- Flex Json Field -->
<div class="col-sm-12">
    {!! Form::label('flex_json', 'Flex Json:') !!}
    <p>{{ $businessCards->flex_json }}</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $businessCards->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $businessCards->updated_at }}</p>
</div>


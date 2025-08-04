<!-- Id Field -->
<div class="col-sm-12">
    {!! Form::label('id', 'Id:') !!}
    <p>{{ $seoSetting->id }}</p>
</div>

<!-- Url Field -->
<div class="col-sm-12">
    {!! Form::label('url', 'Url:') !!}
    <p>{{ $seoSetting->url }}</p>
</div>

<!-- Title Field -->
<div class="col-sm-12">
    {!! Form::label('title', 'Title:') !!}
    <p>{{ $seoSetting->title }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $seoSetting->description }}</p>
</div>

<!-- Keywords Field -->
<div class="col-sm-12">
    {!! Form::label('keywords', 'Keywords:') !!}
    <p>{{ $seoSetting->keywords }}</p>
</div>

<!-- Og:title Field -->
<div class="col-sm-12">
    {!! Form::label('og:title', 'Og:title:') !!}
    <p>{{ $seoSetting->og_title }}</p>
</div>

<!-- Og:description Field -->
<div class="col-sm-12">
    {!! Form::label('og:description', 'Og:description:') !!}
    <p>{{ $seoSetting->og_description }}</p>
</div>

<!-- Og:site Name Field -->
<div class="col-sm-12">
    {!! Form::label('og:site_name', 'Og:site Name:') !!}
    <p>{{ $seoSetting->og_site_name }}</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $seoSetting->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $seoSetting->updated_at }}</p>
</div>


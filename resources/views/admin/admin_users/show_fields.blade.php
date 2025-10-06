<!-- Id Field -->
<div class="col-sm-12">
    {!! Form::label('id', 'Id:') !!}
    <p>{{ $adminUsers->id }}</p>
</div>

<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $adminUsers->name }}</p>
</div>

<!-- Email Field -->
<div class="col-sm-12">
    {!! Form::label('email', 'Email:') !!}
    <p>{{ $adminUsers->email }}</p>
</div>

<!-- Login Count Field -->
<div class="col-sm-12">
    {!! Form::label('login_count', '登入次數:') !!}
    <p>{{ $adminUsers->login_count ?? 0 }}</p>
</div>

<!-- Last Login At Field -->
<div class="col-sm-12">
    {!! Form::label('last_login_at', '最後登入時間:') !!}
    <p>{{ $adminUsers->last_login_at ? $adminUsers->last_login_at->format('Y-m-d H:i:s') : '尚未登入' }}</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $adminUsers->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $adminUsers->updated_at }}</p>
</div>


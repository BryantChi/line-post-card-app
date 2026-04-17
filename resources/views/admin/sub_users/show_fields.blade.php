<!-- Id Field -->
<div class="col-sm-12">
    {!! Form::label('id', 'Id:') !!}
    <p>{{ $subUsers->id }}</p>
</div>

<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $subUsers->name }}</p>
</div>

<!-- Email Field -->
<div class="col-sm-12">
    {!! Form::label('email', 'Email:') !!}
    <p>{{ $subUsers->email }}</p>
</div>

<!-- Phone Field -->
<div class="col-sm-12">
    {!! Form::label('phone', '聯絡電話:') !!}
    <p>{{ $subUsers->phone ?? '未設定' }}</p>
</div>

<!-- LINE URL Field -->
<div class="col-sm-12">
    {!! Form::label('line_url', 'LINE 連結:') !!}
    <p>
        @if($subUsers->line_url)
            <a href="{{ $subUsers->line_url }}" target="_blank" rel="noopener">{{ $subUsers->line_url }}</a>
        @else
            未設定
        @endif
    </p>
</div>

<!-- Login Count Field -->
<div class="col-sm-12">
    {!! Form::label('login_count', '登入次數:') !!}
    <p>{{ $subUsers->login_count ?? 0 }}</p>
</div>

<!-- Last Login At Field -->
<div class="col-sm-12">
    {!! Form::label('last_login_at', '最後登入時間:') !!}
    <p>{{ $subUsers->last_login_at ? $subUsers->last_login_at->format('Y-m-d H:i:s') : '尚未登入' }}</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $subUsers->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $subUsers->updated_at }}</p>
</div>


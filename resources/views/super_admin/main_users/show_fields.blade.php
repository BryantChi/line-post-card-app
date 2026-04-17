<!-- Id Field -->
<div class="col-sm-12">
    {!! Form::label('id', 'Id:') !!}
    <p>{{ $mainUsers->id }}</p>
</div>

<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $mainUsers->name }}</p>
</div>

<!-- Email Field -->
<div class="col-sm-12">
    {!! Form::label('email', 'Email:') !!}
    <p>{{ $mainUsers->email }}</p>
</div>

<!-- Phone Field -->
<div class="col-sm-12">
    {!! Form::label('phone', '聯絡電話:') !!}
    <p>{{ $mainUsers->phone ?? '未設定' }}</p>
</div>

<!-- LINE URL Field -->
<div class="col-sm-12">
    {!! Form::label('line_url', 'LINE 連結:') !!}
    <p>
        @if($mainUsers->line_url)
            <a href="{{ $mainUsers->line_url }}" target="_blank" rel="noopener">{{ $mainUsers->line_url }}</a>
        @else
            未設定
        @endif
    </p>
</div>

<!-- Login Count Field -->
<div class="col-sm-12">
    {!! Form::label('login_count', '登入次數:') !!}
    <p>{{ $mainUsers->login_count ?? 0 }}</p>
</div>

<!-- Last Login At Field -->
<div class="col-sm-12">
    {!! Form::label('last_login_at', '最後登入時間:') !!}
    <p>{{ $mainUsers->last_login_at ? $mainUsers->last_login_at->format('Y-m-d H:i:s') : '尚未登入' }}</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $mainUsers->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $mainUsers->updated_at }}</p>
</div>


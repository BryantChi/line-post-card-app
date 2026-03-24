{{-- 方案名稱 --}}
<div class="form-group col-sm-6">
    {!! Form::label('name', '方案名稱 *') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => '例：月繳方案', 'maxlength' => 100]) !!}
</div>

{{-- 價格 --}}
<div class="form-group col-sm-3">
    {!! Form::label('price', '價格（NT$）*') !!}
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text">NT$</span>
        </div>
        {!! Form::number('price', null, ['class' => 'form-control', 'min' => 1, 'max' => 999999]) !!}
    </div>
</div>

{{-- 有效天數 --}}
<div class="form-group col-sm-3">
    {!! Form::label('duration_days', '有效天數 *') !!}
    <div class="input-group">
        {!! Form::number('duration_days', null, ['class' => 'form-control', 'min' => 1, 'max' => 3650]) !!}
        <div class="input-group-append">
            <span class="input-group-text">天</span>
        </div>
    </div>
</div>

{{-- 方案說明 --}}
<div class="form-group col-sm-12">
    {!! Form::label('description', '方案說明') !!}
    {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'maxlength' => 1000, 'placeholder' => '選填，向會員說明此方案的特色']) !!}
</div>

{{-- 排序 --}}
<div class="form-group col-sm-3">
    {!! Form::label('sort_order', '排序') !!}
    {!! Form::number('sort_order', 0, ['class' => 'form-control', 'min' => 0, 'max' => 999]) !!}
    <small class="form-text text-muted">數字越小排越前面</small>
</div>

{{-- 狀態 --}}
<div class="form-group col-sm-3">
    {!! Form::label('active', '狀態') !!}
    {!! Form::select('active', ['1' => '啟用', '0' => '停用'], 1, ['class' => 'form-control']) !!}
</div>

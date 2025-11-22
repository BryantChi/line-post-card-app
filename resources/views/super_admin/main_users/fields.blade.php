<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {{-- {!! Form::text('name', null, ['class' => 'form-control', 'id' => 'name', 'required' => true]) !!} --}}
    <input type="text" name="name" id="name" value="{{ $mainUser->name ?? '' }}" class="form-control" required {{ Request::is('admin/main-users/edit*') ? 'disabled' : '' }}>
</div>

<!-- Email Field -->
<div class="form-group col-sm-6">
    {!! Form::label('email', 'Email:') !!}
    {{-- {!! Form::text('email', $mainUser->email ?? '', ['class' => 'form-control', 'id' => 'email', 'required' => true, 'disabled' => Request::is('admin/main-users/edit*')]) !!} --}}
    <input type="text" name="email" id="email" value="{{ $mainUser->email ?? '' }}" class="form-control" required {{ Request::is('admin/main-users/edit*') ? 'disabled' : '' }}>
</div>

<!-- Password Field -->
<div class="form-group col-sm-6">
    {!! Form::label('password', 'Password:') !!}
    {{-- {!! Form::text('password', null, ['class' => 'form-control', 'id' => 'password', 'type' => 'password']) !!} --}}
    <input type="password" name="password" id="password" class="form-control" placeholder="請輸入密碼，最少6碼" minlength="6">
    @if (Request::is('admin/main-users/*/edit'))
    <span class="help-block text-danger">★若欲變更密碼，才需輸入密碼，最少6碼</span>
    @endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('password_confirmation', 'Password Confirmation:') !!}
    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="請輸入密碼，最少6碼" minlength="6">
    @if (Request::is('admin/main-users/*/edit'))
    <span class="help-block text-danger">★若欲變更密碼，才需輸入密碼，最少6碼</span>
    @endif
</div>

<!-- Active Field -->
<div class="form-group col-sm-6">
    {!! Form::label('active', 'Active:') !!}
    <select name="active" class="form-control">
        <option value="1" {{ ($mainUser->active ?? true) == 1 ? 'selected' : '' }}>是</option>
        <option value="0" {{ ($mainUser->active ?? true) == 0 ? 'selected' : '' }}>否</option>
    </select>
</div>

<!-- Signature Field -->
<div class="form-group col-sm-12">
    {!! Form::label('signature', '自訂署名:') !!}
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text">Design by</span>
        </div>
        <input type="text" name="signature" id="signature" value="{{ old('signature', $mainUser->signature ?? '') }}" class="form-control" maxlength="100" placeholder="誠翊資訊網路應用事業">
    </div>
    <span class="help-block text-muted">
        此署名會顯示在該主帳號及其子帳號的名片分享頁面底部。留空則使用預設值「誠翊資訊網路應用事業」。
    </span>
</div>


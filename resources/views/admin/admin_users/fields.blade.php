<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {{-- {!! Form::text('name', null, ['class' => 'form-control', 'id' => 'name', 'required' => true]) !!} --}}
    <input type="text" name="name" id="name" value="{{ $adminUsers->name ?? '' }}" class="form-control" required {{ Request::is('admin/adminUsers/edit*') ? 'disabled' : '' }}>
</div>

<!-- Email Field -->
<div class="form-group col-sm-6">
    {!! Form::label('email', 'Email:') !!}
    {{-- {!! Form::text('email', $adminUsers->email ?? '', ['class' => 'form-control', 'id' => 'email', 'required' =>
    true, 'disabled' => Request::is('admin/adminUsers/edit*')]) !!} --}}
    <input type="text" name="email" id="email" value="{{ $adminUsers->email ?? '' }}" class="form-control" required {{ Request::is('admin/adminUsers/edit*') ? 'disabled' : '' }}>
</div>

<!-- Phone Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone', '聯絡電話:') !!}
    <input type="tel" name="phone" id="phone" value="{{ old('phone', $adminUsers->phone ?? '') }}" class="form-control"
        maxlength="30" placeholder="例如：0912345678">
    <span class="help-block text-muted">填寫後，名片預覽頁會顯示「打電話」按鈕。</span>
</div>

<!-- LINE URL Field -->
<div class="form-group col-sm-6">
    {!! Form::label('line_url', 'LINE 連結:') !!}
    <input type="url" name="line_url" id="line_url" value="{{ old('line_url', $adminUsers->line_url ?? '') }}"
        class="form-control" maxlength="500" placeholder="例如：https://line.me/ti/p/xxxxxxx">
    <span class="help-block text-muted">填寫 LINE 加好友連結後，名片預覽頁會顯示「加 LINE」按鈕。</span>
</div>

<!-- Password Field -->
<div class="form-group col-sm-6">
    {!! Form::label('password', 'Password:') !!}
    {{-- {!! Form::text('password', null, ['class' => 'form-control', 'id' => 'password', 'type' => 'password']) !!}
    --}}
    <input type="password" name="password" id="password" class="form-control" placeholder="請輸入密碼，最少6碼" minlength="6">
    @if (Request::is('admin/adminUsers/edit*'))
        <span class="help-block text-danger">★若欲變更密碼，才需輸入密碼，最少6碼</span>
    @endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('password_confirmation', 'Password Confirmation:') !!}
    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
        placeholder="請輸入密碼，最少6碼" minlength="6">
    @if (Request::is('admin/adminUsers/edit*'))
        <span class="help-block text-danger">★若欲變更密碼，才需輸入密碼，最少6碼</span>
    @endif
</div>

<!-- Signature Field -->
<div class="form-group col-sm-12">
    {!! Form::label('signature', '自訂署名:') !!}
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text">Design by</span>
        </div>
        <input type="text" name="signature" id="signature" value="{{ old('signature', $adminUsers->signature ?? '') }}"
            class="form-control" maxlength="100" placeholder="誠翊資訊網路應用事業">
    </div>
    <span class="help-block text-muted">
        此署名會顯示在該超級管理員的名片分享頁面底部。留空則使用預設值「誠翊資訊網路應用事業」。
    </span>
</div>
<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', '帳號:') !!}
    {{-- {!! Form::text('name', null, ['class' => 'form-control', 'id' => 'name', 'required' => true]) !!} --}}
    <input type="text" name="name" id="name" value="{{ $subUser->name ?? '' }}" class="form-control" required readonly>
</div>

<!-- Email Field -->
<div class="form-group col-sm-6">
    {!! Form::label('email', 'Email:') !!}
    {{-- {!! Form::text('email', $subUser->email ?? '', ['class' => 'form-control', 'id' => 'email', 'required' => true, 'disabled' => Request::is('admin/sub-users/edit*')]) !!} --}}
    <input type="text" name="email" id="email" value="{{ $subUser->email ?? '' }}" class="form-control" readonly>
</div>

<!-- Password Field -->
<div class="form-group col-sm-6">
    {!! Form::label('password', '密碼:') !!}
    {{-- {!! Form::text('password', null, ['class' => 'form-control', 'id' => 'password', 'type' => 'password']) !!} --}}
    <input type="password" name="password" id="password" class="form-control" placeholder="請輸入密碼，最少6碼" minlength="6">
    @if (Request::is('admin/profile*'))
    <span class="help-block text-danger">★若欲變更密碼，才需輸入密碼，最少6碼</span>
    @endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('password_confirmation', '確認密碼:') !!}
    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="請輸入密碼，最少6碼" minlength="6">
    @if (Request::is('admin/profile*'))
    <span class="help-block text-danger">★若欲變更密碼，才需輸入密碼，最少6碼</span>
    @endif
</div>

<!-- expires_at Field -->
<div class="form-group col-sm-6">
    {!! Form::label('expires_at', '到期日:') !!}
    <input type="date" name="expires_at" id="expires_at" value="{{ \Carbon\Carbon::parse(($subUser->expires_at ?? \Carbon\Carbon::now()->addYear()))->format('Y-m-d') }}" class="form-control datepicker" readonly>
</div>

@if ($subUser->isSuperAdmin() || $subUser->isMainUser())
<!-- Signature Field (僅超級管理員和主帳號可編輯) -->
<div class="form-group col-sm-12">
    {!! Form::label('signature', '自訂署名:') !!}
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text">Design by</span>
        </div>
        <input type="text" name="signature" id="signature" value="{{ $subUser->signature ?? '' }}" class="form-control" maxlength="100" placeholder="誠翊資訊網路應用事業">
    </div>
    <span class="help-block text-muted">
        此署名會顯示在名片分享頁面底部，子帳號會自動繼承您的署名設定。留空則使用預設值「誠翊資訊網路應用事業」。
    </span>
</div>
@endif


<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', '帳號:') !!}
    {{-- {!! Form::text('name', null, ['class' => 'form-control', 'id' => 'name', 'required' => true]) !!} --}}
    <input type="text" name="name" id="name" value="{{ $subUser->name ?? '' }}" class="form-control" required {{ Request::is('admin/sub-users/edit*') ? 'disabled' : '' }}>
</div>

<!-- Email Field -->
<div class="form-group col-sm-6">
    {!! Form::label('email', 'Email:') !!}
    {{-- {!! Form::text('email', $subUser->email ?? '', ['class' => 'form-control', 'id' => 'email', 'required' => true, 'disabled' => Request::is('admin/sub-users/edit*')]) !!} --}}
    <input type="text" name="email" id="email" value="{{ $subUser->email ?? '' }}" class="form-control" required {{ Request::is('admin/sub-users/edit*') ? 'disabled' : '' }}>
</div>

<!-- Password Field -->
<div class="form-group col-sm-6">
    {!! Form::label('password', '密碼:') !!}
    {{-- {!! Form::text('password', null, ['class' => 'form-control', 'id' => 'password', 'type' => 'password']) !!} --}}
    <input type="password" name="password" id="password" class="form-control" placeholder="請輸入密碼，最少6碼" minlength="6">
    @if (Request::is('admin/sub-users/*/edit'))
    <span class="help-block text-danger">★若欲變更密碼，才需輸入密碼，最少6碼</span>
    @endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('password_confirmation', '確認密碼:') !!}
    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="請輸入密碼，最少6碼" minlength="6">
    @if (Request::is('admin/sub-users/*/edit'))
    <span class="help-block text-danger">★若欲變更密碼，才需輸入密碼，最少6碼</span>
    @endif
</div>

<!-- Parent Id Field only for Super Admin User -->
@if (Auth::user()->isSuperAdmin())
<div class="form-group col-sm-6">
    {!! Form::label('parent_id', '主帳號:') !!}
    <select name="parent_id" class="form-control">
        <option value="">請選擇</option>
        @foreach ($mainUsers ?? [] as $mainUser)
            <option value="{{ $mainUser->id }}" {{ (($subUser->parent_id ?? 0) == $mainUser->id) ? 'selected' : '' }}>
                {{ $mainUser->name }} ({{ $mainUser->email }})
            </option>
        @endforeach
    </select>
    <span class="help-block text-danger">★若不設定，預設直屬Super Admin</span>
</div>
@endif

<!-- remarks Field -->
<div class="form-group col-sm-12">
    {!! Form::label('remarks', '備註:') !!}
    <textarea name="remarks" id="remarks" class="form-control" rows="3">{{ $subUser->remarks ?? '' }}</textarea>
    <span class="help-block text-danger">★備註欄位，可用於記錄其他資訊</span>
</div>

<!-- expires_at Field -->
<div class="form-group col-sm-6">
    {!! Form::label('expires_at', '到期日:') !!}
    <input type="date" name="expires_at" id="expires_at" value="{{ \Carbon\Carbon::parse(($subUser->expires_at ?? \Carbon\Carbon::now()->addYear()))->format('Y-m-d') }}" class="form-control datepicker">
    <span class="help-block text-danger">★若不設定，預設有效期限一年</span>
</div>


<!-- Active Field -->
<div class="form-group col-sm-6">
    {!! Form::label('active', '啟用狀態:') !!}
    <select name="active" class="form-control">
        <option value="1" {{ ($subUser->active ?? true) == 1 ? 'selected' : '' }}>是</option>
        <option value="0" {{ ($subUser->active ?? true) == 0 ? 'selected' : '' }}>否</option>
    </select>
</div>


<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
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
    {!! Form::label('password', 'Password:') !!}
    {{-- {!! Form::text('password', null, ['class' => 'form-control', 'id' => 'password', 'type' => 'password']) !!} --}}
    <input type="password" name="password" id="password" class="form-control" placeholder="請輸入密碼，最少6碼" minlength="6">
    @if (Request::is('admin/sub-users/edit*'))
    <span class="help-block text-danger">★若欲變更密碼，才需輸入密碼，最少6碼</span>
    @endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('password_confirmation', 'Password Confirmation:') !!}
    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="請輸入密碼，最少6碼" minlength="6">
    @if (Request::is('admin/sub-users/edit*'))
    <span class="help-block text-danger">★若欲變更密碼，才需輸入密碼，最少6碼</span>
    @endif
</div>

<!-- Parent Id Field only for Super Admin User -->
@if (Auth::user()->isSuperAdmin())
<div class="form-group col-sm-6">
    {!! Form::label('parent_id', 'Parent Id:') !!}
    <select name="parent_id" class="form-control">
        <option value="">請選擇</option>
        @foreach ($mainUsers ?? [] as $mainUser)
            <option value="{{ $mainUser->id }}" {{ (isset($subUser) && $subUser->parent_id == $mainUser->id) ? 'selected' : '' }}>
                {{ $mainUser->name }} ({{ $mainUser->email }})
            </option>
        @endforeach
    </select>
    <span class="help-block text-danger">★若不設定，預設直屬Super Admin</span>
</div>
@endif

<!-- expires_at Field -->
<div class="form-group col-sm-6">
    {!! Form::label('expires_at', 'Expires At:') !!}
    <input type="date" name="expires_at" id="expires_at" value="{{ $subUser->expires_at ?? '' }}" class="form-control datepicker">
    <span class="help-block text-danger">★若不設定，則為永久有效</span>
</div>


<!-- Active Field -->
<div class="form-group col-sm-6">
    {!! Form::label('active', 'Active:') !!}
    <select name="active" class="form-control">
        <option value="1" {{ ($subUser->active ?? true) == 1 ? 'selected' : '' }}>是</option>
        <option value="0" {{ ($subUser->active ?? true) == 0 ? 'selected' : '' }}>否</option>
    </select>
</div>


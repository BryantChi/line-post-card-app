@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>手動延長到期日</h1>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('sub-users.edit', $subUser->id) }}" class="btn btn-default float-right">
                        <i class="fas fa-arrow-left"></i> 返回會員編輯
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="card" style="max-width: 640px;">
            <div class="card-header">
                <h3 class="card-title">
                    會員：<strong>{{ $subUser->name }}</strong>
                    <small class="text-muted ml-2">{{ $subUser->email }}</small>
                </h3>
            </div>
            <div class="card-body">

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    目前到期日：
                    @if($subUser->expires_at)
                        <strong>{{ $subUser->expires_at->format('Y-m-d H:i') }}</strong>
                        @if($subUser->expires_at->isPast())
                            <span class="badge badge-danger ml-1">已過期</span>
                        @endif
                    @else
                        <strong class="text-muted">尚未設定</strong>
                    @endif
                </div>

                {!! Form::open(['route' => ['admin.subUsers.manualExtend.process', $subUser->id], 'method' => 'POST']) !!}

                    <div class="form-group">
                        <label for="days" class="font-weight-bold">延長天數 <span class="text-danger">*</span></label>
                        <input type="number" name="days" id="days"
                               class="form-control @error('days') is-invalid @enderror"
                               min="1" max="3650"
                               value="{{ old('days', 30) }}"
                               style="max-width: 200px;">
                        <small class="form-text text-muted">可輸入 1 ~ 3650 天</small>
                        @error('days')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="reason" class="font-weight-bold">延長原因 <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reason" rows="3"
                                  class="form-control @error('reason') is-invalid @enderror"
                                  placeholder="請填寫延長原因，例如：客服補償、系統問題補償..."
                                  style="max-width: 500px;">{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-4">
                        {!! Form::submit('確認延長', ['class' => 'btn btn-primary']) !!}
                        <a href="{{ route('sub-users.edit', $subUser->id) }}" class="btn btn-default ml-2">取消</a>
                    </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>登入紀錄</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">篩選條件</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.login-logs.index') }}" id="filter-form">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="start_date">開始日期</label>
                                <input type="date"
                                       name="start_date"
                                       id="start_date"
                                       class="form-control"
                                       value="{{ request('start_date') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="end_date">結束日期</label>
                                <input type="date"
                                       name="end_date"
                                       id="end_date"
                                       class="form-control"
                                       value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="user_id">用戶</label>
                                <select name="user_id" id="user_id" class="form-control">
                                    <option value="">全部用戶</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}"
                                                {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="ip_address">IP 位址</label>
                                <input type="text"
                                       name="ip_address"
                                       id="ip_address"
                                       class="form-control"
                                       placeholder="請輸入 IP"
                                       value="{{ request('ip_address') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> 查詢
                            </button>
                            <a href="{{ route('admin.login-logs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> 重置
                            </a>
                            <button type="button" id="export-btn" class="btn btn-success float-right">
                                <i class="fas fa-file-excel"></i> 匯出 Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 12%">用戶帳號</th>
                                <th style="width: 25%">備註</th>
                                <th style="width: 12%">Email</th>
                                <th style="width: 13%">登入時間</th>
                                <th style="width: 13%">登出時間</th>
                                <th style="width: 10%">線上時長</th>
                                <th style="width: 10%">IP 位址</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->user->name ?? '-' }}</td>
                                <td>
                                    <small class="text-muted">
                                        {{ $log->user->remarks ?? '-' }}
                                    </small>
                                </td>
                                <td>{{ $log->user->email ?? '-' }}</td>
                                <td>{{ $log->logged_in_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    @if($log->logged_out_at)
                                        {{ $log->logged_out_at->format('Y-m-d H:i:s') }}
                                    @else
                                        <span class="badge badge-success">線上</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->session_duration)
                                        <small>{{ $log->formatted_duration }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $log->ip_address ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    <i class="fas fa-inbox"></i> 無登入紀錄
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer clearfix">
                    <div class="float-left">
                        <small class="text-muted">
                            共 {{ $logs->total() }} 筆紀錄
                        </small>
                    </div>
                    <div class="float-right">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('page_scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
$(document).ready(function() {
    // 匯出功能
    $('#export-btn').on('click', function() {
        // 複製目前的篩選條件
        let form = $('#filter-form').clone();

        // 修改 action 為匯出路由
        form.attr('action', '{{ route("admin.login-logs.export") }}');
        form.attr('method', 'POST');

        // 加入 CSRF Token
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: '{{ csrf_token() }}'
        }));

        // 隱藏並提交表單
        form.hide().appendTo('body').submit();
    });
});
</script>
@endpush

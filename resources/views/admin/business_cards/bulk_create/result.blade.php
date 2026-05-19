@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <h1>批次建立結果</h1>
    </div>
</section>

<div class="content px-3">
    @include('flash::message')

    @php
        $success = $report['successRows'] ?? [];
        $skipped = $report['skippedRows'] ?? [];
        $failed = array_merge($previewFailed ?? [], $report['failedRows'] ?? []);
    @endphp

    <div class="row">
        <div class="col-md-4"><div class="info-box bg-success"><div class="info-box-content">
            <span class="info-box-text">成功建立</span>
            <span class="info-box-number">{{ count($success) }}</span>
        </div></div></div>
        <div class="col-md-4"><div class="info-box bg-warning"><div class="info-box-content">
            <span class="info-box-text">略過(skip 模式)</span>
            <span class="info-box-number">{{ count($skipped) }}</span>
        </div></div></div>
        <div class="col-md-4"><div class="info-box bg-danger"><div class="info-box-content">
            <span class="info-box-text">失敗</span>
            <span class="info-box-number">{{ count($failed) }}</span>
        </div></div></div>
    </div>

    @if(count($failed) > 0)
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">失敗列詳細</h5>
            <div class="card-tools">
                <a href="{{ route('admin.businessCards.bulkCreate.failedReport', ['token' => $token]) }}" class="btn btn-sm btn-outline-danger">
                    <i class="fa fa-download"></i> 下載失敗報告 Excel
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="thead-light"><tr><th>列號</th><th>user_id</th><th>失敗原因</th></tr></thead>
                <tbody>
                    @foreach($failed as $f)
                    <tr>
                        <td>{{ $f['row_index'] ?? '-' }}</td>
                        <td>{{ $f['user_id'] ?? '-' }}</td>
                        <td class="text-danger">{{ $f['error_reason'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if(count($success) > 0)
    <div class="card">
        <div class="card-header"><h5 class="card-title">成功列</h5></div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="thead-light"><tr><th>列號</th><th>user_id</th><th>card_id</th><th>模式</th></tr></thead>
                <tbody>
                    @foreach($success as $s)
                    <tr>
                        <td>{{ $s['row_index'] }}</td>
                        <td>{{ $s['user_id'] }}</td>
                        <td>{{ $s['card_id'] }}</td>
                        <td><span class="badge badge-info">{{ $s['mode'] }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if(count($skipped) > 0)
    <div class="card">
        <div class="card-header"><h5 class="card-title">略過列</h5></div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="thead-light"><tr><th>列號</th><th>user_id</th><th>原因</th></tr></thead>
                <tbody>
                    @foreach($skipped as $k)
                    <tr><td>{{ $k['row_index'] }}</td><td>{{ $k['user_id'] }}</td><td>{{ $k['reason'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="text-right mt-3">
        <a href="{{ route('admin.businessCards.bulkCreate.wizard') }}" class="btn btn-secondary">再執行一次</a>
        <a href="{{ route('admin.businessCards.index') }}" class="btn btn-primary">回到名片列表</a>
    </div>
</div>
@endsection

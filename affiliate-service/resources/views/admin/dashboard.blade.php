@extends('layouts.admin')
@section('title', 'ダッシュボード')

@section('content')
<h1 class="h4 mb-4">ダッシュボード</h1>

<div class="row mb-4 g-3">
    <div class="col-md-3 col-6">
        <div class="card card-body h-100">
            <div class="text-muted small">承認待ち</div>
            <div class="h3 mb-0">{{ $pendingAffiliates }}</div>
            <a href="{{ route('admin.affiliates.index', ['status' => 'pending']) }}" class="small">確認する</a>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-body h-100">
            <div class="text-muted small">承認済みアフィリエイター</div>
            <div class="h3 mb-0">{{ $approvedAffiliates }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-body h-100">
            <div class="text-muted small">当月の売上 <span class="fw-normal">(アフィリエイト経由)</span></div>
            <div class="h3 mb-0">{{ number_format($salesThisMonth) }} <small class="fs-6">円</small></div>
            <div class="small text-muted">前月：{{ number_format($salesLastMonth) }} 円</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-body h-100">
            <div class="text-muted small">当月の新規登録者</div>
            <div class="h3 mb-0">{{ $regThisMonth }} <small class="fs-6">人</small></div>
            <div class="small text-muted">前月：{{ $regLastMonth }} 人</div>
        </div>
    </div>
</div>

<div class="row mb-4 g-3">
    <div class="col-lg-6">
        <div class="card card-body">
            <h2 class="h6 mb-3">売上推移 <span class="text-muted small fw-normal">(直近12ヶ月・アフィリエイト経由)</span></h2>
            <canvas id="salesChart" height="140"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-body">
            <h2 class="h6 mb-3">新規登録者数の推移 <span class="text-muted small fw-normal">(直近12ヶ月)</span></h2>
            <canvas id="regChart" height="140"></canvas>
        </div>
    </div>
</div>

<h2 class="h6">報酬サマリー <span class="text-muted small fw-normal">(全期間)</span></h2>
<div class="row g-3">
    @foreach (['pending', 'confirmed', 'paid', 'cancelled'] as $st)
        <div class="col-6 col-md">
            <div class="card card-body">
                <div class="text-muted small">{{ $statusLabels[$st] }}</div>
                <div class="h5 mb-0">{{ number_format(optional($totals->get($st))->total ?? 0) }} 円</div>
                <div class="small text-muted">{{ optional($totals->get($st))->cnt ?? 0 }} 件</div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels = @json($chartLabels);

    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: { labels: labels, datasets: [{
            label: '売上', data: @json($chartSales),
            borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,.1)', fill: true, tension: .3
        }] },
        options: {
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: function (c) { return Number(c.parsed.y).toLocaleString() + '円'; } } }
            },
            scales: { y: { beginAtZero: true, ticks: { callback: function (v) { return Number(v).toLocaleString(); } } } }
        }
    });

    new Chart(document.getElementById('regChart'), {
        type: 'bar',
        data: { labels: labels, datasets: [{
            label: '新規登録', data: @json($chartReg), backgroundColor: '#198754'
        }] },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
})();
</script>
@endsection

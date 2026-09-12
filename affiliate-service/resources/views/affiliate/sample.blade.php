@extends('layouts.app')
@section('title', 'サンプルのお申し込み')

@section('content')
<h1 class="h4 mb-3">サンプル商品のお申し込み</h1>

<div class="card mb-3">
    <div class="card-body">
        <div class="fw-bold">{{ $setting->sample_product_name }}</div>
        @if ($setting->sample_product_url)
            <a href="{{ $setting->sample_product_url }}" target="_blank" rel="noopener" class="small">商品の詳細を見る</a>
        @endif
        <div class="small text-muted mt-2">
            ※サンプルのご提供は<strong>ペットフードのみ</strong>とさせていただいております。<br>
            馬刺しセットは配送方法を検討中のため、現在お申し込みいただけません。
        </div>
        <div class="small text-muted mt-2">
            お一人さま1回限りです。ヤマト運輸ネコポスでポストへお届けします。
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="post" action="{{ route('affiliate.sample.store') }}">
    @csrf

    <h2 class="h6 text-muted border-bottom pb-2 mb-3">お届け先</h2>
    <div class="alert alert-light border small">
        ご登録の住所を入れてあります。違う場所へお届けする場合は書き換えてください。
    </div>

    <div class="mb-3">
        <label class="form-label">お名前 <span class="text-danger">*</span></label>
        <input type="text" name="recipient_name" value="{{ old('recipient_name', $affiliate->name) }}" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">郵便番号 <span class="text-danger">*</span></label>
        <div class="input-group" style="max-width: 320px;">
            <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $affiliate->postal_code) }}" class="form-control" placeholder="1234567（ハイフン無し）" required>
            <button type="button" id="zip-search" class="btn btn-outline-secondary">住所自動入力</button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">都道府県 <span class="text-danger">*</span></label>
            <input type="text" name="prefecture" id="prefecture" value="{{ old('prefecture', $affiliate->prefecture) }}" class="form-control" required>
        </div>
        <div class="col-md-8 mb-3">
            <label class="form-label">市区町村 <span class="text-danger">*</span></label>
            <input type="text" name="city" id="city" value="{{ old('city', $affiliate->city) }}" class="form-control" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">番地 <span class="text-danger">*</span></label>
        <input type="text" name="address1" value="{{ old('address1', $affiliate->address1) }}" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">建物名・部屋番号 <span class="text-muted small">(任意)</span></label>
        <input type="text" name="address2" value="{{ old('address2', $affiliate->address2) }}" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">電話番号 <span class="text-danger">*</span></label>
        <input type="text" name="phone" value="{{ old('phone', $affiliate->phone) }}" class="form-control" style="max-width: 320px;" required>
    </div>

    <div class="mb-3">
        <label class="form-label">ご要望 <span class="text-muted small">(任意)</span></label>
        <textarea name="note" rows="3" class="form-control">{{ old('note') }}</textarea>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">この内容で申し込む</button>
        <a href="{{ route('affiliate.mypage') }}" class="btn btn-outline-secondary">戻る</a>
    </div>
</form>

<script>
document.getElementById('zip-search').addEventListener('click', function () {
    var zip = document.getElementById('postal_code').value.replace(/[^0-9]/g, '');
    if (zip.length !== 7) {
        alert('郵便番号を7桁（ハイフン無し）で入力してください。');
        return;
    }
    fetch('https://zipcloud.ibsnet.co.jp/api/search?zipcode=' + zip)
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.results && data.results[0]) {
                var r = data.results[0];
                document.getElementById('prefecture').value = r.address1;
                document.getElementById('city').value = r.address2 + r.address3;
            } else {
                alert('住所が見つかりませんでした。郵便番号をご確認ください。');
            }
        })
        .catch(function () {
            alert('住所の自動取得に失敗しました。お手数ですが手入力してください。');
        });
});
</script>
@endsection

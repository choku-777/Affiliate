@extends('layouts.app')
@section('title', 'アンバサダー登録')

@section('content')
<h1 class="h4 mb-3">アンバサダー登録</h1>
<p class="text-muted">お申し込み後、管理者の承認をもってご紹介用URLをメールでお送りします。登録したメールアドレスとパスワードでマイページにログインできます。</p>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="post" action="{{ route('register.store') }}" class="card card-body">
    @csrf

    <h2 class="h6 text-muted border-bottom pb-2 mb-3">基本情報</h2>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">姓 <span class="text-danger">*</span></label>
            <input type="text" name="last_name" value="{{ old('last_name') }}" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">名 <span class="text-danger">*</span></label>
            <input type="text" name="first_name" value="{{ old('first_name') }}" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">姓（フリガナ） <span class="text-danger">*</span></label>
            <input type="text" name="last_name_kana" value="{{ old('last_name_kana') }}" class="form-control" placeholder="ヤマダ" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">名（フリガナ） <span class="text-danger">*</span></label>
            <input type="text" name="first_name_kana" value="{{ old('first_name_kana') }}" class="form-control" placeholder="タロウ" required>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">生年月日 <span class="text-danger">*</span></label>
            <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label d-block">性別 <span class="text-danger">*</span></label>
            <div class="form-check form-check-inline mt-2">
                <input class="form-check-input" type="radio" name="gender" id="gender_male" value="male" @checked(old('gender') === 'male') required>
                <label class="form-check-label" for="gender_male">男性</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="gender" id="gender_female" value="female" @checked(old('gender') === 'female') required>
                <label class="form-check-label" for="gender_female">女性</label>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">電話番号 <span class="text-danger">*</span></label>
        <input type="tel" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="09012345678（ハイフン無し）" required>
    </div>

    <h2 class="h6 text-muted border-bottom pb-2 mb-3 mt-2">活動について</h2>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">主に使うSNS <span class="text-danger">*</span></label>
            @php $snsList = ['X（旧Twitter）', 'Instagram', 'TikTok', 'YouTube', 'Facebook', 'ブログ・ウェブサイト', 'その他']; @endphp
            <select name="sns" class="form-select" required>
                <option value="">選択してください</option>
                @foreach ($snsList as $snsName)
                    <option value="{{ $snsName }}" @selected(old('sns') === $snsName)>{{ $snsName }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">アカウント名 <span class="text-danger">*</span></label>
            <input type="text" name="sns_account" value="{{ old('sns_account') }}" class="form-control" placeholder="@your_account など" required>
        </div>
    </div>

    <h2 class="h6 text-muted border-bottom pb-2 mb-3 mt-2">ログイン情報</h2>
    <div class="mb-3">
        <label class="form-label">メールアドレス <span class="text-danger">*</span></label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
        <div class="form-text">ログインIDになります。</div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">パスワード <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" required>
            <div class="form-text">8文字以上</div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">パスワード（確認） <span class="text-danger">*</span></label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
    </div>

    <h2 class="h6 text-muted border-bottom pb-2 mb-3 mt-2">住所</h2>
    <div class="mb-3">
        <label class="form-label">郵便番号 <span class="text-danger">*</span></label>
        <div class="input-group" style="max-width: 320px;">
            <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}" class="form-control" placeholder="1234567（ハイフン無し）" required>
            <button type="button" id="zip-search" class="btn btn-outline-secondary">住所自動入力</button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">都道府県 <span class="text-danger">*</span></label>
            <input type="text" name="prefecture" id="prefecture" value="{{ old('prefecture') }}" class="form-control" required>
        </div>
        <div class="col-md-8 mb-3">
            <label class="form-label">市区町村 <span class="text-danger">*</span></label>
            <input type="text" name="city" id="city" value="{{ old('city') }}" class="form-control" required>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">番地 <span class="text-danger">*</span></label>
        <input type="text" name="address1" value="{{ old('address1') }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">建物名・部屋番号 <span class="text-muted small">(任意)</span></label>
        <input type="text" name="address2" value="{{ old('address2') }}" class="form-control">
    </div>

    <h2 class="h6 text-muted border-bottom pb-2 mb-3 mt-2">報酬の振込先 <span class="text-muted small fw-normal">(任意・後から登録も可)</span></h2>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">銀行名</label>
            <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">支店名</label>
            <input type="text" name="bank_branch" value="{{ old('bank_branch') }}" class="form-control">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">口座種別</label>
            <select name="account_type" class="form-select">
                <option value="">選択</option>
                <option value="普通" @selected(old('account_type') === '普通')>普通</option>
                <option value="当座" @selected(old('account_type') === '当座')>当座</option>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">口座番号</label>
            <input type="text" name="account_number" value="{{ old('account_number') }}" class="form-control">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">口座名義</label>
            <input type="text" name="account_holder" value="{{ old('account_holder') }}" class="form-control">
        </div>
    </div>

    <h2 class="h6 text-muted border-bottom pb-2 mb-3 mt-2">利用規約</h2>
    <p class="text-muted small mb-2">下記の規約を最後までご確認のうえ、同意してお進みください。</p>
    <div class="border rounded p-3 mb-3 bg-light" style="max-height: 240px; overflow-y: auto;">
        @include('partials.affiliate-terms')
    </div>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="agree" id="agree" value="1" @checked(old('agree')) required>
        <label class="form-check-label" for="agree">
            上記の利用規約をすべて確認し、同意します <span class="text-danger">*</span>
        </label>
    </div>

    <div>
        <button type="submit" class="btn btn-primary">登録する</button>
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

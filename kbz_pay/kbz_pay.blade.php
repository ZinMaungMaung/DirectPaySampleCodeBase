<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KBZ Pay - Mother Finance</title>
    @include('partials.header-meta') @include('partials.css-links')
</head>

<body>
@include('partials.sub-page-nav',['lang'=>'en'])
<br>
<div class="container">
    <div class="row">
        <div class="col-12 text-center">
            <p>Repayment Amount (Included Service Fee 2%): <strong>{{ $amount }} MMK</strong></p>
            <a href="{{ $link }}" class="btn btn-primary">KPZ Pay ဖြင့် ငွေပေးချေရန် နှိပ်ပါ</a>
        </div>
    </div>
</div>
</div>
</body>
</html>
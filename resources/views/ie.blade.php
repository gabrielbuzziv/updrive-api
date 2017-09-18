<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="icon" href="{{ url('/images/favicon.ico') }}" />

    <title>{{ config('app.name')  }}</title>

    <link rel="stylesheet" href="{{ elixir('css/all.css') }}">
</head>
<body>
    <div id="unsupported" class="col-md-12">
        <div class="container">
            <h1 class="text-center">Navegador não suportado</h1>
            <p class="text-center">O navegador que você está utilizando não é suportado pelo UP Cont no momento.</p>

            <ul class="browser">
                <li>
                    <img src="/images/chrome.ico" alt="">
                    <a href="https://www.google.com.br/chrome/browser/desktop/" class="btn btn-success btn-block margin-top-30">
                        Baixar Google Chrome
                    </a>
                </li>
                <li>
                    <img src="/images/firefox.png" alt="">
                    <a href="https://www.mozilla.org/pt-BR/firefox/new/" class="btn btn-success btn-block margin-top-30">
                        Baixar Mozilla Firefox
                    </a>
                </li>
            </ul>
        </div>
    </div>
</body>
</html>

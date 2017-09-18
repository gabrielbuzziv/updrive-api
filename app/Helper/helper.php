<?php

function setActiveAccount($account) {
    config(['account' => $account]);
    config(['database.connections.account.database' => $account->slug]);
    config(['app.url' => parseUrl(config('app.url'), $account)]);
    config(['app.frontend' => parseUrl(config('app.frontend'), $account)]);
}

function parseUrl($url, $account)
{
    return str_replace('{account}', $account->slug, $url);
}

function logMessage($exception, $message = 'Erro')
{
    return sprintf("%s
            Arquivo: %s
            Linha: %s
            Mensagem: %s
            ------------------------
            %s
        ",
        $message,
        $exception->getFile(),
        $exception->getLine(),
        $exception->getMessage(),
        htmlentities($exception->getTraceAsString())
    );
}

function logUser () {
    return [
        'user' => [
            'id' => Auth::user()->id,
            'name' => Auth::user()->name
        ]
    ];
}
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link href="http://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css">

    <style type="text/css" rel="stylesheet" media="all">
        /* Media Queries */
        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>
</head>

<?php

$colors = [
        'theme-color'     => '#4a90e2',
        'success-color'   => '#27ae60',
        'danger-color'    => '#c9302c',
        'warning-color'   => '#e6ad5c',
        'primary-color'   => '#4a90e2',
        'secondary-color' => '#f34167',
        'dark-color'      => '#333',
        'text-color'      => '#444'
];

$maxSize = '700px';

$style = [
    /* Layout ------------------------------ */

        'body'          => 'margin: 0; padding: 0; width: 100%; background-color: #f3f3f3;',
        'email-wrapper' => 'width: 100%; margin: 0; padding: 20px 0 0 0; text-align: center;',

    /* Logo ----------------------- */

        'email-logo'      => 'padding: 25px 0; text-align: center;',
        'email-logo_name' => 'background: dark-color; border-radius: 3px; color: #fff; display: inline-block; font-size: 20px;
                          font-style: italic; margin: 0; padding-top: 10px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px;
                          text-decoration: none;',

    /* Header ----------------------- */

        'email-header'             => 'padding-top: 25px; padding-right: 0; padding-bottom: 25px; padding-left: 0; text-align: center;',
        'email-header_subject'     => 'color: theme-color; font-size: 30px; font-weight: 300; margin: 0 0 10px;',
        'email-header_description' => 'color: #777; font-size: 16px; font-weight: 300; line-height: 24px; margin: 0;',

        'email-body'       => 'background: #fff; border-radius: 3px;',
        'email-body_inner' => 'width: 100%; margin: 0 auto; padding: 0;',
        'email-body_cell'  => 'padding-top: 30px; padding-right: 30px; padding-bottom: 30px; padding-left: 30px;',

        'email-footer'           => 'width: auto; max-width: 100%; margin: 0 auto; padding-top: 15px; padding-bottom: 15px; text-align: center;',
        'email-footer_paragraph' => 'color: #ACACAC; font-size: 11px; line-height: 20px; text-align: center; margin-bottom: 25px;',
        'email-footer_anchor'    => 'color: #ACACAC; font-size: 11px;',

    /* Body ------------------------------ */

        'body_action' => 'width: 100%; margin: 0 auto; padding: 25px 0 0; text-align: center;',
        'body_sub'    => 'margin-top: 25px; padding-top: 25px; border-top: 1px solid #EDEFF2;',

    /* Type ------------------------------ */

        'anchor'           => 'color: #3869D4;',
        'header-1'         => 'margin-top: 0; color: dark-color; font-size: 20px; font-weight: bold; text-align: left;',
        'paragraph'        => 'margin-top: 0; color: text-color; font-size: 14px; line-height: 1.5em; text-align: left;',
        'paragraph-sub'    => 'margin-top: 0; color: text-color; font-size: 12px; line-height: 1.5em;',
        'paragraph-center' => 'text-align: center;',
        'list'             => 'padding: 20px 0; display: block;',
        'list_body'        => 'padding: 10px; border-left: 3px solid success-color; display: block;',
        'list-item'        => 'color: #74787E; font-size: 14px; padding-top: 5px; padding-bottom: 5px; text-align: left;',
        'list-title'       => 'color: success-color; font-weight: bold; padding-bottom: 20px; text-align: left;',
        'regards'          => 'width: auto; max-width: 100%; font-size: 14px; color: #74787E; margin: 0 auto; padding-top: 25px; padding-bottom: 0; text-align: left;',
        'regards_p'        => 'font-size: 14px; color: #74787E; margin-bottom: 0; text-align: left;',

    /* Buttons ------------------------------ */

        'button' => 'display: inline-block; color: #fff;
                 background-color: #3869D4; border-radius: 3px;
                 font-weight: 300; text-transform: uppercase;
                 text-align: center; text-decoration: none; -webkit-text-size-adjust: none;',

        'button_anchor' => 'border-radius: 3px; line-height: 40px; width: 100%; color: #fff; display: inline-block; font-size: 15px;
                            text-decoration: none; -webkit-text-size-adjust: none; text-transform: uppercase;',

        'button--green'         => 'background-color: success-color;',
        'button--red'           => 'background-color: danger-color;',
        'button--blue'          => 'background-color: primary-color;',

    /* Text ---------------------------- */
        'text_right'            => 'text-align: right;',

    /* Documents ---------------------------- */
        'documents_table_wrap'  => 'width: 100%; padding: 20px 0 0; border: 0;',
        'documents_tr_wrap'     => 'width: 100%; padding: 0; border: 0;',
        'documents_td_wrap'     => 'width: 100%; padding: 0; border: 0;',
        'documents_table'       => 'width: 100%; border: 1px solid #ddd; border-top: 4px solid primary-color; padding: 10px;',
        'documents_thead'       => 'width: 100%;',
        'documents_tr'          => '',
        'documents_th'          => 'padding: 10px; border-bottom: 3px solid #efefef; vertical-align: middle; background: #fff; border-top: 0; text-transform: uppercase; font-size: 12px; color: dark-color; text-align: left;',
        'documents_tbody'       => '',
        'documents_td'          => 'padding: 15px 10px; color: dark-color; vertical-align: middle; border-bottom: 1px solid #efefef; background: #fff; text-align: left;',
        'documents_td_noborder' => 'padding: 15px 10px; color: dark-color; vertical-align: middle; background: #fff; text-align: left;',
        'documents_name'        => 'color: text-color; display: block; font-size: 14px;',
        'documents_date'        => 'display: block; margin-top: 7px; font-size: 10px; font-weight: 300;',
        'documents_divider'     => 'margin: 0 5px; display: inline-block;',
        'documents_company'     => 'font-size: 14px; display: block;',
        'documents_category'    => 'font-size: 12px; margin-top: 7px; font-size: 600; display: block;',
        'documents_button'      => 'background: #fff; border-radius: 2px; border: 1px solid #bdc3d1; font-size: 13px; text-align: center; padding: 10px 12px 9px; color: #696c74; text-decoration: none;',
        'documents_paragraph'   => 'margin: 0; color: text-color; font-size: 14px; line-height: 1.5em; text-align: left;',
];

$style = str_replace(array_keys($colors), array_values($colors), $style);
$fontFamily = 'font-family: "Open Sans", "Source Sans Pro", Arial, "Helvetica Neue", Helvetica, sans-serif;';
?>

<body style="{{ $style['body'] }}">
    <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td style="{{ $style['email-wrapper'] }}">
                <table cellpadding="0" cellspacing="0" width="{{ $maxSize }}" align="center">

                @if ($description)
                    <!-- Header -->
                        <tr>
                            <td style="{{ $style['email-header'] }}">
                                <h1 style="{{ $fontFamily }} {{ $style['email-header_subject'] }}">
                                    {!! $description !!}
                                </h1>
                                <p style="{{ $fontFamily }} {{ $style['email-header_description'] }}">

                                </p>
                            </td>
                        </tr>
                @endif

                <!--  Image -->

                    <!-- Body -->
                    <tr>
                        <td style="{{ $style['email-body'] }}">
                            <table style="{{ $style['email-body_inner'] }}" align="center" width="100%" cellpadding="0"
                                   cellspacing="0">
                                <tr>
                                    <td style="{{ $fontFamily }} {{ $style['email-body_cell'] }}">

                                        <h2 style="{{ $style['header-1'] }}">
                                            {{ $subject }}
                                        </h2>

                                        @foreach ($introLines as $line)
                                            <p style="{{ $style['paragraph'] }}">
                                                {!! $line !!}
                                            </p>
                                        @endforeach

                                    <!-- Action Button -->
                                        @if (isset($actionText))
                                            <?php
                                            switch ($level) {
                                                case 'success':
                                                    $actionColor = 'button--green';
                                                    break;
                                                case 'error':
                                                    $actionColor = 'button--red';
                                                    break;
                                                default:
                                                    $actionColor = 'button--blue';
                                            }
                                            ?>


                                            <table style="{{ $style['body_action'] }}" align="center" width="100%"
                                                   cellpadding="0" cellspacing="0" align="center">
                                                <tr>
                                                    <td align="center"
                                                        style="{{ $fontFamily }} {{ $style['button'] }}  {{ $style[$actionColor] }}"
                                                        width="300" height="40">
                                                        <a href="{{ $actionUrl }}"
                                                           style="{{ $fontFamily }} {{ $style['button_anchor'] }} {{ $style[$actionColor] }}"
                                                           class="button"
                                                           target="_blank">
                                                            {{ $actionText }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>

                                        @endif

                                    <!-- Outro -->
                                        @foreach ($outroLines as $line)
                                            <p style="{{ $style['paragraph'] }}">
                                                {!! $line !!}
                                            </p>
                                        @endforeach

                                        @if (! empty($documents))
                                            <table style="{{ $style['documents_table_wrap'] }}">
                                                <tr style="{{ $style['documents_tr_wrap'] }}">
                                                    <td style="{{ $style['documents_td_wrap'] }}">
                                                        <table style="{{ $style['documents_table'] }}">
                                                            <thead style="{{ $style['documents_thead'] }}">
                                                                <tr style="{{ $style['documents_tr'] }}">
                                                                    <th style="{{ $style['documents_th'] }}">Documento
                                                                    </th>
                                                                    <th style="{{ $style['documents_th'] }}">
                                                                        Competência
                                                                    </th>
                                                                    <th style="{{ $style['documents_th'] }}">
                                                                        Vencimento
                                                                    </th>
                                                                    <th style="{{ $style['documents_th'] }}"></th>
                                                                </tr>
                                                            </thead>

                                                            <tbody style="{{ $style['documents_tbody'] }}">
                                                                @foreach ($documents as $document)
                                                                    <?php $document = (object) $document; ?>
                                                                    <tr style="{{ $style['documents_tr'] }}">
                                                                        <td style="{{ $loop->last ? $style['documents_td_noborder'] : $style['documents_td'] }}">
                                                                            <span style="{{ $style['documents_name'] }}">
                                                                                {{ $document->name }}
                                                                            </span>
                                                                        </td>
                                                                        <td style="{{ $loop->last ? $style['documents_td_noborder'] : $style['documents_td'] }}">
                                                                            <p style="{{ $style['documents_paragraph'] }}">
                                                                                @if (! empty($document->cycle))
                                                                                    {{ $document->cycle }}
                                                                                @else
                                                                                    -
                                                                                @endif
                                                                            </p>
                                                                        </td>
                                                                        <td style="{{ $loop->last ? $style['documents_td_noborder'] : $style['documents_td'] }}">
                                                                            <p style="{{ $style['documents_paragraph'] }}">
                                                                                @if (! empty($document->validity))
                                                                                    {{ $document->validity }}
                                                                                @else
                                                                                    -
                                                                                @endif
                                                                            </p>
                                                                        </td>
                                                                        <td style="{{ $loop->last ? $style['documents_td_noborder'] : $style['documents_td'] }} {{ $style['text_right'] }}">
                                                                            <a href="{{ action('AuthController@refreshToken', [config('account')->slug]) }}?token={{ $token }}&redirect={{ $document->links['download'] }}"
                                                                               style="{{ $fontFamily }} {{ $style['documents_button'] }}">Baixar</a>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        @endif

                                        @if ($regards)
                                            <p style="{{ $fontFamily }} {{ $style['regards'] }}">
                                                Atenciosamente, <br/>
                                                <b>{{ $regards }}</b>
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    @if ($documents)
                        <tr>
                            <td style="{{ $style['email-footer'] }}">
                                <p style="{{ $fontFamily }} {{ $style['email-footer_paragraph'] }}">

                                    Ao baixar os documentos através deste e-mail você está concordando que recebeu os
                                    documentos enviados.<br>
                                    Quer ter mais controle dos documentos que te enviamos?
                                    <a href="{{ config('app.frontend') }}/login?token={{ $token }}"
                                       style="{{ $style['email-footer_anchor'] }}">Acesse
                                        o {{ config('app.name') }}</a>
                                </p>
                            </td>
                        </tr>
                    @endif

                </table>
            </td>
        </tr>
    </table>
</body>


</html>

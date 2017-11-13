<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Protocolo de envio de documento: {{ $document->name }}</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">

    <style type="text/css">
        .page-break {
            page-break-after: always;
        }

        .protocol {
            font-family: "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
            width: 100%;
        }

        .protocol .header td {
            /*border-bottom: 2px solid #ddd;*/
            padding-bottom: 30px;
        }

        .protocol .details > td {
            padding-top: 30px;
        }

        .protocol .details table tr > th {
            border-bottom: 2px solid #ddd;
            font-weight: 600;
            padding-bottom: 5px;
            font-size: 14px;
        }

        .protocol .details table tr > td {
            padding: 0;
            font-size: 14px;
            vertical-align: top;
        }

        .protocol .details table tr > td p {
            margin: 0;
        }

        .protocol p {
            font-size: 14px;
        }

        .protocol .documents > td {
            padding-top: 50px;
        }

        .protocol .documents .title th {
            padding-bottom: 30px;
            font-size: 20px;
        }

        .protocol .documents .thead th {
            font-size: 14px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }

        .protocol .documents .tbody td {
            font-size: 14px;
            border-bottom: 1px solid #e1e1e1;
            padding: 5px;
        }
    </style>
</head>
<body>
    <table class="protocol" cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr class="header">
            <td align="left" class="logo" width="25%">
                {{ config('account')->name }}
            </td>

            <td align="center" class="name" width="50%">
                <b>Protocolo de Entrega</b><br>
                <b>Documento {{ $document->name }}</b>
            </td>

            <td align="right" width="25%">
                <img src="{{ $barcode }}" alt="">
            </td>
        </tr>

        <tr class="details">
            <td cellpadding="0" cellspacing="0" colspan="3" border="0">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <th>Remetente</th>
                        <th>Destinatário</th>
                    </tr>

                    <tr>
                        <td width="50%">{{ $document->user->name }}</td>
                        <td width="50%">
                            @foreach($document->sharedWith as $contact)
                                {{ $contact->name != $contact->email ? $contact->name . ' - ' : '' }}{{ $contact->email }}<br>
                            @endforeach
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="details">
            <td cellpadding="0" cellspacing="0" colspan="3" border="0">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <th>Documento</th>
                        <th>Empresa</th>
                    </tr>

                    <tr>
                        <td width="50%">{{ $document->name }}</td>
                        <td width="50%">{{ $document->company->name }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="documents">
            <td colspan="3">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr class="title">
                        <th colspan="3" align="center">
                            Histórico
                        </th>
                    </tr>

                    <tr class="thead">
                        <th width="33.33%">Ação</th>
                        <th width="33.33%">Pessoa</th>
                        <th align="right" width="33.33%">Data</th>
                    </tr>

                    @foreach ($document->history()->orderBy('created_at', 'asc')->get() as $history)
                        <tr class="tbody">
                            <td width="33.33%">
                                @if ($history->action == 2)
                                    Enviado
                                @elseif ($history->action == 3 || $history->action == 4)
                                    Aberto
                                @elseif ($history->action == 5)
                                    Vencido
                                @elseif ($history->action == 6)
                                    Reenviado
                                @elseif ($history->action == 7)
                                    E-mail Entregue
                                @elseif ($history->action == 8)
                                    E-mail Lido
                                @elseif ($history->action == 9)
                                    Falha no envio
                                @endif
                            </td>
                            <td width="33.33%">
                                {{ $history->user ? $history->user->name : 'Sistema' }}
                            </td>
                            <td align="right" width="33.33%">
                                {{ $history->created_at->format('d M Y - H:i') }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
@php
    $remetente = [
        'nome'     => 'Gamers Nest',
        'endereco' => 'Rua Exemplo, 100 - Centro',
        'cidade'   => 'Guarapuava - PR',
        'cep'      => '85010-000',
    ];

    $endereco     = $pedido->endereco ?? [];
    $cliente      = $pedido->cliente ?? [];
    $nome         = $cliente['name'] ?? ($pedido->user->name ?? 'Cliente');
    $telefone     = $cliente['cellphone'] ?? '';
    $cepFormatado = strlen($cep) === 8 ? substr($cep, 0, 5) . '-' . substr($cep, 5) : ($endereco['cep'] ?? '');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Etiqueta {{ $pedido->codigo }}</title>
    <style>
        @page { size: 100mm 150mm; margin: 0; }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            background: #e5e7eb;
        }

        .barra-acoes {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 16px;
        }

        .barra-acoes button {
            border: 0;
            border-radius: 10px;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
        }

        .btn-imprimir { background: #4f46e5; color: #fff; }
        .btn-fechar   { background: #fff; color: #374151; }

        .etiqueta {
            width: 100mm;
            height: 150mm;
            margin: 0 auto 24px;
            background: #fff;
            border: 2px solid #000;
            padding: 5mm;
            display: flex;
            flex-direction: column;
            gap: 3mm;
            overflow: hidden;
        }

        .topo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 2mm;
        }

        .loja {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .pedido-num {
            font-size: 10px;
            text-align: right;
        }

        .pedido-num b { display: block; font-size: 11px; }

        .barras { text-align: center; }
        .barras svg { max-width: 100%; height: 12mm; }
        .barras span { display: block; font-size: 9px; margin-top: 1mm; letter-spacing: 1px; }

        .bloco {
            border: 1.5px solid #000;
            padding: 3mm;
        }

        .bloco-titulo {
            display: inline-block;
            background: #000;
            color: #fff;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 1px;
            padding: 1mm 2mm;
            margin-bottom: 2mm;
        }

        .destinatario-nome {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 1.5mm;
        }

        .destinatario-linha { font-size: 12px; line-height: 1.4; }

        .cep-grande {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 2px;
            margin-top: 2mm;
        }

        .remetente { font-size: 10px; line-height: 1.4; }

        .conteudo { font-size: 9px; flex: 1; overflow: hidden; }
        .conteudo table { width: 100%; border-collapse: collapse; }
        .conteudo td { padding: 0.6mm 0; border-bottom: 1px dotted #999; }
        .conteudo td:last-child { text-align: right; white-space: nowrap; }

        @media print {
            body { background: #fff; }
            .barra-acoes { display: none; }
            .etiqueta { margin: 0; border: none; }
        }
    </style>
</head>
<body>
    <div class="barra-acoes">
        <button type="button" class="btn-imprimir" onclick="window.print()">Imprimir etiqueta</button>
        <button type="button" class="btn-fechar" onclick="window.close()">Fechar</button>
    </div>

    <div class="etiqueta">
        <div class="topo">
            <div class="loja">{{ $remetente['nome'] }}</div>
            <div class="pedido-num">
                Pedido
                <b>{{ $pedido->codigo }}</b>
                {{ $pedido->created_at->timezone('America/Sao_Paulo')->format('d/m/Y') }}
            </div>
        </div>

        <div class="barras">
            {!! $barrasPedido !!}
        </div>

        <div class="bloco">
            <span class="bloco-titulo">DESTINATÁRIO</span>
            <div class="destinatario-nome">{{ $nome }}</div>
            <div class="destinatario-linha">
                {{ $endereco['logradouro'] ?? '' }}, {{ $endereco['numero'] ?? '' }}<br>
                {{ $endereco['bairro'] ?? '' }}<br>
                {{ $endereco['cidade'] ?? '' }}
                @if($telefone)
                    <br>Tel: {{ $telefone }}
                @endif
            </div>
            <div class="cep-grande">CEP {{ $cepFormatado }}</div>
            @if($barrasCep)
                <div class="barras" style="text-align: left; margin-top: 1mm;">
                    {!! $barrasCep !!}
                </div>
            @endif
        </div>

        <div class="bloco remetente">
            <span class="bloco-titulo">REMETENTE</span><br>
            <b>{{ $remetente['nome'] }}</b><br>
            {{ $remetente['endereco'] }}<br>
            {{ $remetente['cidade'] }} · CEP {{ $remetente['cep'] }}
        </div>

        <div class="conteudo">
            <b>DECLARAÇÃO DE CONTEÚDO</b>
            <table>
                @foreach($pedido->itens as $item)
                    <tr>
                        <td>{{ $item->quantidade }}x {{ \Illuminate\Support\Str::limit($item->nome, 38) }}</td>
                        <td>R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><b>Total</b></td>
                    <td><b>R$ {{ number_format($pedido->total_reais, 2, ',', '.') }}</b></td>
                </tr>
            </table>
        </div>
    </div>

    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 300));
    </script>
</body>
</html>

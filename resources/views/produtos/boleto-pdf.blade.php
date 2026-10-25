<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Boleto {{ $pedido['id'] ?? '' }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1 { font-size: 16px; margin: 0; }
        .muted { color: #777; }
        .aviso { background: #fff7e6; border: 1px solid #f0c36d; padding: 6px 8px; margin-bottom: 12px; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; }
        .cab td { vertical-align: middle; }
        .boleto td { border: 1px solid #444; padding: 3px 5px; vertical-align: top; }
        .rotulo { display: block; font-size: 7px; color: #555; text-transform: uppercase; }
        .valor { font-size: 10px; font-weight: bold; }
        .banco { font-size: 18px; font-weight: bold; border-right: 2px solid #222; padding-right: 8px; }
        .linha { font-size: 13px; font-weight: bold; letter-spacing: 1px; text-align: right; }
        .corte { border-top: 1px dashed #999; margin: 18px 0 10px; text-align: right; font-size: 7px; color: #999; }
        .itens td, .itens th { border-bottom: 1px solid #ddd; padding: 4px; text-align: left; }
        .itens th { font-size: 8px; text-transform: uppercase; color: #666; }
        .direita { text-align: right; }
    </style>
</head>
<body>
@php
    $valor       = ($cobranca['amount'] ?? 0) / 100;
    $vencimento  = !empty($cobranca['expiresAt'])
        ? \Carbon\Carbon::parse($cobranca['expiresAt'])->timezone('America/Sao_Paulo')->format('d/m/Y')
        : now()->addDays(3)->format('d/m/Y');
    $emissao     = !empty($cobranca['createdAt'])
        ? \Carbon\Carbon::parse($cobranca['createdAt'])->timezone('America/Sao_Paulo')->format('d/m/Y')
        : now()->format('d/m/Y');
    $cliente     = $pedido['cliente'] ?? [];
    $endereco    = $pedido['endereco'] ?? [];
    $linha       = $cobranca['barCode'] ?? '—';
    $cpf         = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cliente['taxId'] ?? '');
@endphp
 
    @if($cobranca['devMode'] ?? false)
        <div class="aviso"><b>DOCUMENTO DE TESTE</b> — boleto gerado no modo sandbox do AbacatePay. Não tem valor para pagamento real.</div>
    @endif
 
    <table class="cab">
        <tr>
            <td><h1>Gamers Nest</h1><span class="muted">Recibo do pagador</span></td>
            <td class="direita"><span class="muted">Pedido</span><br><b>{{ $pedido['id'] ?? $cobranca['id'] }}</b></td>
        </tr>
    </table>
 
    <table class="itens" style="margin-top:10px;">
        <tr><th>Produto</th><th>Qtd</th><th class="direita">Unitário</th><th class="direita">Subtotal</th></tr>
        @foreach(($pedido['itens'] ?? []) as $item)
            <tr>
                <td>{{ $item['nome'] }}</td>
                <td>{{ $item['quantidade'] }}</td>
                <td class="direita">R$ {{ number_format($item['preço'], 2, ',', '.') }}</td>
                <td class="direita">R$ {{ number_format($item['preço'] * $item['quantidade'], 2, ',', '.') }}</td>
            </tr>
        @endforeach
        <tr><td colspan="3" class="direita"><b>Total</b></td><td class="direita"><b>R$ {{ number_format($valor, 2, ',', '.') }}</b></td></tr>
    </table>
 
    <div class="corte">✂ corte aqui</div>
 
    <table class="cab" style="border-bottom:2px solid #222; margin-bottom:0;">
        <tr>
            <td style="width:130px;"><span class="banco">AbacatePay</span></td>
            <td class="linha">{{ $linha }}</td>
        </tr>
    </table>
 
    <table class="boleto">
        <tr>
            <td colspan="4"><span class="rotulo">Local de pagamento</span><span class="valor">Pagável em qualquer banco ou app até o vencimento</span></td>
            <td style="width:150px;"><span class="rotulo">Vencimento</span><span class="valor">{{ $vencimento }}</span></td>
        </tr>
        <tr>
            <td colspan="4"><span class="rotulo">Beneficiário</span><span class="valor">Gamers Nest (via AbacatePay)</span></td>
            <td><span class="rotulo">Nosso número</span><span class="valor">{{ $cobranca['id'] }}</span></td>
        </tr>
        <tr>
            <td><span class="rotulo">Data do documento</span><span class="valor">{{ $emissao }}</span></td>
            <td><span class="rotulo">Nº do documento</span><span class="valor">{{ $pedido['id'] ?? '' }}</span></td>
            <td><span class="rotulo">Espécie</span><span class="valor">R$</span></td>
            <td><span class="rotulo">Aceite</span><span class="valor">N</span></td>
            <td><span class="rotulo">(=) Valor do documento</span><span class="valor">R$ {{ number_format($valor, 2, ',', '.') }}</span></td>
        </tr>
        <tr>
            <td colspan="4" rowspan="2" style="height:60px;">
                <span class="rotulo">Instruções</span>
                Não receber após o vencimento.<br>
                Referente à compra na loja Gamers Nest — pedido {{ $pedido['id'] ?? '' }}.
            </td>
            <td><span class="rotulo">(-) Desconto</span>&nbsp;</td>
        </tr>
        <tr>
            <td><span class="rotulo">(=) Valor cobrado</span><span class="valor">R$ {{ number_format($valor, 2, ',', '.') }}</span></td>
        </tr>
        <tr>
            <td colspan="5">
                <span class="rotulo">Pagador</span>
                <span class="valor">{{ $cliente['name'] ?? '' }}</span> — CPF {{ $cpf }}<br>
                @if($endereco)
                    {{ $endereco['logradouro'] ?? '' }}, {{ $endereco['numero'] ?? '' }} — {{ $endereco['bairro'] ?? '' }} — {{ $endereco['cidade'] ?? '' }} — CEP {{ $endereco['cep'] ?? '' }}
                @endif
            </td>
        </tr>
    </table>
 
    <table style="margin-top:10px;">
        <tr>
            <td style="vertical-align:top;">
                @if($barcodeImg)
                    <img src="{{ $barcodeImg }}" style="height:50px; width:400px;">
                @else
                    <span class="muted">Código de barras indisponível — use a linha digitável acima.</span>
                @endif
                <br><span class="rotulo" style="margin-top:4px;">Autenticação mecânica — Ficha de compensação</span>
            </td>
            @if(!empty($cobranca['brCodeBase64']))
                <td style="width:110px; text-align:center; vertical-align:top;">
                    <img src="{{ $cobranca['brCodeBase64'] }}" style="width:95px; height:95px;"><br>
                    <span class="rotulo">Ou pague com PIX</span>
                </td>
            @endif
        </tr>
    </table>
</body>
</html>
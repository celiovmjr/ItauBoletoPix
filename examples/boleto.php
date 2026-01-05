<?php
// Dados do boleto
$dadosBoleto = [
    'beneficiario' => [
        'nome' => 'ZUK PAY',
        'cnpj' => '53.060.531/0001-36',
        'endereco' => 'Avenida Eldes Scherrer Souza, 303',
        'bairro' => 'PQ R Laranjeiras',
        'cidade' => 'Serra',
        'uf' => 'ES',
        'cep' => '29165-680',
    ],
    'pagador' => [
        'nome' => 'Celio Vieira de Magalhaes Junior',
        'cpf' => '166.227.527-70',
        'endereco' => 'Av do Estado, 5533',
        'bairro' => 'Mooca',
        'cidade' => 'Sao Paulo',
        'uf' => 'SP',
        'cep' => '04135-010',
    ],
    'boleto' => [
        'nosso_numero' => '00000001',
        'numero_documento' => '00000001',
        'data_emissao' => '18/12/2025',
        'data_vencimento' => '17/01/2026',
        'data_limite' => '18/01/2026',
        'valor' => '100,00',
        'desconto' => '10,00',
        'desconto_ate' => '10/01/2026',
        'codigo_barras' => '34191132900000100001090000000165581994034000',
        'linha_digitavel' => '34191090080000016558919940340003113290000010000',
        'pix_copia_cola' => '00020101021226860014BR.GOV.BCB.PIX2564spi-h.itau.com.br/pix/qr/v2/e8772e8f-3e31-4766-831c-c3ce8f453b635204000053039865802BR5920CARVALHEIRA GERALDES6009SAO PAULO62070503***63048DAC',
        'carteira' => '109',
        'juros_dia' => '0,10',
        'multa' => '1,00%',
    ],
    'pix' => [
        'chave' => '+5527992025628',
        'qrcode_base64' => 'iVBORw0KGgoAAAANSUhEUgAAAPoAAAD6AQAAAACgl2eQAAAC1klEQVR4Xu2XS47rIBBFYSNm/7vopcBG4J1TRG0ng9YbtKsnrkSKDSfSTX0uTlk/x1f5XPmIB9jxADseYMf/Ab2UOusaax5ttll8DxcTgcF79CpZxzxKW9y5mAl014mGSpa9rZtJBUYnR4pckaij/QFAkhoMFfLGLCUDvFmcbrJx1OVnrKcB9uf4jM+u/tz/ZcCYCCRXJKwNqkbj7MgCemQo6sMlI1wonD2cCKjMSjk10bm4CFJTgd62ToYHmS1umZ6zaRMAlJEct6pJimzBqDENYFR6gQipxRQBL4cpD7Bcs+3jBA+jVi6N+MgCwjqax5py27Jox3uxbgc8xzxVD9dH2NewWJkAy9wyK3qXpwl1A7lW83bAVNVIThSset92rdIAvHPQr/TqcICGJgLIwZIH2J8+YGBj6NzJIg5VZgEIZNHkYOdMDmIt1KWa9wMjBqbooGhV3bR/r7/idoBeYZXnXkgu1BjCz19xP2B6Op3SXRsv97CNEgHSNLZ796jacpDGuPyK+4HpWdL0kCPUwiryLFYCYNtao31h93b6p+qoeQBNq4kTtCqdgmTH2W9kAUxtt2d1U8zE+SXon0SANaZVA5lyCNXRPO/zACpUHZ5ptnw5P1Tqu1gJAMOitBJOFpOMmzXxPMDjnP6IfXbsHr9yHd7bAded3kNh1IsHL2t3PdRuB+JER1ZRo1t8vNwsDRj7dG9LR53Uiz/F79XMAFhdkSJONdA9upfhvR9YkSsyBFg0khavs1gJwHRumtZVI01LT8NVzmomADZKMU0y3TFC9XEReT+gZSCKRFGrEikLX/sWmQAYeJcmXuNoVfH74X47oCL2lUW8PFVDTwRsET88WkmZeVOkGvMASoXQ4tD6XyhsXd3JgBliZlBGwlYk7yxWEsBC6z7eQHm4MDru5wG80ee04qNc6mGhOQ+IyrCL0ub0eCVswrKAn+IBdjzAjgfY8QvAP6fWH62SBojlAAAAAElFTkSuQmCC',
        'txid' => 'BL55810099403109000000000000001',
    ],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - <?php echo $dadosBoleto['beneficiario']['nome']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #00D4AA;
            --tertiary: #0088CC;
            --gradient-main: linear-gradient(135deg, #00D4AA 0%, #0088CC 50%, #1a237e 100%);
            --gradient-soft: linear-gradient(135deg, rgba(0,212,170,0.1) 0%, rgba(0,136,204,0.1) 100%);
            --text-dark: #1a1a1a;
            --text-light: #666;
            --border: #e0e0e0;
            --bg-light: #f8f9fa;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.08);
            --shadow-lg: 0 8px 32px rgba(0,0,0,0.12);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8eef5 100%);
            min-height: 100vh;
            padding: 20px;
            color: var(--text-dark);
        }
        
        .container-custom {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: var(--shadow-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideDown 0.6s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-header {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo-diamonds {
            display: flex;
            gap: 3px;
        }
        
        .diamond {
            width: 14px;
            height: 14px;
            transform: rotate(45deg);
            animation: float 3s ease-in-out infinite;
        }
        
        .diamond:nth-child(1) {
            background: #00D4AA;
            animation-delay: 0s;
        }
        
        .diamond:nth-child(2) {
            background: #00B8D4;
            animation-delay: 0.2s;
        }
        
        .diamond:nth-child(3) {
            background: #0088CC;
            animation-delay: 0.4s;
        }
        
        @keyframes float {
            0%, 100% { transform: rotate(45deg) translateY(0); }
            50% { transform: rotate(45deg) translateY(-5px); }
        }
        
        .logo-text {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -1px;
        }
        
        .logo-zuk {
            color: var(--primary);
        }
        
        .logo-pay {
            color: var(--secondary);
        }
        
        .status-badge {
            background: var(--gradient-main);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: var(--shadow-md);
        }
        
        /* Main Grid */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 25px;
            animation: fadeIn 0.8s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Card Styles */
        .card-custom {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }
        
        .card-custom:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-title i {
            color: var(--secondary);
        }
        
        /* Valor em Destaque */
        .valor-destaque {
            background: var(--gradient-main);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            color: white;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
            animation: scaleIn 0.6s ease-out;
        }
        
        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .valor-destaque::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .valor-label {
            font-size: 14px;
            font-weight: 500;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .valor-principal {
            font-size: 64px;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: -2px;
            line-height: 1;
            text-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .vencimento-info {
            margin-top: 20px;
            font-size: 16px;
            font-weight: 600;
            opacity: 0.95;
        }
        
        /* PIX Section */
        .pix-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--shadow-md);
            text-align: center;
            position: sticky;
            top: 20px;
            animation: slideLeft 0.8s ease-out;
        }
        
        @keyframes slideLeft {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .pix-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .qrcode-wrapper {
            background: var(--gradient-soft);
            padding: 25px;
            border-radius: 16px;
            margin: 20px 0;
            display: inline-block;
            border: 3px solid var(--secondary);
        }
        
        .qrcode-img {
            width: 240px;
            height: 240px;
            border-radius: 8px;
        }
        
        .pix-chave {
            background: var(--bg-light);
            padding: 15px;
            border-radius: 12px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            font-size: 14px;
            margin: 15px 0;
            color: var(--text-dark);
        }
        
        /* Copy Buttons */
        .copy-btn {
            background: var(--gradient-main);
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            margin-top: 10px;
            box-shadow: var(--shadow-sm);
        }
        
        .copy-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .copy-btn:active {
            transform: scale(0.98);
        }
        
        .copy-btn.copied {
            background: #4CAF50 !important;
        }
        
        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        
        .info-item {
            background: var(--bg-light);
            padding: 16px;
            border-radius: 12px;
            border-left: 4px solid var(--secondary);
            transition: all 0.3s ease;
        }
        
        .info-item:hover {
            background: var(--gradient-soft);
            border-left-color: var(--tertiary);
        }
        
        .info-label {
            font-size: 11px;
            color: var(--text-light);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        
        .info-value {
            font-size: 15px;
            color: var(--text-dark);
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
        }
        
        /* Código de Barras */
        .barcode-section {
            background: var(--bg-light);
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
        }
        
        .barcode-visual {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            border: 2px solid var(--border);
        }
        
        .barcode-visual svg {
            max-width: 100%;
            height: auto;
        }
        
        .linha-digitavel-display {
            background: white;
            padding: 16px;
            border-radius: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 16px;
            font-weight: 700;
            text-align: center;
            letter-spacing: 2px;
            color: var(--text-dark);
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 12px;
            border: 2px solid transparent;
        }
        
        .linha-digitavel-display:hover {
            border-color: var(--secondary);
            background: var(--gradient-soft);
        }
        
        /* Instruções */
        .instrucoes {
            background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
            border-left: 4px solid #ffc107;
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
        }
        
        .instrucoes-title {
            font-size: 16px;
            font-weight: 700;
            color: #856404;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .instrucoes-list {
            list-style: none;
            padding: 0;
        }
        
        .instrucoes-list li {
            color: #856404;
            padding: 6px 0;
            padding-left: 24px;
            position: relative;
            font-size: 14px;
        }
        
        .instrucoes-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #ffc107;
            font-weight: bold;
            font-size: 16px;
        }
        
        /* Dados das Partes */
        .parte-info {
            background: var(--gradient-soft);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .parte-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .parte-content {
            font-size: 14px;
            line-height: 1.8;
        }
        
        .parte-content strong {
            color: var(--text-dark);
            font-weight: 600;
        }
        
        /* Toast Notification */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            display: none;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            z-index: 1000;
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .toast-notification.show {
            display: flex;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
            
            .pix-section {
                position: relative;
                top: 0;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .logo-text {
                font-size: 28px;
            }
            
            .valor-principal {
                font-size: 48px;
            }
            
            .qrcode-img {
                width: 200px;
                height: 200px;
            }
            
            .linha-digitavel-display {
                font-size: 12px;
                letter-spacing: 1px;
            }
        }
    </style>
</head>
<body>
    <!-- Toast Notification -->
    <div class="toast-notification" id="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">Copiado!</span>
    </div>

    <div class="container-custom">
        <!-- Header -->
        <div class="header">
            <div class="logo-header">
                <div class="logo-diamonds">
                    <div class="diamond"></div>
                    <div class="diamond"></div>
                    <div class="diamond"></div>
                </div>
                <div class="logo-text">
                    <span class="logo-zuk">zuk</span><span class="logo-pay">pay</span>
                </div>
            </div>
            <div class="status-badge">
                <i class="fas fa-clock"></i>
                Aguardando Pagamento
            </div>
        </div>

        <!-- Valor em Destaque -->
        <div class="valor-destaque">
            <div class="valor-label">Valor a Pagar</div>
            <div class="valor-principal">R$ <?php echo $dadosBoleto['boleto']['valor']; ?></div>
            <div class="vencimento-info">
                <i class="far fa-calendar-alt"></i> Vencimento: <?php echo $dadosBoleto['boleto']['data_vencimento']; ?>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="main-grid">
            <!-- Coluna Esquerda -->
            <div>
                <!-- Informações do Boleto -->
                <div class="card-custom">
                    <div class="card-title">
                        <i class="fas fa-file-invoice"></i>
                        Dados do Boleto
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Documento</div>
                            <div class="info-value"><?php echo $dadosBoleto['boleto']['numero_documento']; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Data de Emissão</div>
                            <div class="info-value"><?php echo $dadosBoleto['boleto']['data_emissao']; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Desconto até</div>
                            <div class="info-value"><?php echo $dadosBoleto['boleto']['desconto_ate']; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Valor Desconto</div>
                            <div class="info-value">R$ <?php echo $dadosBoleto['boleto']['desconto']; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Juros ao Dia</div>
                            <div class="info-value">R$ <?php echo $dadosBoleto['boleto']['juros_dia']; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Multa</div>
                            <div class="info-value"><?php echo $dadosBoleto['boleto']['multa']; ?></div>
                        </div>
                    </div>
                </div>

                <!-- Código de Barras -->
                <div class="card-custom" style="margin-top: 25px;">
                    <div class="card-title">
                        <i class="fas fa-barcode"></i>
                        Código de Barras
                    </div>
                    <div class="barcode-section">
                        <div class="barcode-visual">
                            <svg id="barcode"></svg>
                        </div>
                        <div style="text-align: center; margin-top: 15px; font-size: 12px; color: var(--text-light);">
                            <i class="fas fa-info-circle"></i> Aponte o leitor de código de barras para escanear
                        </div>
                    </div>
                </div>

                <!-- Linha Digitável -->
                <div class="card-custom" style="margin-top: 25px;">
                    <div class="card-title">
                        <i class="fas fa-calculator"></i>
                        Linha Digitável
                    </div>
                    <div class="barcode-section">
                        <div class="linha-digitavel-display" onclick="copiarTexto('<?php echo $dadosBoleto['boleto']['linha_digitavel']; ?>', 'Linha digitável')" title="Clique para copiar">
                            <?php echo $dadosBoleto['boleto']['linha_digitavel']; ?>
                        </div>
                        <button class="copy-btn" onclick="copiarTexto('<?php echo $dadosBoleto['boleto']['linha_digitavel']; ?>', 'Linha digitável', this)">
                            <i class="fas fa-copy"></i> Copiar Linha Digitável
                        </button>
                    </div>
                </div>

                <!-- Instruções -->
                <div class="instrucoes">
                    <div class="instrucoes-title">
                        <i class="fas fa-info-circle"></i>
                        Instruções de Pagamento
                    </div>
                    <ul class="instrucoes-list">
                        <li>Pagamento disponível via PIX</li>
                        <li>Desconto de R$ <?php echo $dadosBoleto['boleto']['desconto']; ?> até <?php echo $dadosBoleto['boleto']['desconto_ate']; ?></li>
                        <li>Após o vencimento multa de <?php echo $dadosBoleto['boleto']['multa']; ?></li>
                        <li>Juros de R$ <?php echo $dadosBoleto['boleto']['juros_dia']; ?> ao dia</li>
                    </ul>
                </div>

                <!-- Beneficiário -->
                <div class="parte-info">
                    <div class="parte-title">📍 Beneficiário</div>
                    <div class="parte-content">
                        <strong><?php echo $dadosBoleto['beneficiario']['nome']; ?></strong><br>
                        CNPJ: <?php echo $dadosBoleto['beneficiario']['cnpj']; ?><br>
                        <?php echo $dadosBoleto['beneficiario']['endereco']; ?>, <?php echo $dadosBoleto['beneficiario']['bairro']; ?><br>
                        <?php echo $dadosBoleto['beneficiario']['cidade']; ?>/<?php echo $dadosBoleto['beneficiario']['uf']; ?> - CEP: <?php echo $dadosBoleto['beneficiario']['cep']; ?>
                    </div>
                </div>

                <!-- Pagador -->
                <div class="parte-info">
                    <div class="parte-title">👤 Pagador</div>
                    <div class="parte-content">
                        <strong><?php echo $dadosBoleto['pagador']['nome']; ?></strong><br>
                        CPF: <?php echo $dadosBoleto['pagador']['cpf']; ?><br>
                        <?php echo $dadosBoleto['pagador']['endereco']; ?>, <?php echo $dadosBoleto['pagador']['bairro']; ?><br>
                        <?php echo $dadosBoleto['pagador']['cidade']; ?>/<?php echo $dadosBoleto['pagador']['uf']; ?> - CEP: <?php echo $dadosBoleto['pagador']['cep']; ?>
                    </div>
                </div>
            </div>

            <!-- Coluna Direita - PIX -->
            <div>
                <div class="pix-section">
                    <div class="pix-title">
                        <i class="fas fa-qrcode"></i>
                        Pague com PIX
                    </div>
                    
                    <p style="color: var(--text-light); margin-bottom: 20px; font-size: 14px;">
                        Escaneie o QR Code com o app do seu banco
                    </p>
                    
                    <div class="qrcode-wrapper">
                        <img src="data:image/png;base64,<?php echo $dadosBoleto['pix']['qrcode_base64']; ?>" class="qrcode-img" alt="QR Code PIX">
                    </div>
                    
                    <button class="copy-btn" onclick="copiarTexto('<?php echo $dadosBoleto['boleto']['pix_copia_cola']; ?>', 'PIX Copia e Cola', this)">
                        <i class="fas fa-copy"></i> Copiar PIX Copia e Cola
                    </button>
                    
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 2px dashed var(--border); font-size: 12px; color: var(--text-light);">
                        <strong>TXID:</strong> <?php echo $dadosBoleto['pix']['txid']; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gerar código de barras visual
        document.addEventListener('DOMContentLoaded', function() {
            try {
                JsBarcode("#barcode", "<?php echo $dadosBoleto['boleto']['codigo_barras']; ?>", {
                    format: "ITF",
                    width: 2,
                    height: 80,
                    displayValue: false,
                    margin: 10,
                    background: "#ffffff",
                    lineColor: "#000000"
                });
            } catch (error) {
                console.error('Erro ao gerar código de barras:', error);
            }
        });
        
        function copiarTexto(texto, tipo, element) {
            // Remove espaços se for linha digitável
            const textoLimpo = texto.replace(/\s/g, '');
            
            navigator.clipboard.writeText(textoLimpo).then(() => {
                mostrarToast(`${tipo} copiado com sucesso!`);
                
                // Animação no botão se o elemento foi passado
                if (element) {
                    element.classList.add('copied');
                    const originalText = element.innerHTML;
                    element.innerHTML = '<i class="fas fa-check"></i> Copiado!';
                    
                    setTimeout(() => {
                        element.classList.remove('copied');
                        element.innerHTML = originalText;
                    }, 2000);
                }
            }).catch(err => {
                console.error('Erro ao copiar:', err);
                mostrarToast('Erro ao copiar. Tente novamente.', 'error');
            });
        }
        
        function mostrarToast(mensagem, tipo = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            
            toastMessage.textContent = mensagem;
            toast.style.background = tipo === 'error' ? '#f44336' : '#4CAF50';
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
        
        // Adicionar efeito de hover nos elementos clicáveis
        document.querySelectorAll('.linha-digitavel-display').forEach(el => {
            el.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.02)';
            });
            el.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>
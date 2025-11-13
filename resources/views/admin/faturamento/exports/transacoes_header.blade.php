<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 8px; color: #212121; margin: 0; padding: 0; }
        .w-100 { width: 100%; }
        .text-blue { color: #004AE6; }
        .text-dark { color: #0D132B; }
        .text-muted { color: #888; }
        header {
            width: 100%;
            height: 100px;
            border-bottom: 2px solid #004AE6;
            padding-top: 20px; 
            box-sizing: border-box; 
            padding-left: 40px;
            padding-right: 40px;
        }
    </style>
</head>
<body>
    <header>
        <table class="w-100">
            <tr>
                <td style="width: 250px; vertical-align: middle;">
                    <table style="border-collapse: collapse;">
                        <tr>
                            <td style="padding-right: 15px; vertical-align: middle;">
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('vendor/adminlte/dist/img/Rovema Bank/LOGO-VERTICAL.png'))) }}" alt="Rovema Bank" style="width: 80px;">
                            </td>
                            <td style="vertical-align: middle;">
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('vendor/adminlte/dist/img/Logo Eliq png/Eliq.png'))) }}" alt="Eliq" style="width: 60px;">
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="text-align: right; vertical-align: middle;" class="text-dark">
                    <h2 style="margin: 0;" class="text-blue">RELATÓRIO DE TRANSAÇÕES</h2>
                    <div style="font-size: 9px;" class="text-muted">Período: {{ $periodo }}</div>
                </td>
            </tr>
        </table>
    </header>
</body>
</html>
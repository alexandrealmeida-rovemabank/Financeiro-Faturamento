<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Erro') - Sistema</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Ícones --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0d6efd;
            --danger: #dc3545;
            --warning: #ffc107;
            --success: #198754;
            --text-dark: #212529;
            --text-light: #6c757d;
            --bg-light: #f8f9fa;
        }

        body {
            background: linear-gradient(135deg, var(--bg-light) 0%, #eef2f7 100%);
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .error-container {
            background: #fff;
            text-align: center;
            padding: 50px 40px;
            border-radius: 24px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            max-width: 520px;
            animation: fadeIn 0.4s ease-in-out;
        }

        .error-icon {
            font-size: 64px;
            color: var(--danger);
            margin-bottom: 15px;
            animation: bounce 1.2s infinite;
        }

        .error-code {
            font-size: 86px;
            font-weight: 800;
            margin: 0;
            color: var(--danger);
        }

        .error-message {
            font-size: 22px;
            font-weight: 600;
            color: var(--text-dark);
            margin: 12px 0;
        }

        .error-details {
            font-size: 15px;
            color: var(--text-light);
            margin-bottom: 30px;
            line-height: 1.5;
        }

        a.btn {
            display: inline-block;
            background: var(--primary);
            color: #fff;
            padding: 12px 28px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            letter-spacing: 0.3px;
            transition: all 0.25s ease;
        }

        a.btn:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        /* Modo escuro automático */
        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(135deg, #121212 0%, #1f1f1f 100%);
            }
            .error-container {
                background: #222;
                color: #f1f1f1;
                box-shadow: 0 8px 30px rgba(0,0,0,0.5);
            }
            .error-message { color: #fff; }
            .error-details { color: #bbb; }
        }
    </style>
</head>
<body>
<div class="error-container">
    @yield('content')
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { 
            font-family: Arial, Helvetica, sans-serif; 
            font-size: 8px; 
            margin: 0; 
            padding: 0;
        }
        
        /* --- NOVO ESTILO "CLEAN" --- */
        footer {
            width: 100%;
            height: auto; /* Altura automática */
            background-color: #FFFFFF; /* Fundo branco */
            color: #555; /* Cor do texto principal */
            font-size: 8px;
            line-height: 1.4;
            box-sizing: border-box;
            padding-top: 15px; /* Espaço acima do conteúdo */
            border-top: 1px solid #004AE6; /* Linha divisória fina */
        }
        
        .w-100 { width: 100%; }
        .text-bold { font-weight: bold; }
        
        .footer-column { 
            vertical-align: top; 
            padding: 0 10px; 
            color: #555; 
        }
        
        .footer-column h5 { 
            margin: 0 0 8px 0; 
            font-size: 9px; 
            text-transform: uppercase; 
            color: #0D132B; /* Azul escuro da marca */
        }
        
        .footer-column p, .footer-column a { 
            margin: 0; 
            color: #555; 
            text-decoration: none; 
            font-size: 8px; 
        }
        
        .footer-logo { 
            text-align: left; 
            width: 100px; 
            vertical-align: top; 
            padding-left: 0; 
        }
        
        .footer-logo img { 
            width: 80px; 
            display: block; 
            margin-bottom: 10px;
        }
        .footer-logo img.eliq-logo-footer { 
            width: 60px; 
        }
        
        .social-icons-wrapper { margin-top: 5px; }
        
        
        .social-icon-link { 
            display: inline-block; 
            margin-right: 4px; 
            vertical-align: middle; 
            width: 16px;  /* Definimos o tamanho do link */
            height: 16px; /* Definimos o tamanho do link */
            background-size: contain; /* Para a imagem caber */
            background-repeat: no-repeat;
            background-position: center;
        }
    </style>
    
    </head>
<body>
    <footer>
        <table class="w-100">
            <tr>
                <td class="footer-column footer-logo" style="width: 20%;">
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('vendor/adminlte/dist/img/Rovema Bank/LOGO-VERTICAL.png'))) }}" alt="Rovema Bank">
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('vendor/adminlte/dist/img/Logo Eliq png/Eliq.png'))) }}" alt="Eliq" class="eliq-logo-footer">
                </td>
                
                <td class="footer-column" style="width: 30%;">
                    <h5>Atendimento</h5>
                    <p>Capitais e Regiões Metrop.: <strong class="text-bold">4020-1724</strong></p>
                    <p>Demais Localidades: <strong class="text-bold">0800 025 8871</strong></p>
                    <p>Atendimento Via E-mail: contato@rovemabank.com.br</p>
                    <br>
                    
                </td>
                
                <td class="footer-column" style="width: 30%;">
                    <h5>Informações</h5>
                    <p>WhatsApp (SAC): <strong class="text-bold">(69) 9 9322-9855</strong> <small>(somente mensagens)</small></p>
                    <p>WhatsApp (Vendas): <strong class="text-bold">(69) 99351-6909</strong> <small>(somente mensagens)</small></p>
                </td>
                <td class="footer-column" style="width: 30%;"> 
                    <h5>Redes Sociais</h5>
                    <div class="social-icons-wrapper">
                        
                        <a href="https://www.instagram.com/rovemabank" class="social-icon-link" 
                           style="background-image: url(data:image/png;base64,{{ base64_encode(file_get_contents(public_path('vendor/adminlte/dist/img/icones/instagram.png'))) }});">
                           </a>
                        
                        <a href="https://www.facebook.com/rovemabank" class="social-icon-link"
                           style="background-image: url(data:image/png;base64,{{ base64_encode(file_get_contents(public_path('vendor/adminlte/dist/img/icones/facebook.png'))) }});">
                        </a>
                        
                        <a href="https://br.linkedin.com/company/rovemabankbr" class="social-icon-link"
                           style="background-image: url(data:image/png;base64,{{ base64_encode(file_get_contents(public_path('vendor/adminlte/dist/img/icones/linkedin.png'))) }});">
                        </a>
                        
                        <a href="https://www.youtube.com/@rovemabank" class="social-icon-link"
                           style="background-image: url(data:image/png;base64,{{ base64_encode(file_get_contents(public_path('vendor/adminlte/dist/img/icones/youtube.png'))) }});">
                        </a>
                        
                        <a href="https://www.tiktok.com/@rovemabank" class="social-icon-link"
                           style="background-image: url(data:image/png;base64,{{ base64_encode(file_get_contents(public_path('vendor/adminlte/dist/img/icones/tik-tok.png'))) }});">
                        </a>
                        </div>
                </td>
            </tr>
        </table>
    </footer>
</body>
</html>
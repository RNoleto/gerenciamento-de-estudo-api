<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro 404</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
        }
        h1 {
            font-size: 48px;
            color: #FF6F61;
        }
        p {
            font-size: 18px;
            color: #333;
        }
        a {
            color: #007BFF;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Erro 404</h1>
    <p>{{ $errorMessage }}</p>
    <p>A URL solicitada foi: <strong>{{ $requestedUrl }}</strong></p>
    <p><a href="{{ url('/') }}">Voltar para a página inicial</a></p>
</body>
</html>

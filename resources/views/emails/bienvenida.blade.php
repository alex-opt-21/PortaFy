<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
    .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; padding: 40px; }
    .logo { font-size: 28px; font-weight: bold; margin-bottom: 24px; }
    .logo .porta { color: #111; }
    .logo .fy    { color: #E53535; }
    h1 { font-size: 22px; color: #111; }
    p  { color: #555; line-height: 1.6; }
    .footer { margin-top: 40px; font-size: 12px; color: #aaa; border-top: 1px solid #eee; padding-top: 16px; }
  </style>
</head>
<body>
  <div class="container">
    <img src="{{ ('logo.png') }}" alt="Portafy" style="height: 48px; margin-bottom: 16px;">

    <div class="logo">
      <span class="porta">Porta</span><span class="Fy">fy</span>
    </div>

    <h1>¡Hola, {{ $usuario->nombre }}!</h1>
    <p>Te registraste exitosamente en <strong>Portafy</strong>. Ya puedes empezar a construir tu portafolio profesional.</p>
    <p>Completa tu perfil para que reclutadores y clientes puedan encontrarte.</p>

    <div class="footer">
      Si no creaste esta cuenta, puedes ignorar este correo.
    </div>

  </div>
</body>
</html>

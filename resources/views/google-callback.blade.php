<!DOCTYPE html>
<html>
<body>
<script>
  @if(isset($error))
    window.opener?.postMessage(
      { type: 'GOOGLE_AUTH_ERROR', error: '{{ $error }}' },
      '{{ config("app.frontend_url") }}'
    );
  @else
    window.opener?.postMessage(
      {
        type: 'GOOGLE_AUTH_SUCCESS',
        token: '{{ $token }}',
        user: @json($user)
      },
      '{{ config("app.frontend_url") }}'
    );
  @endif
  window.close();
</script>
</body>
</html>

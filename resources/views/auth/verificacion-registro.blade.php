<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Verificación de Registro</title>
</head>
<body>
    <div class="contenedor-verificacion">
        <form action="{{ route('verificacion.registro.verificar') }}" method="POST" class="formulario-verificacion">
            @csrf
            <h2>Verificación de Correo</h2>

            @if($errors->any())
                <div class="alerta-error">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <p>Hemos enviado un código de verificación a tu correo electrónico para completar el registro.</p>

            <div class="grupo-formulario">
                <label for="codigo">Código de Verificación</label>
                <input 
                    type="text" 
                    id="codigo" 
                    name="codigo" 
                    required 
                    maxlength="6"
                    class="codigo-input"
                    pattern="[0-9A-Za-z]{6}"
                    autocomplete="off"
                >
            </div>

            <button type="submit" class="boton-login">
                Verificar Correo
            </button>
        </form>
    </div>
    <script>
        window.onload = function() {
            history.pushState(null, null, document.URL);
            window.addEventListener('popstate', function () {
                history.pushState(null, null, document.URL);
            });
        }
    </script>
</body>
</html>
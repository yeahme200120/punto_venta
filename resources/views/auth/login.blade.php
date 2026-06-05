<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Punto de Venta</title>

    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-slate-100">

<div class="min-h-screen flex">

    <!-- LADO IZQUIERDO -->
    <div
        class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-600 via-blue-600 to-cyan-500 text-white p-12 flex-col justify-between">

        <div>
            <h1 class="text-4xl font-bold mb-4">
                Punto de Venta
            </h1>

            <p class="text-lg text-blue-100">
                Control total de ventas, inventario, clientes,
                sucursales y reportes desde una sola plataforma.
            </p>
        </div>

        <div class="space-y-6">

            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    ✓
                </div>

                <div>
                    <h3 class="font-semibold">
                        Ventas rápidas
                    </h3>

                    <p class="text-blue-100 text-sm">
                        Registra ventas en segundos.
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    ✓
                </div>

                <div>
                    <h3 class="font-semibold">
                        Inventario en tiempo real
                    </h3>

                    <p class="text-blue-100 text-sm">
                        Control exacto de existencias.
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    ✓
                </div>

                <div>
                    <h3 class="font-semibold">
                        Multi sucursal
                    </h3>

                    <p class="text-blue-100 text-sm">
                        Gestiona todas tus tiendas.
                    </p>
                </div>
            </div>

        </div>

        <div class="text-sm text-blue-200">
            © {{ date('Y') }} Punto de Venta Ivan Hernandez
        </div>

    </div>

    <!-- LOGIN -->
    <div
        class="w-full lg:w-1/2 flex items-center justify-center p-6">

        <div class="w-full max-w-md">

            <!-- Wizard -->
            <div class="mb-8">

                <div class="flex items-center justify-center gap-2">

                    <div
                        class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-semibold">
                        1
                    </div>

                    <div class="h-1 w-16 bg-indigo-600"></div>

                    <div
                        class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center">
                        2
                    </div>

                </div>

                <p class="text-center mt-3 text-gray-500 text-sm">
                    Acceso al sistema
                </p>

            </div>

            <div class="bg-white rounded-3xl shadow-xl p-8">

                <div class="text-center mb-8">

                    <h2 class="text-3xl font-bold text-gray-800">
                        Bienvenido
                    </h2>

                    <p class="text-gray-500 mt-2">
                        Inicia sesión para continuar
                    </p>

                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div class="mb-5">

                        <label
                            class="block text-sm font-medium text-gray-700 mb-2">
                            Correo electrónico
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">

                        @error('email')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <!-- Password -->
                    <div class="mb-5">

                        <label
                            class="block text-sm font-medium text-gray-700 mb-2">
                            Contraseña
                        </label>

                        <input
                            type="password"
                            name="password"
                            required
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">

                        @error('password')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <!-- Extras -->
                    <div
                        class="flex items-center justify-between mb-6">

                        <label
                            class="flex items-center gap-2 text-sm text-gray-600">

                            <input
                                type="checkbox"
                                name="remember"
                                class="rounded">

                            Recordarme

                        </label>

                        <a
                            href="#"
                            class="text-indigo-600 text-sm hover:underline">

                            ¿Olvidaste tu contraseña?

                        </a>

                    </div>

                    <button
                        type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-semibold transition">

                        Iniciar sesión

                    </button>

                </form>

            </div>

            <div class="text-center mt-6 text-sm text-gray-500">
                Versión 1.0.0
            </div>

        </div>

    </div>

</div>

</body>
</html>
<?php

session_start();
// Activar buffer de salida para permitir regenerar la sesión
// incluso si ya se ha enviado output en el flujo del script.
ob_start();

require_once 'db_config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Vehículos - Asociación Civil</title>
    <style>
    /* Reset y estilos base */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    /* Contenedores */
    .container {
        width: 100%;
        max-width: 420px;
        margin: 0 auto;
        padding: 16px;
    }

    /* Tarjeta de login */
    .login-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
        padding: 28px;
        width: 100%;
        border: 1px solid rgba(0,0,0,0.06);
    }

    /* Encabezados y texto */
    .login-title {
        text-align: center;
        color: #222;
        margin-bottom: 10px;
        font-size: 22px;
    }

    .login-subtitle {
        text-align: center;
        color: #555;
        margin-bottom: 18px;
        font-size: 14px;
    }

    /* Formularios */
    .form-group {
        margin-bottom: 14px;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        color: #333;
        font-size: 13px;
    }

    .form-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
    }

    .form-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102,126,234,0.12);
    }

    /* Filas y disposición */
    .form-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    /* Botones */
    .btn {
        padding: 10px 16px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.2s;
    }

    .btn-primary {
        background: #667eea;
        color: #fff;
    }

    .btn-secondary {
        background: #6b7280;
        color: #fff;
    }

    .btn-primary:hover {
        background: #5a67d8;
    }

    .btn-secondary:hover {
        background: #4b5563;
    }

    /* Utilidades */
    .text-small {
        font-size: 13px;
        color: #555;
    }

    .text-center {
        text-align: center;
    }

    .mt-4 {
        margin-top: 16px;
    }

    /* Mensajes */
    .error-message {
        color: #b91c1c;
        margin-top: 8px;
        font-size: 13px;
    }

    .success-message {
        color: #047857;
        margin-top: 8px;
        font-size: 13px;
    }

    /* Enlaces */
    .text-link {
        color: #4f46e5;
        text-decoration: none;
    }

    .text-link:hover {
        text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 480px) {
        .login-card {
            padding: 20px;
        }
        
        .form-row {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .btn {
            width: 100%;
            margin-top: 8px;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <h1 class="login-title">Iniciar sesión</h1>
            <p class="login-subtitle">Ingresa con tu correo y contraseña.</p>

            <!-- El formulario envía por POST a este mismo archivo. En producción separar la lógica en controladores. -->
            <form id="loginForm" method="post" novalidate>
                <div class="form-group">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input id="email" class="form-input" name="email" type="email" required placeholder="tu@ejemplo.com" />
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <input id="password" class="form-input" name="password" type="password" required minlength="6" placeholder="Contraseña" />
                </div>

                <div class="form-row">
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </div>

                <div id="message" class="error-message" aria-live="polite"></div>
            </form>

            <form id="registerForm" method="post" novalidate class="mt-4" style="display:none;">
                <h2 class="login-title">Registro</h2>
                <div class="form-group">
                    <label for="reg_email" class="form-label">Correo electrónico</label>
                    <input id="reg_email" class="form-input" name="reg_email" type="email" required placeholder="tu@ejemplo.com" />
                </div>
                <div class="form-group">
                    <label for="reg_password" class="form-label">Contraseña</label>
                    <input id="reg_password" class="form-input" name="reg_password" type="password" required minlength="6" placeholder="Contraseña" />
                </div>
                <div class="form-group">
                    <label for="rol" class="form-label">Rol del usuario</label>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN'): ?>
                        <select id="rol" name="rol" class="form-input" required>
                            <option value="ADMIN">Administrador</option>
                            <option value="SOCIO">Socio</option>
                            <option value="AFILIADO" selected>Afiliado</option>
                        </select>
                    <?php else: ?>
                        <select id="rol" name="rol" class="form-input" disabled>
                            <option value="AFILIADO" selected>Afiliado</option>
                        </select>
                        <input type="hidden" name="rol" value="AFILIADO">
                    <?php endif; ?>
                </div>
                <div class="form-row">
                    <button type="submit" class="btn btn-secondary">Crear cuenta</button>
                    <button id="cancelRegister" type="button" class="btn">Cancelar</button>
                </div>
                <div id="regMessage" class="error-message" aria-live="polite"></div>
            </form>

            <div class="mt-4 text-center">
                <p>¿No tienes cuenta? <a href="#" id="registerLink" class="text-link">Regístrate</a></p>
            </div>

        </div>

        <?php
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pdo = null;
            try {
                $pdo = getPDO();
            } catch (Exception $e) {
                echo '<p class="error-message">No se pudo conectar a la base de datos.</p>';
            }
            
            if (isset($_POST['reg_email']) || isset($_POST['reg_password'])) {
                
                $reg_email = isset($_POST['reg_email']) ? trim($_POST['reg_email']) : '';
                $reg_password = isset($_POST['reg_password']) ? $_POST['reg_password'] : '';
                $errors = [];

                if (!filter_var($reg_email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Correo no válido.';
                }
                if (strlen($reg_password) < 6) {
                    $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
                }

                if (empty($errors)) {
                    try {
                        if (!$pdo) {
                            throw new Exception('Conexión a base de datos no disponible.');
                        }
                        $stmt = $pdo->prepare('SELECT id FROM usuario WHERE email = ? LIMIT 1');
                        $stmt->execute([$reg_email]);
                        if ($stmt->fetch()) {
                            echo '<p class="error-message">El correo ya está registrado.</p>';
                        } else {
                            $rol = 'AFILIADO'; // Valor por defecto
                            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN' && 
                                isset($_POST['rol']) && in_array($_POST['rol'], ['ADMIN', 'SOCIO', 'AFILIADO'])) {
                                $rol = $_POST['rol'];
                            }

                            $password_hash = password_hash($reg_password, PASSWORD_DEFAULT);
                            $ins = $pdo->prepare('INSERT INTO usuario (email, password_hash, rol) VALUES (?, ?, ?)');
                            $ins->execute([$reg_email, $password_hash, $rol]);
                            echo '<p class="success-message">Registro exitoso. Ahora puedes iniciar sesión.</p>';
                        }
                    } catch (PDOException $e) {
                        echo '<p class="error-message">Error en el registro: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    } catch (Exception $e) {
                        echo '<p class="error-message">Error en el registro: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                } else {
                    foreach ($errors as $e) {
                        echo '<p class="error-message">' . htmlspecialchars($e) . '</p>';
                    }
                }
            } else {
                
                $email = isset($_POST['email']) ? trim($_POST['email']) : '';
                $password = isset($_POST['password']) ? $_POST['password'] : '';

                $errors = [];
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Correo no válido.';
                }
                if (strlen($password) < 6) {
                    $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
                }

                if (empty($errors)) {
                    try {
                        if (!$pdo) {
                            throw new Exception('Conexión a base de datos no disponible.');
                        }
                        $stmt = $pdo->prepare('SELECT id, email, password_hash, rol FROM usuario WHERE email = ? LIMIT 1');
                        $stmt->execute([$email]);
                        $user = $stmt->fetch();

                        if ($user && isset($user['password_hash']) && password_verify($password, $user['password_hash'])) {
                            session_regenerate_id(true);
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_email'] = $user['email'];
                            $_SESSION['user_role'] = $user['rol'];

                            // Redirigir al dashboard después del login exitoso
                            header("Location: dashboard.php");
                            exit();
                        } else {
                            echo '<p class="error">Credenciales incorrectas.</p>';
                        }
                    } catch (PDOException $e) {
                        echo '<p class="error-message">Error en la autenticación: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    } catch (Exception $e) {
                        echo '<p class="error-message">Error en la autenticación: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                } else {
                    foreach ($errors as $e) {
                        echo '<p class="error-message">' . htmlspecialchars($e) . '</p>';
                    }
                }
            }
        }
        ?>
    </div>

        <script>
        
        (function(){
            const form = document.getElementById('loginForm');
            const message = document.getElementById('message');
            const registerLink = document.getElementById('registerLink');
            const registerForm = document.getElementById('registerForm');
            const regMessage = document.getElementById('regMessage');
            const cancelRegister = document.getElementById('cancelRegister');

            function showRegister() {
                registerForm.style.display = '';
                form.style.display = 'none';
            }

            function showLogin() {
                registerForm.style.display = 'none';
                form.style.display = '';
                regMessage.textContent = '';
            }

            const params = new URLSearchParams(window.location.search);
            if (params.get('action') === 'register') {
                showRegister();
            }

            form.addEventListener('submit', function(e){
                message.textContent = '';
                const email = document.getElementById('email').value.trim();
                const password = document.getElementById('password').value;

                if (!email || !password) {
                    e.preventDefault();
                    message.textContent = 'Por favor completa todos los campos.';
                    return;
                }
                if (password.length < 6) {
                    e.preventDefault();
                    message.textContent = 'La contraseña debe tener al menos 6 caracteres.';
                    return;
                }
            });

            registerLink.addEventListener('click', function(e){
                e.preventDefault();
                showRegister();
            });

            cancelRegister.addEventListener('click', function(e){
                showLogin();
            });

            
            registerForm.addEventListener('submit', function(e){
                regMessage.textContent = '';
                const email = document.getElementById('reg_email').value.trim();
                const password = document.getElementById('reg_password').value;

                if (!email || !password) {
                    e.preventDefault();
                    regMessage.textContent = 'Por favor completa todos los campos.';
                    return;
                }
                if (password.length < 6) {
                    e.preventDefault();
                    regMessage.textContent = 'La contraseña debe tener al menos 6 caracteres.';
                    return;
                }
            });
        })();
    </script>
</body>
</html>
<?php
// Enviar el buffer y permitir que los headers se manden correctamente
ob_end_flush();
?>

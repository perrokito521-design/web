<div class="container">
    <div class="header">
        <h1>Control de Vehículos</h1>
        <div>
            <p>Usuario: <strong><?php echo htmlspecialchars($_SESSION['user_email']); ?></strong></p>
            <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
        </div>
    </div>

    <h2><?php echo $page_title ?? 'Panel de Control'; ?></h2>
    <p class="welcome-message">Bienvenido al panel de control. Utiliza los botones para navegar a las funcionalidades.</p>
    <div class="nav-buttons">
        <a href="dashboard.php" class="Btn BtnNav BtnNav--reports">📈 Dashboard</a>
        <a href="vehicles.php" class="Btn BtnNav BtnNav--vehicles">🚗 Gestión de Vehículos</a>
        <a href="conductores.php" class="Btn BtnNav BtnNav--conductors">👥 Gestión de Conductores</a>
        <!-- <a href="maintenance.php" class="Btn BtnNav BtnNav--maintenance">🔧 Gestión de Mantenimiento</a> -->
        <a href="solvencia.php" class="Btn BtnNav BtnNav--solvencia">💰 Solvencia Administrativa</a>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN'): ?>
        <a href="usuarios.php" class="Btn BtnNav BtnNav--users">👤 Gestión de Usuarios</a>
        <a href="reportes.php" class="Btn BtnNav BtnNav--reportes">📊 Reportes Exportables</a>
        <?php endif; ?>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            ✅ **Éxito:** <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-error">
            ❌ **Error:** <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
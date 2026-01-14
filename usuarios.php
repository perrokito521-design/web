<?php
require_once 'includes/page_setup.php';
require_once 'includes/user_logic.php';

// Verificar sesión activa
require_session();

// Solo los administradores pueden acceder a esta página
if ($_SESSION['user_role'] !== 'ADMIN') {
    $_SESSION['error_message'] = "Permiso denegado: Solo los administradores pueden acceder a la gestión de usuarios.";
    header('Location: dashboard.php');
    exit();
}

$page_title = 'Gestión de Usuarios';
require_once 'includes/page_header.php';
?>

<div class="Card Card--full">
    <h2>👥 Gestión de Usuarios</h2>
    
    <!-- Sección de Crear/Editar Usuario -->
    <div class="Card">
        <h3><?php echo $is_editing_user ? '✏️ Editar Usuario' : '➕ Crear Nuevo Usuario'; ?></h3>
        
        <?php if ($is_editing_user): ?>
            <p><strong>Editando:</strong> <?php echo htmlspecialchars($edit_user_data['email']); ?> (ID: <?php echo $edit_user_data['id']; ?>)</p>
        <?php endif; ?>
        
        <form class="Form" method="POST" action="usuarios.php">
            <input type="hidden" name="action" value="<?php echo $is_editing_user ? 'update_user' : 'add_user'; ?>">
            
            <?php if ($is_editing_user): ?>
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($edit_user_data['id']); ?>">
            <?php endif; ?>

            <div class="Field Grid Grid--2">
                <div class="Field">
                    <label for="email" class="Label">Email</label>
                    <input id="email" name="email" type="email" class="Input" required 
                           value="<?php echo htmlspecialchars($is_editing_user ? $edit_user_data['email'] : ''); ?>"
                           placeholder="usuario@ejemplo.com">
                </div>
                <div class="Field">
                    <label for="rol" class="Label">Rol</label>
                    <select id="rol" name="rol" class="Select" required>
                        <?php $current_rol = $is_editing_user ? $edit_user_data['rol'] : 'AFILIADO'; ?>
                        <option value="ADMIN" <?php echo ($current_rol == 'ADMIN') ? 'selected' : ''; ?>>Administrador</option>
                        <option value="SOCIO" <?php echo ($current_rol == 'SOCIO') ? 'selected' : ''; ?>>Socio</option>
                        <option value="AFILIADO" <?php echo ($current_rol == 'AFILIADO') ? 'selected' : ''; ?>>Afiliado</option>
                    </select>
                </div>
            </div>

            <div class="Field Grid Grid--2">
                <div class="Field">
                    <label for="password" class="Label">
                        Contraseña <?php echo $is_editing_user ? '(dejar en blanco para mantener actual)' : ''; ?>
                    </label>
                    <input id="password" name="password" type="password" class="Input" 
                           <?php echo $is_editing_user ? '' : 'required'; ?>
                           placeholder="<?php echo $is_editing_user ? 'Nueva contraseña (opcional)' : 'Mínimo 6 caracteres'; ?>">
                </div>
                <div class="Field">
                    <label for="confirm_password" class="Label">Confirmar Contraseña</label>
                    <input id="confirm_password" name="confirm_password" type="password" class="Input" 
                           placeholder="Repite la contraseña">
                </div>
            </div>

            <div class="Card Card--info" style="background: #f0f9ff; border-left: 4px solid #0ea5e9; margin: 15px 0;">
                <h4 style="margin: 0 0 10px 0; color: #0c4a6e;">📋 Requisitos de Contraseña:</h4>
                <ul style="margin: 0; padding-left: 20px; color: #475569;">
                    <li>Mínimo 6 caracteres</li>
                    <li>Al menos una letra mayúscula</li>
                    <li>Al menos una letra minúscula</li>
                    <li>Al menos un número</li>
                </ul>
            </div>

            <div class="Actions">
                <button type="submit" class="Btn">
                    <?php echo $is_editing_user ? 'Actualizar Usuario' : 'Crear Usuario'; ?>
                </button>
                <?php if ($is_editing_user): ?>
                    <a href="usuarios.php" class="Btn BtnSecondary">Cancelar Edición</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Lista de Usuarios -->
    <div class="Card">
        <h3>📋 Usuarios Registrados</h3>
        
        <?php if (empty($users_list)): ?>
            <p class="EmptyState">No hay usuarios registrados.</p>
        <?php else: ?>
            <div class="Table--responsive">
                <table class="Table">
                    <thead class="Table-header">
                        <tr>
                            <th class="Table-th">ID</th>
                            <th class="Table-th">Email</th>
                            <th class="Table-th">Rol</th>
                            <th class="Table-th">Fecha de Creación</th>
                            <th class="Table-th">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users_list as $user): ?>
                        <tr class="Table-tr">
                            <td class="Table-td"><?php echo htmlspecialchars($user['id']); ?></td>
                            <td class="Table-td">
                                <?php echo htmlspecialchars($user['email']); ?>
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                    <span class="EstadoBadge EstadoBadge--pagado" style="margin-left: 8px;">TÚ</span>
                                <?php endif; ?>
                            </td>
                            <td class="Table-td">
                                <span class="EstadoBadge EstadoBadge--<?php echo strtolower($user['rol']); ?>">
                                    <?php echo htmlspecialchars($user['rol']); ?>
                                </span>
                            </td>
                            <td class="Table-td"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                            <td class="Table-td">
                                <div class="Actions Actions--end">
                                    <a href="usuarios.php?action=edit_user&id=<?php echo $user['id']; ?>" class="Btn BtnSecondary Btn--small">Editar</a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="usuarios.php?action=delete_user&id=<?php echo $user['id']; ?>" class="Btn BtnDanger Btn--small" 
                                           onclick="return confirm('¿Está seguro de eliminar este usuario? Esta acción no se puede deshacer.');">Eliminar</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Estadísticas de Usuarios -->
    <div class="Card">
        <h3>📊 Estadísticas de Usuarios</h3>
        <div class="Grid Grid--4 Grid--reports">
            <div class="Card Card--stat Card--stat1">
                <p class="Card-statLabel">Total Usuarios</p>
                <h3 class="Card-statValue"><?php echo $user_stats['total_users']; ?></h3>
            </div>
            
            <div class="Card Card--stat Card--stat2">
                <p class="Card-statLabel Card-statLabel--success">Administradores</p>
                <h3 class="Card-statValue"><?php echo $user_stats['total_admins']; ?></h3>
            </div>

            <div class="Card Card--stat Card--stat3">
                <p class="Card-statLabel Card-statLabel--warning">Socios</p>
                <h3 class="Card-statValue"><?php echo $user_stats['total_socios']; ?></h3>
            </div>

            <div class="Card Card--stat Card--stat4">
                <p class="Card-statLabel Card-statLabel--socios">Afiliados</p>
                <h3 class="Card-statValue"><?php echo $user_stats['total_afiliados']; ?></h3>
            </div>
        </div>
        
        <div class="Card" style="margin-top: 15px;">
            <h4>📈 Usuarios Nuevos este Mes</h4>
            <div class="ProgressBar">
                <div class="ProgressBar-pagados" style="width: 100%">
                    <span><?php echo $user_stats['users_this_month']; ?> usuarios nuevos este mes</span>
                </div>
            </div>
        </div>
    </div>
</div>

</div> 
</body>
</html>

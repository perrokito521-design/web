<?php
require_once 'includes/page_setup.php';
require_once 'includes/conductor_logic.php';

// Verificar permisos para esta página
check_permission(['ADMIN', 'SOCIO', 'AFILIADO']);

$page_title = 'Gestion de Conductores';
require_once 'includes/page_header.php';
?>

<div class="Card Card--full">
    <h2>👥 Gestión de Conductores</h2>
    
    <div class="Grid Grid--2">
        
        <div class="Card">
            <h3><?php echo $is_editing_conductor ? '✏️ Editar Conductor ID: ' . htmlspecialchars($edit_conductor_data['id']) : '➕ Registrar Nuevo Conductor'; ?></h3>
            
            <form class="Form" method="POST" action="conductores.php">
                <input type="hidden" name="action" value="<?php echo $is_editing_conductor ? 'update_conductor' : 'add_conductor'; ?>">
                
                <?php if ($is_editing_conductor): ?>
                    <input type="hidden" name="conductor_id" value="<?php echo htmlspecialchars($edit_conductor_data['id']); ?>">
                <?php endif; ?>

                <div class="Field Grid Grid--2">
                    <div class="Field">
                        <label for="nombre" class="Label">Nombre</label>
                        <input id="nombre" name="nombre" type="text" class="Input" required placeholder="Ej: Juan"
                               value="<?php echo htmlspecialchars($is_editing_conductor ? $edit_conductor_data['nombre'] : ($_POST['nombre'] ?? '')); ?>">
                    </div>
                    <div class="Field">
                        <label for="apellido" class="Label">Apellido</label>
                        <input id="apellido" name="apellido" type="text" class="Input" required placeholder="Ej: Pérez"
                               value="<?php echo htmlspecialchars($is_editing_conductor ? $edit_conductor_data['apellido'] : ($_POST['apellido'] ?? '')); ?>">
                    </div>
                </div>

                <div class="Field">
                    <label for="cedula" class="Label">Cédula de Identidad</label>
                    <input id="cedula" name="cedula" type="text" class="Input" required placeholder="Ej: V-12345678"
                           value="<?php echo htmlspecialchars($is_editing_conductor ? $edit_conductor_data['cedula'] : ($_POST['cedula'] ?? '')); ?>">
                </div>

                <div class="Field">
                    <label for="licencia_tipo" class="Label">Clasificación de Licencia</label>
                    <select id="licencia_tipo" name="licencia_tipo" class="Select" required>
                        <?php 
                        $current_tipo = $is_editing_conductor ? $edit_conductor_data['licencia_tipo'] : ($_POST['licencia_tipo'] ?? '');
                        $tipos_licencia = ['A1', 'A2', 'A3', 'A4', 'A5', 'B', 'B1', 'B2', 'C', 'C1', 'C2', 'D', 'D1', 'E', 'F', 'G', 'H'];
                        ?>
                        <option value="">Seleccione un tipo</option>
                        <?php foreach ($tipos_licencia as $tipo): ?>
                            <option value="<?php echo $tipo; ?>" <?php echo ($current_tipo == $tipo) ? 'selected' : ''; ?>>
                                <?php echo $tipo; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="Field Grid Grid--2">
                    <div class="Field">
                        <label for="licencia_emision" class="Label">Fecha de Emisión</label>
                        <input id="licencia_emision" name="licencia_emision" type="date" class="Input" required max="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo htmlspecialchars($is_editing_conductor ? $edit_conductor_data['licencia_emision'] : ($_POST['licencia_emision'] ?? '')); ?>">
                    </div>
                    <div class="Field">
                        <label for="licencia_vencimiento" class="Label">Fecha de Vencimiento</label>
                        <input id="licencia_vencimiento" name="licencia_vencimiento" type="date" class="Input" required min="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo htmlspecialchars($is_editing_conductor ? $edit_conductor_data['licencia_vencimiento'] : ($_POST['licencia_vencimiento'] ?? '')); ?>">
                    </div>
                </div>

                <div class="Actions">
                    <button type="submit" class="Btn"><?php echo $is_editing_conductor ? 'Guardar Cambios' : 'Registrar Conductor'; ?></button>
                    <?php if ($is_editing_conductor): ?>
                        <a href="conductores.php" class="Btn BtnSecondary">Cancelar Edición</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="Card">
            <h3>📋 Conductores Registrados</h3>
            
            <?php if (empty($conductores_list)): ?>
                <p class="EmptyState">No hay conductores registrados.</p>
            <?php else: ?>
                
                <div class="List">
                    <?php foreach ($conductores_list as $c): ?>
                        <div class="ListItem ListItem--conductor">
                            <p class="ListItem-title">
                                <?php echo htmlspecialchars($c['nombre'] . ' ' . $c['apellido']); ?>
                                <span class="ListItem-cedula">(<?php echo htmlspecialchars($c['cedula']); ?>)</span>
                            </p>
                            <p class="ListItem-details">
                                Licencia: <?php echo htmlspecialchars($c['licencia_tipo']); ?> | 
                                Vence: <?php echo date('d/m/Y', strtotime($c['licencia_vencimiento'])); ?>
                                <?php 
                                $dias_vencimiento = (new DateTime($c['licencia_vencimiento']))->diff(new DateTime())->days;
                                if ($dias_vencimiento <= 30 && $dias_vencimiento >= 0): ?>
                                    <span class="AlertMessage">⚠️ Vence en <?php echo $dias_vencimiento; ?> días</span>
                                <?php elseif ($dias_vencimiento < 0): ?>
                                    <span class="AlertMessage">❌ Vencida hace <?php echo abs($dias_vencimiento); ?> días</span>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN'): ?>
                                | Usuario: <?php echo htmlspecialchars($c['usuario_email']); ?>
                                <?php endif; ?>
                            </p>
                            <div class="Actions Actions--end">
                                <a href="conductores.php?action=edit&id=<?php echo $c['id']; ?>" class="Btn BtnSecondary Btn--small">Editar</a>
                                <a href="conductores.php?action=delete&id=<?php echo $c['id']; ?>" class="Btn BtnDanger Btn--small" 
                                   onclick="return confirm('¿Está seguro de eliminar este conductor? No se puede eliminar si tiene vehículos asociados.');">Eliminar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            
            <?php endif; ?>
        </div>
    </div>
</div>

</div> 
</body>
</html>

<?php
require_once 'includes/page_setup.php';
require_once 'includes/vehicle_logic.php';

// Verificar permisos para esta página
check_permission(['ADMIN', 'SOCIO', 'AFILIADO']);

$page_title = 'Gestion de Vehiculos';
require_once 'includes/page_header.php';
?>

<div class="Card Card--full">
    <h2> Gestión de Vehículos</h2>
    
    <div class="Grid Grid--2">
        
        <div class="Card">
            <h3><?php echo $is_editing ? '✏️ Editar Vehículo ID: ' . htmlspecialchars($edit_vehicle_data['id']) : '➕ Registrar Nuevo Vehículo'; ?></h3>
            
            <form class="Form" method="POST" action="vehicles.php">
                <input type="hidden" name="action" value="<?php echo $is_editing ? 'update_vehicle' : 'add_vehicle'; ?>">
                
                <?php if ($is_editing): ?>
                    <input type="hidden" name="vehicle_id" value="<?php echo htmlspecialchars($edit_vehicle_data['id']); ?>">
                <?php endif; ?>

                <div class="Field">
                    <label for="placa" class="Label">Placa/Identificador</label>
                    <input id="placa" name="placa" type="text" class="Input" required maxlength="10" placeholder="Ej: AB123CD"
                           value="<?php echo htmlspecialchars($is_editing ? $edit_vehicle_data['placa'] : ($_POST['placa'] ?? '')); ?>">
                </div>

                <div class="Field Grid Grid--2">
                    <div class="Field">
                        <label for="marca" class="Label">Marca</label>
                        <input id="marca" name="marca" type="text" class="Input" required placeholder="Ej: Toyota"
                               value="<?php echo htmlspecialchars($is_editing ? $edit_vehicle_data['marca'] : ($_POST['marca'] ?? '')); ?>">
                    </div>
                    <div class="Field">
                        <label for="modelo" class="Label">Modelo</label>
                        <input id="modelo" name="modelo" type="text" class="Input" required placeholder="Ej: Corolla"
                               value="<?php echo htmlspecialchars($is_editing ? $edit_vehicle_data['modelo'] : ($_POST['modelo'] ?? '')); ?>">
                    </div>
                </div>

                <div class="Field Grid Grid--2">
                    <div class="Field">
                        <label for="anio" class="Label">Año</label>
                        <input id="anio" name="anio" type="number" class="Input" required min="1950" max="<?php echo date('Y') + 1; ?>" placeholder="Ej: 2020"
                               value="<?php echo htmlspecialchars($is_editing ? $edit_vehicle_data['anio'] : ($_POST['anio'] ?? '')); ?>">
                    </div>
                    <div class="Field">
                        <label for="color" class="Label">Color</label>
                        <input id="color" name="color" type="text" class="Input" required placeholder="Ej: Rojo, Azul, Negro"
                               value="<?php echo htmlspecialchars($is_editing ? $edit_vehicle_data['color'] : ($_POST['color'] ?? '')); ?>">
                    </div>
                </div>

                <div class="Field Grid Grid--2">
                    <div class="Field">
                        <label for="capacidad" class="Label">Capacidad (Pasajeros)</label>
                        <input id="capacidad" name="capacidad" type="number" class="Input" required min="1" placeholder="Ej: 5"
                               value="<?php echo htmlspecialchars($is_editing ? $edit_vehicle_data['capacidad'] : ($_POST['capacidad'] ?? '')); ?>">
                    </div>
                </div>
                
                <div class="Field">
                    <label for="estado" class="Label">Estado</label>
                    <select id="estado" name="estado" class="Select" required>
                        <?php $current_estado = $is_editing ? $edit_vehicle_data['estado'] : ($_POST['estado'] ?? 'Activo'); ?>
                        <option value="Activo" <?php echo ($current_estado == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                        <option value="Inactivo" <?php echo ($current_estado == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                        <option value="Mantenimiento" <?php echo ($current_estado == 'Mantenimiento') ? 'selected' : ''; ?>>Mantenimiento</option>
                    </select>
                </div>

                <div class="Actions">
                    <button type="submit" class="Btn"><?php echo $is_editing ? 'Guardar Cambios' : 'Registrar Vehículo'; ?></button>
                    <?php if ($is_editing): ?>
                        <a href="vehicles.php" class="Btn BtnSecondary">Cancelar Edición</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="Card">
            <h3>📋 Mis Vehículos Registrados</h3>
            
            <?php if (empty($user_vehicles_list)): ?>
                <p class="EmptyState">No has registrado ningún vehículo todavía.</p>
            <?php else: ?>
                
                <div class="List">
                    <?php foreach ($user_vehicles_list as $v): ?>
                        <div class="ListItem ListItem--vehicle ListItem--<?php echo strtolower($v['estado']); ?>">
                            <p class="ListItem-title">
                                <?php echo htmlspecialchars($v['placa'] . ' - ' . $v['marca'] . ' ' . $v['modelo']); ?>
                                <span class="ListItem-year">(<?php echo htmlspecialchars($v['anio']); ?>)</span>
                            </p>
                            <p class="ListItem-details">
                                Color: <?php echo htmlspecialchars($v['color']); ?> | 
                                Capacidad: <?php echo htmlspecialchars($v['capacidad']); ?> | 
                                Estado: <?php echo htmlspecialchars($v['estado']); ?>
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN'): ?>
                                | Propietario: <?php echo htmlspecialchars($v['conductor_nombre'] ?? 'N/A'); ?>
                                <?php endif; ?>
                            </p>
                            <div class="Actions Actions--end">
                                <a href="vehicles.php?action=edit&id=<?php echo $v['id']; ?>" class="Btn BtnSecondary Btn--small">Editar</a>
                                <a href="vehicles.php?action=delete&id=<?php echo $v['id']; ?>" class="Btn BtnDanger Btn--small" 
                                   onclick="return confirm('¿Está seguro de eliminar este vehículo? Esto también eliminará todos sus registros de mantenimiento asociados.');">Eliminar</a>
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
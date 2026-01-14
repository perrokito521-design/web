<?php
require_once 'includes/page_setup.php';
require_once 'includes/maint_logic.php';
require_once 'includes/page_header.php';
?>

<div class="Card Card--full">
    <h2>🔧 Gestión de Mantenimiento</h2>
    
    <div class="Grid Grid--2">
        
        <div class="Card">
            <h3><?php echo $is_editing_maint ? '✏️ Editar Registro ID: ' . htmlspecialchars($edit_maint_data['id']) : '➕ Registrar Mantenimiento'; ?></h3>
            
            <?php if (empty($user_vehicles)): ?>
                <p class="AlertMessage">**Atención:** Debes <a href="vehicles.php" class="AlertMessage-link">registrar un vehículo</a> antes de añadir un registro de mantenimiento.</p>
            <?php else: ?>

            <form class="Form" method="POST" action="maintenance.php">
                <input type="hidden" name="action" value="<?php echo $is_editing_maint ? 'update_maintenance' : 'add_maintenance'; ?>">
                
                <?php if ($is_editing_maint): ?>
                    <input type="hidden" name="mantenimiento_id" value="<?php echo htmlspecialchars($edit_maint_data['id']); ?>">
                <?php endif; ?>
                
                <div class="Field">
                    <label for="vehiculo_id" class="Label">Vehículo</label>
                    <select id="vehiculo_id" name="vehiculo_id" class="Select" required>
                        <option value="">Seleccione un vehículo</option>
                        <?php foreach ($user_vehicles as $v): ?>
                            <?php 
                            $current_vehiculo_id = $is_editing_maint ? $edit_maint_data['vehiculo_id'] : ($_POST['vehiculo_id'] ?? null);
                            $selected = ($current_vehiculo_id == $v['id']) ? 'selected' : '';
                            ?>
                            <option value="<?php echo htmlspecialchars($v['id']); ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($v['placa'] . ' - ' . $v['marca'] . ' ' . $v['modelo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="Field Grid Grid--2">
                    <div class="Field">
                        <label for="fecha_servicio" class="Label">Fecha de Servicio</label>
                        <input id="fecha_servicio" name="fecha_servicio" type="date" class="Input" required max="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo htmlspecialchars($is_editing_maint ? $edit_maint_data['fecha_servicio'] : ($_POST['fecha_servicio'] ?? '')); ?>">
                    </div>
                    <div class="Field">
                        <label for="kilometraje" class="Label">Kilometraje (km)</label>
                        <input id="kilometraje" name="kilometraje" type="number" class="Input" required min="0"
                               value="<?php echo htmlspecialchars($is_editing_maint ? $edit_maint_data['kilometraje'] : ($_POST['kilometraje'] ?? '')); ?>">
                    </div>
                </div>

                <div class="Field Grid Grid--2">
                    <div class="Field">
                        <label for="tipo_servicio" class="Label">Tipo de Servicio</label>
                        <input id="tipo_servicio" name="tipo_servicio" type="text" class="Input" required placeholder="Ej: Cambio de aceite, Reparación de frenos"
                               value="<?php echo htmlspecialchars($is_editing_maint ? $edit_maint_data['tipo_servicio'] : ($_POST['tipo_servicio'] ?? '')); ?>">
                    </div>
                    <div class="Field">
                        <label for="costo" class="Label">Costo Total ($)</label>
                        <input id="costo" name="costo" type="number" step="0.01" class="Input" required min="0"
                               value="<?php echo htmlspecialchars($is_editing_maint ? $edit_maint_data['costo'] : ($_POST['costo'] ?? '')); ?>">
                    </div>
                </div>
                
                <div class="Field">
                    <label for="descripcion" class="Label">Descripción del Trabajo</label>
                    <textarea id="descripcion" name="descripcion" class="Input" rows="3" placeholder="Detalles de las piezas o el trabajo realizado"><?php echo htmlspecialchars($is_editing_maint ? $edit_maint_data['descripcion'] : ($_POST['descripcion'] ?? '')); ?></textarea>
                </div>

                <div class="Actions">
                    <button type="submit" class="Btn"><?php echo $is_editing_maint ? 'Guardar Cambios' : 'Guardar Registro'; ?></button>
                    <?php if ($is_editing_maint): ?>
                        <a href="maintenance.php" class="Btn BtnSecondary">Cancelar Edición</a>
                    <?php endif; ?>
                </div>
            </form>
            <?php endif; ?>
        </div>
        
        <div class="Card">
            <h3>📋 Registros de Mantenimiento</h3>
            
            <?php if (empty($maintenance_records)): ?>
                <p class="EmptyState">No hay registros de mantenimiento para tus vehículos.</p>
            <?php else: ?>
                
                <div class="List">
                    <?php foreach ($maintenance_records as $record): ?>
                        <div class="ListItem ListItem--maint">
                            <p class="ListItem-title">
                                <?php echo htmlspecialchars($record['tipo_servicio']); ?>
                                <span class="ListItem-date">(<?php echo date('d/m/Y', strtotime($record['fecha_servicio'])); ?>)</span>
                            </p>
                            <p class="ListItem-details">
                                Vehículo: <?php echo htmlspecialchars($record['placa'] . ' - ' . $record['modelo']); ?> | 
                                Costo: $<?php echo number_format($record['costo'], 2); ?>
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN'): ?>
                                | Propietario: <?php echo htmlspecialchars($record['conductor_nombre'] ?? 'N/A'); ?>
                                <?php endif; ?>
                            </p>
                            <div class="Actions Actions--end">
                                <a href="maintenance.php?action=edit&id=<?php echo $record['id']; ?>" class="Btn BtnSecondary Btn--small">Editar</a>
                                <a href="maintenance.php?action=delete&id=<?php echo $record['id']; ?>" class="Btn BtnDanger Btn--small" 
                                   onclick="return confirm('¿Está seguro de eliminar este registro de mantenimiento?');">Eliminar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            
            <?php endif; ?>
        </div>
    </div>
</div>

</div> </body>
</html>
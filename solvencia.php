<?php
require_once 'includes/page_setup.php';
require_once 'includes/solvencia_logic.php';

// Verificar permisos para esta página
check_permission(['ADMIN', 'SOCIO', 'AFILIADO']);

$page_title = 'Solvencia Administrativa';
require_once 'includes/page_header.php';
?>

<div class="Card Card--full">
    <h2>💰 Solvencia Administrativa</h2>
    
    <!-- Sección de Solvencia Financiera -->
    <div class="Card">
        <h3>📊 Control Financiero (Mensual)</h3>
        
        <?php if ($is_editing_financiera): ?>
            <h4>✏️ Editar Registro ID: <?php echo htmlspecialchars($edit_financiera_data['id']); ?></h4>
        <?php endif; ?>
        
        <form class="Form" method="POST" action="solvencia.php">
            <input type="hidden" name="action" value="<?php echo $is_editing_financiera ? 'update_financiera' : 'add_financiera'; ?>">
            
            <?php if ($is_editing_financiera): ?>
                <input type="hidden" name="financiera_id" value="<?php echo htmlspecialchars($edit_financiera_data['id']); ?>">
            <?php endif; ?>

            <div class="Field Grid Grid--3">
                <div class="Field">
                    <label for="anio" class="Label">Año</label>
                    <input id="anio" name="anio" type="number" class="Input" required min="2020" max="<?php echo date('Y') + 1; ?>"
                           value="<?php echo htmlspecialchars($is_editing_financiera ? $edit_financiera_data['anio'] : date('Y')); ?>">
                </div>
                <div class="Field">
                    <label for="mes" class="Label">Mes</label>
                    <select id="mes" name="mes" class="Select" required>
                        <?php 
                        $current_mes = $is_editing_financiera ? $edit_financiera_data['mes'] : date('n');
                        $meses = [1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril', 5=>'Mayo', 6=>'Junio', 
                                 7=>'Julio', 8=>'Agosto', 9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre'];
                        ?>
                        <?php foreach ($meses as $num => $nombre): ?>
                            <option value="<?php echo $num; ?>" <?php echo ($current_mes == $num) ? 'selected' : ''; ?>>
                                <?php echo $nombre; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="Field">
                    <label for="estado" class="Label">Estado</label>
                    <select id="estado" name="estado" class="Select" required>
                        <?php $current_estado = $is_editing_financiera ? $edit_financiera_data['estado'] : 'PENDIENTE'; ?>
                        <option value="PENDIENTE" <?php echo ($current_estado == 'PENDIENTE') ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="PAGADO" <?php echo ($current_estado == 'PAGADO') ? 'selected' : ''; ?>>Pagado</option>
                        <option value="ATRASADO" <?php echo ($current_estado == 'ATRASADO') ? 'selected' : ''; ?>>Atrasado</option>
                    </select>
                </div>
            </div>

            <div class="Field Grid Grid--2">
                <div class="Field">
                    <label for="monto" class="Label">Monto ($)</label>
                    <input id="monto" name="monto" type="number" step="0.01" class="Input" required min="0"
                           value="<?php echo htmlspecialchars($is_editing_financiera ? $edit_financiera_data['monto'] : '0.00'); ?>">
                </div>
                <div class="Field">
                    <label for="fecha_pago" class="Label">Fecha de Pago</label>
                    <input id="fecha_pago" name="fecha_pago" type="date" class="Input"
                           value="<?php echo htmlspecialchars($is_editing_financiera ? $edit_financiera_data['fecha_pago'] : ''); ?>">
                </div>
            </div>

            <div class="Actions">
                <button type="submit" class="Btn"><?php echo $is_editing_financiera ? 'Guardar Cambios' : 'Agregar Registro'; ?></button>
                <?php if ($is_editing_financiera): ?>
                    <a href="solvencia.php" class="Btn BtnSecondary">Cancelar Edición</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Lista de Registros Financieros -->
    <div class="Card">
        <h3>📋 Registros Financieros</h3>
        
        <?php if (empty($financiera_records)): ?>
            <p class="EmptyState">No hay registros financieros.</p>
        <?php else: ?>
            <div class="Table--responsive">
                <table class="Table">
                    <thead class="Table-header">
                        <tr>
                            <th class="Table-th">Año/Mes</th>
                            <th class="Table-th">Estado</th>
                            <th class="Table-th">Monto</th>
                            <th class="Table-th">Fecha Pago</th>
                            <?php if ($is_admin): ?>
                            <th class="Table-th">Usuario</th>
                            <?php endif; ?>
                            <th class="Table-th">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($financiera_records as $record): ?>
                        <tr class="Table-tr">
                            <td class="Table-td">
                                <?php echo htmlspecialchars($record['anio'] . '/' . str_pad($record['mes'], 2, '0', STR_PAD_LEFT)); ?>
                            </td>
                            <td class="Table-td">
                                <span class="EstadoBadge EstadoBadge--<?php echo strtolower($record['estado']); ?>">
                                    <?php echo htmlspecialchars($record['estado']); ?>
                                </span>
                            </td>
                            <td class="Table-td">$<?php echo number_format($record['monto'], 2); ?></td>
                            <td class="Table-td">
                                <?php echo $record['fecha_pago'] ? date('d/m/Y', strtotime($record['fecha_pago'])) : '-'; ?>
                            </td>
                            <?php if ($is_admin): ?>
                            <td class="Table-td"><?php echo htmlspecialchars($record['usuario_email']); ?></td>
                            <?php endif; ?>
                            <td class="Table-td">
                                <div class="Actions Actions--end">
                                    <a href="solvencia.php?action=edit_financiera&id=<?php echo $record['id']; ?>" class="Btn BtnSecondary Btn--small">Editar</a>
                                    <a href="solvencia.php?action=delete_financiera&id=<?php echo $record['id']; ?>" class="Btn BtnDanger Btn--small" 
                                       onclick="return confirm('¿Está seguro de eliminar este registro?');">Eliminar</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sección de Solvencia Fiscal -->
    <div class="Card">
        <h3>📅 Control Fiscal (Semanal)</h3>
        
        <?php if ($is_editing_fiscal): ?>
            <h4>✏️ Editar Registro ID: <?php echo htmlspecialchars($edit_fiscal_data['id']); ?></h4>
        <?php endif; ?>
        
        <form class="Form" method="POST" action="solvencia.php">
            <input type="hidden" name="action" value="<?php echo $is_editing_fiscal ? 'update_fiscal' : 'add_fiscal'; ?>">
            
            <?php if ($is_editing_fiscal): ?>
                <input type="hidden" name="fiscal_id" value="<?php echo htmlspecialchars($edit_fiscal_data['id']); ?>">
            <?php endif; ?>

            <div class="Field Grid Grid--3">
                <div class="Field">
                    <label for="dia_semana" class="Label">Día de Semana</label>
                    <select id="dia_semana" name="dia_semana" class="Select" required>
                        <?php 
                        $current_dia = $is_editing_fiscal ? $edit_fiscal_data['dia_semana'] : 1;
                        $dias = [1=>'Lunes', 2=>'Martes', 3=>'Miércoles', 4=>'Jueves', 5=>'Viernes', 6=>'Sábado', 7=>'Domingo'];
                        ?>
                        <?php foreach ($dias as $num => $nombre): ?>
                            <option value="<?php echo $num; ?>" <?php echo ($current_dia == $num) ? 'selected' : ''; ?>>
                                <?php echo $nombre; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="Field">
                    <label for="semana" class="Label">Semana (YYYY-WW)</label>
                    <input id="semana" name="semana" type="text" class="Input" required placeholder="Ej: 2024-01"
                           pattern="\d{4}-\d{2}" title="Formato: YYYY-WW"
                           value="<?php echo htmlspecialchars($is_editing_fiscal ? $edit_fiscal_data['semana'] : date('Y-W') . str_pad(date('W'), 2, '0', STR_PAD_LEFT)); ?>">
                </div>
                <div class="Field">
                    <label for="estado_fiscal" class="Label">Estado</label>
                    <select id="estado_fiscal" name="estado" class="Select" required>
                        <?php $current_estado_fiscal = $is_editing_fiscal ? $edit_fiscal_data['estado'] : 'PENDIENTE'; ?>
                        <option value="PENDIENTE" <?php echo ($current_estado_fiscal == 'PENDIENTE') ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="CUMPLIDO" <?php echo ($current_estado_fiscal == 'CUMPLIDO') ? 'selected' : ''; ?>>Cumplido</option>
                        <option value="AUSENTE" <?php echo ($current_estado_fiscal == 'AUSENTE') ? 'selected' : ''; ?>>Ausente</option>
                    </select>
                </div>
            </div>

            <div class="Field">
                <label for="observaciones" class="Label">Observaciones</label>
                <textarea id="observaciones" name="observaciones" class="Input" rows="3" placeholder="Notas adicionales"><?php echo htmlspecialchars($is_editing_fiscal ? $edit_fiscal_data['observaciones'] : ''); ?></textarea>
            </div>

            <div class="Actions">
                <button type="submit" class="Btn"><?php echo $is_editing_fiscal ? 'Guardar Cambios' : 'Agregar Registro'; ?></button>
                <?php if ($is_editing_fiscal): ?>
                    <a href="solvencia.php" class="Btn BtnSecondary">Cancelar Edición</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Lista de Registros Fiscales -->
    <div class="Card">
        <h3>📋 Registros Fiscales</h3>
        
        <?php if (empty($fiscal_records)): ?>
            <p class="EmptyState">No hay registros fiscales.</p>
        <?php else: ?>
            <div class="Table--responsive">
                <table class="Table">
                    <thead class="Table-header">
                        <tr>
                            <th class="Table-th">Semana</th>
                            <th class="Table-th">Día</th>
                            <th class="Table-th">Estado</th>
                            <th class="Table-th">Observaciones</th>
                            <?php if ($is_admin): ?>
                            <th class="Table-th">Usuario</th>
                            <?php endif; ?>
                            <th class="Table-th">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fiscal_records as $record): ?>
                        <tr class="Table-tr">
                            <td class="Table-td"><?php echo htmlspecialchars($record['semana']); ?></td>
                            <td class="Table-td">
                                <?php 
                                $dias = [1=>'Lunes', 2=>'Martes', 3=>'Miércoles', 4=>'Jueves', 5=>'Viernes', 6=>'Sábado', 7=>'Domingo'];
                                echo $dias[$record['dia_semana']] ?? 'Desconocido';
                                ?>
                            </td>
                            <td class="Table-td">
                                <span class="EstadoBadge EstadoBadge--<?php echo strtolower($record['estado']); ?>">
                                    <?php echo htmlspecialchars($record['estado']); ?>
                                </span>
                            </td>
                            <td class="Table-td"><?php echo htmlspecialchars($record['observaciones'] ?: '-'); ?></td>
                            <?php if ($is_admin): ?>
                            <td class="Table-td"><?php echo htmlspecialchars($record['usuario_email']); ?></td>
                            <?php endif; ?>
                            <td class="Table-td">
                                <div class="Actions Actions--end">
                                    <a href="solvencia.php?action=edit_fiscal&id=<?php echo $record['id']; ?>" class="Btn BtnSecondary Btn--small">Editar</a>
                                    <a href="solvencia.php?action=delete_fiscal&id=<?php echo $record['id']; ?>" class="Btn BtnDanger Btn--small" 
                                       onclick="return confirm('¿Está seguro de eliminar este registro?');">Eliminar</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</div> 
</body>
</html>

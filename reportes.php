<?php
require_once 'includes/page_setup.php';
require_once 'includes/export_logic.php';

// Verificar sesión activa
require_session();

// Solo los administradores pueden acceder a esta página
if ($_SESSION['user_role'] !== 'ADMIN') {
    $_SESSION['error_message'] = "Permiso denegado: Solo los administradores pueden acceder a los reportes.";
    header('Location: dashboard.php');
    exit();
}

$page_title = 'Reportes Exportables';

// Procesar exportaciones
if (isset($_GET['export']) && isset($_GET['type'])) {
    $export_type = $_GET['export'];
    $report_type = $_GET['type'];
    
    // Recoger filtros
    $filters = [];
    if (!empty($_GET['estado'])) $filters['estado'] = $_GET['estado'];
    if (!empty($_GET['conductor_id'])) $filters['conductor_id'] = $_GET['conductor_id'];
    if (!empty($_GET['vehiculo_id'])) $filters['vehiculo_id'] = $_GET['vehiculo_id'];
    if (!empty($_GET['fecha_desde'])) $filters['fecha_desde'] = $_GET['fecha_desde'];
    if (!empty($_GET['fecha_hasta'])) $filters['fecha_hasta'] = $_GET['fecha_hasta'];
    if (!empty($_GET['anio'])) $filters['anio'] = $_GET['anio'];
    if (!empty($_GET['mes'])) $filters['mes'] = $_GET['mes'];
    if (!empty($_GET['semana'])) $filters['semana'] = $_GET['semana'];
    if (!empty($_GET['licencia_vence_mes'])) $filters['licencia_vence_mes'] = $_GET['licencia_vence_mes'];
    if (!empty($_GET['licencia_vence_anio'])) $filters['licencia_vence_anio'] = $_GET['licencia_vence_anio'];
    
    switch ($report_type) {
        case 'vehicles':
            $data = get_vehicles_report_data($pdo, $filters);
            $headers = ['ID', 'Placa', 'Marca', 'Modelo', 'Año', 'Color', 'Capacidad', 'Estado', 'Email Conductor', 'Fecha Creación'];
            $filename = 'reporte_vehiculos_' . date('Y-m-d');
            break;
            
        // case 'maintenance':
        //     $data = get_maintenance_report_data($pdo, $filters);
        //     $headers = ['ID', 'Descripción', 'Costo', 'Fecha', 'Tipo', 'Placa Vehículo', 'Marca', 'Modelo', 'Email Conductor', 'Fecha Creación'];
        //     $filename = 'reporte_mantenimiento_' . date('Y-m-d');
        //     break;
            
        case 'conductors':
            $data = get_conductors_report_data($pdo, $filters);
            $headers = ['ID', 'Nombre', 'Cédula', 'Tipo Licencia', 'Vencimiento Licencia', 'Estado', 'Email Usuario', 'Fecha Creación'];
            $filename = 'reporte_conductores_' . date('Y-m-d');
            break;
            
        case 'solvencia_financiera':
            $data = get_solvencia_report_data($pdo, array_merge($filters, ['tipo' => 'financiera']));
            $headers = ['ID', 'Año', 'Mes', 'Estado', 'Monto', 'Fecha Pago', 'Email Usuario', 'Fecha Creación'];
            $filename = 'reporte_solvencia_financiera_' . date('Y-m-d');
            break;
            
        case 'solvencia_fiscal':
            $data = get_solvencia_report_data($pdo, array_merge($filters, ['tipo' => 'fiscal']));
            $headers = ['ID', 'Día Semana', 'Semana', 'Estado', 'Observaciones', 'Email Usuario', 'Fecha Creación'];
            $filename = 'reporte_solvencia_fiscal_' . date('Y-m-d');
            break;
            
        default:
            $_SESSION['error_message'] = 'Tipo de reporte no válido.';
            header('Location: reportes.php');
            exit();
    }
    
    // Exportar según el formato solicitado
    switch ($export_type) {
        case 'csv':
            export_to_csv($data, $filename, $headers);
            break;
        case 'excel':
            export_to_excel($data, $filename, $headers);
            break;
        case 'pdf':
            export_to_pdf($data, $filename, 'Reporte - ' . ucfirst($report_type), $headers);
            break;
        default:
            $_SESSION['error_message'] = 'Formato de exportación no válido.';
            header('Location: reportes.php');
            exit();
    }
}

$page_title = 'Reportes Exportables';
require_once 'includes/page_header.php';

// Obtener datos para mostrar en la interfaz
$vehicles_data = get_vehicles_report_data($pdo);
//$maintenance_data = get_maintenance_report_data($pdo);
$conductors_data = get_conductors_report_data($pdo);
$solvencia_financiera_data = get_solvencia_report_data($pdo, ['tipo' => 'financiera']);
$solvencia_fiscal_data = get_solvencia_report_data($pdo, ['tipo' => 'fiscal']);
//$cost_summary = get_cost_summary($pdo);

// Asegurar que los datos sean arrays para evitar errores
$vehicles_data = is_array($vehicles_data) ? $vehicles_data : [];
//$maintenance_data = is_array($maintenance_data) ? $maintenance_data : [];
$conductors_data = is_array($conductors_data) ? $conductors_data : [];
$solvencia_financiera_data = is_array($solvencia_financiera_data) ? $solvencia_financiera_data : [];
$solvencia_fiscal_data = is_array($solvencia_fiscal_data) ? $solvencia_fiscal_data : [];

// Obtener listas para filtros
$vehicles_list = $pdo->query("SELECT id, placa, marca, modelo FROM vehiculo ORDER BY placa")->fetchAll();
$users_list = $pdo->query("SELECT id, email FROM usuario ORDER BY email")->fetchAll();
?>

<div class="Card Card--full">
    <h2>📊 Reportes Exportables</h2>
    
    <!-- Resumen General -->
    <div class="Card">
        <h3>📈 Resumen General</h3>
        <div class="Grid Grid--4 Grid--reports">
            <div class="Card Card--stat Card--stat1">
                <p class="Card-statLabel">Total Vehículos</p>
                <h3 class="Card-statValue"><?php echo count($vehicles_data); ?></h3>
            </div>
            
            <div class="Card Card--stat Card--stat2">
                <p class="Card-statLabel Card-statLabel--success">Total Conductores</p>
                <h3 class="Card-statValue"><?php echo count($conductors_data); ?></h3>
            </div>

            <div class="Card Card--stat Card--stat3">
                <p class="Card-statLabel Card-statLabel--warning">Vehículos Activos</p>
                <h3 class="Card-statValue"><?php 
                    $activos = array_filter($vehicles_data, function($v) { return $v['estado'] === 'ACTIVO'; });
                    echo count($activos); 
                ?></h3>
            </div>

            <div class="Card Card--stat Card--stat4">
                <p class="Card-statLabel Card-statLabel--socios">Total Usuarios</p>
                <h3 class="Card-statValue"><?php echo count($users_list); ?></h3>
            </div>
        </div>
    </div>

    <!-- Reporte de Vehículos -->
    <div class="Card">
        <h3>🚗 Reporte de Vehículos</h3>
        
        <form method="GET" class="Form">
            <input type="hidden" name="type" value="vehicles">
            
            <div class="Field Grid Grid--3">
                <div class="Field">
                    <label for="v_estado" class="Label">Estado</label>
                    <select id="v_estado" name="estado" class="Select">
                        <option value="">Todos</option>
                        <option value="ACTIVO">Activo</option>
                        <option value="MANTENIMIENTO">Mantenimiento</option>
                        <option value="INACTIVO">Inactivo</option>
                    </select>
                </div>
                <div class="Field">
                    <label for="v_conductor" class="Label">Conductor</label>
                    <select id="v_conductor" name="conductor_id" class="Select">
                        <option value="">Todos</option>
                        <?php foreach ($users_list as $user): ?>
                            <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['email']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="Field">
                    <label for="v_fecha_desde" class="Label">Fecha Desde</label>
                    <input id="v_fecha_desde" name="fecha_desde" type="date" class="Input">
                </div>
            </div>
            
            <div class="Field Grid Grid--3">
                <div class="Field">
                    <label for="v_fecha_hasta" class="Label">Fecha Hasta</label>
                    <input id="v_fecha_hasta" name="fecha_hasta" type="date" class="Input">
                </div>
                <div class="Field">
                    <label>&nbsp;</label>
                    <div class="ExportActions">
                        <button type="submit" name="export" value="csv" class="Btn BtnSecondary">📄 Exportar CSV</button>
                        <button type="submit" name="export" value="excel" class="Btn BtnSecondary">📊 Exportar Excel</button>
                        <button type="submit" name="export" value="pdf" class="Btn BtnSecondary">📋 Exportar PDF</button>
                    </div>
                </div>
            </div>
        </form>
        
        <!-- Vista previa de datos -->
        <div class="Table--responsive">
            <table class="Table">
                <thead class="Table-header">
                    <tr>
                        <th class="Table-th">Placa</th>
                        <th class="Table-th">Marca/Modelo</th>
                        <th class="Table-th">Año</th>
                        <th class="Table-th">Color</th>
                        <th class="Table-th">Estado</th>
                        <th class="Table-th">Conductor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $limit = 5; // Mostrar solo los primeros 5 como vista previa
                    $count = 0;
                    foreach ($vehicles_data as $vehicle): 
                    if ($count >= $limit) break;
                    $count++;
                    ?>
                    <tr class="Table-tr">
                        <td class="Table-td"><?php echo htmlspecialchars($vehicle['placa']); ?></td>
                        <td class="Table-td"><?php echo htmlspecialchars($vehicle['marca'] . ' ' . $vehicle['modelo']); ?></td>
                        <td class="Table-td"><?php echo htmlspecialchars($vehicle['anio']); ?></td>
                        <td class="Table-td"><?php echo htmlspecialchars($vehicle['color']); ?></td>
                        <td class="Table-td">
                            <span class="EstadoBadge EstadoBadge--<?php echo strtolower($vehicle['estado']); ?>">
                                <?php echo htmlspecialchars($vehicle['estado']); ?>
                            </span>
                        </td>
                        <td class="Table-td"><?php echo htmlspecialchars($vehicle['conductor_email'] ?? 'N/A'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (count($vehicles_data) > $limit): ?>
                    <tr class="Table-tr">
                        <td colspan="6" class="Table-td" style="text-align: center; font-style: italic;">
                            ... y <?php echo (count($vehicles_data) - $limit); ?> registros más
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Reporte de Mantenimiento -->
    <!-- <div class="Card">
        <h3>🔧 Reporte de Mantenimiento</h3>
        
        <form method="GET" class="Form">
            <input type="hidden" name="type" value="maintenance">
            
            <div class="Field Grid Grid--3">
                <div class="Field">
                    <label for="m_vehiculo" class="Label">Vehículo</label>
                    <select id="m_vehiculo" name="vehiculo_id" class="Select">
                        <option value="">Todos</option>
                        <?php //foreach ($vehicles_list as $vehicle): ?>
                            <option value="<?php //echo $vehicle['id']; ?>"><?php //echo htmlspecialchars($vehicle['placa'] . ' - ' . $vehicle['modelo']); ?></option>
                        <?php //endforeach; ?>
                    </select>
                </div>
                <div class="Field">
                    <label for="m_tipo" class="Label">Tipo</label>
                    <select id="m_tipo" name="tipo" class="Select">
                        <option value="">Todos</option>
                        <option value="PREVENTIVO">Preventivo</option>
                        <option value="CORRECTIVO">Correctivo</option>
                    </select>
                </div>
                <div class="Field">
                    <label for="m_fecha_desde" class="Label">Fecha Desde</label>
                    <input id="m_fecha_desde" name="fecha_desde" type="date" class="Input">
                </div>
            </div>
            
            <div class="Field Grid Grid--3">
                <div class="Field">
                    <label for="m_fecha_hasta" class="Label">Fecha Hasta</label>
                    <input id="m_fecha_hasta" name="fecha_hasta" type="date" class="Input">
                </div>
                <div class="Field">
                    <label>&nbsp;</label>
                    <div class="ExportActions">
                        <button type="submit" name="export" value="csv" class="Btn BtnSecondary">📄 Exportar CSV</button>
                        <button type="submit" name="export" value="excel" class="Btn BtnSecondary">📊 Exportar Excel</button>
                        <button type="submit" name="export" value="pdf" class="Btn BtnSecondary">📋 Exportar PDF</button>
                    </div>
                </div>
            </div>
        </form> -->
        
        <!-- Vista previa de datos 
        <div class="Table--responsive">
            <table class="Table">
                <thead class="Table-header">
                    <tr>
                        <th class="Table-th">Fecha</th>
                        <th class="Table-th">Placa</th>
                        <th class="Table-th">Descripción</th>
                        <th class="Table-th">Tipo</th>
                        <th class="Table-th">Costo</th>
                    </tr>
                </thead>
                <tbody> -->
                    <?php 
                    //$limit = 5;
                    //$count = 0;
                    //foreach ($maintenance_data as $maintenance): 
                    //if ($count >= $limit) break;
                    //$count++;
                    ?>
                    <!-- <tr class="Table-tr">
                        <td class="Table-td"><?php //echo date('d/m/Y', strtotime($maintenance['fecha_servicio'])); ?></td>
                        <td class="Table-td"><?php //echo htmlspecialchars($maintenance['placa']); ?></td>
                        <td class="Table-td"><?php //echo htmlspecialchars($maintenance['descripcion']); ?></td>
                        <td class="Table-td"><?php //echo htmlspecialchars($maintenance['tipo_servicio']); ?></td>
                        <td class="Table-td Table-td--bold">$<?php //echo number_format($maintenance['costo'], 2); ?></td>
                    </tr> -->
                    <?php //endforeach; ?>
                    <?php //if (count($maintenance_data) > $limit): ?>
                    <!-- <tr class="Table-tr">
                        <td colspan="5" class="Table-td" style="text-align: center; font-style: italic;">
                            ... y <?php echo (count($maintenance_data) - $limit); ?> registros más
                        </td>
                    </tr> -->
                    <?php //endif; ?>
                <!-- </tbody>
            </table>
        </div>
</div> -->

    <!-- Reporte de Conductores -->
    <div class="Card">
        <h3>👥 Reporte de Conductores</h3>
        
        <form method="GET" class="Form">
            <input type="hidden" name="type" value="conductors">
            
            <div class="Field Grid Grid--3">
                <div class="Field">
                    <label for="c_licencia_mes" class="Label">Mes Vencimiento Licencia</label>
                    <select id="c_licencia_mes" name="licencia_vence_mes" class="Select">
                        <option value="">Todos</option>
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>"><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="Field">
                    <label for="c_licencia_anio" class="Label">Año Vencimiento Licencia</label>
                    <select id="c_licencia_anio" name="licencia_vence_anio" class="Select">
                        <option value="">Todos</option>
                        <?php for($y = date('Y'); $y <= date('Y') + 5; $y++): ?>
                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="Field">
                    <label>&nbsp;</label>
                    <div class="ExportActions">
                        <button type="submit" name="export" value="csv" class="Btn BtnSecondary">📄 Exportar CSV</button>
                        <button type="submit" name="export" value="excel" class="Btn BtnSecondary">📊 Exportar Excel</button>
                        <button type="submit" name="export" value="pdf" class="Btn BtnSecondary">📋 Exportar PDF</button>
                    </div>
                </div>
            </div>
        </form>
        
        <!-- Vista previa de datos -->
        <div class="Table--responsive">
            <table class="Table">
                <thead class="Table-header">
                    <tr>
                        <th class="Table-th">Nombre</th>
                        <th class="Table-th">Cédula</th>
                        <th class="Table-th">Licencia</th>
                        <th class="Table-th">Vencimiento</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $limit = 5;
                    $count = 0;
                    if (!empty($conductors_data)):
                    foreach ($conductors_data as $conductor): 
                    if ($count >= $limit) break;
                    $count++;
                    ?>
                    <tr class="Table-tr">
                        <td class="Table-td"><?php echo htmlspecialchars($conductor['nombre'] ?? 'N/A'); ?></td>
                        <td class="Table-td"><?php echo htmlspecialchars($conductor['cedula'] ?? 'N/A'); ?></td>
                        <td class="Table-td"><?php echo htmlspecialchars($conductor['licencia_tipo'] ?? 'N/A'); ?></td>
                        <td class="Table-td"><?php echo $conductor['licencia_vencimiento'] ? date('d/m/Y', strtotime($conductor['licencia_vencimiento'])) : 'N/A'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (count($conductors_data) > $limit): ?>
                    <tr class="Table-tr">
                        <td colspan="4" class="Table-td" style="text-align: center; font-style: italic;">
                            ... y <?php echo (count($conductors_data) - $limit); ?> registros más
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php else: ?>
                    <tr class="Table-tr">
                        <td colspan="4" class="Table-td" style="text-align: center; font-style: italic;">
                            No hay datos de conductores disponibles
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Reporte de Solvencia Financiera -->
    <div class="Card">
        <h3>💰 Reporte de Solvencia Financiera</h3>
        
        <form method="GET" class="Form">
            <input type="hidden" name="type" value="solvencia_financiera">
            
            <div class="Field Grid Grid--3">
                <div class="Field">
                    <label for="sf_estado" class="Label">Estado</label>
                    <select id="sf_estado" name="estado" class="Select">
                        <option value="">Todos</option>
                        <option value="PENDIENTE">Pendiente</option>
                        <option value="PAGADO">Pagado</option>
                        <option value="ATRASADO">Atrasado</option>
                    </select>
                </div>
                <div class="Field">
                    <label for="sf_anio" class="Label">Año</label>
                    <select id="sf_anio" name="anio" class="Select">
                        <option value="">Todos</option>
                        <?php for($y = date('Y') - 2; $y <= date('Y') + 2; $y++): ?>
                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="Field">
                    <label for="sf_mes" class="Label">Mes</label>
                    <select id="sf_mes" name="mes" class="Select">
                        <option value="">Todos</option>
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>"><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <div class="Field Grid Grid--3">
                <div class="Field">
                    <label>&nbsp;</label>
                    <div class="ExportActions">
                        <button type="submit" name="export" value="csv" class="Btn BtnSecondary">📄 Exportar CSV</button>
                        <button type="submit" name="export" value="excel" class="Btn BtnSecondary">📊 Exportar Excel</button>
                        <button type="submit" name="export" value="pdf" class="Btn BtnSecondary">📋 Exportar PDF</button>
                    </div>
                </div>
            </div>
        </form>
        
        <!-- Vista previa de datos -->
        <div class="Table--responsive">
            <table class="Table">
                <thead class="Table-header">
                    <tr>
                        <th class="Table-th">Año</th>
                        <th class="Table-th">Mes</th>
                        <th class="Table-th">Estado</th>
                        <th class="Table-th">Monto</th>
                        <th class="Table-th">Fecha Pago</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $limit = 5;
                    $count = 0;
                    if (!empty($solvencia_financiera_data)):
                    foreach ($solvencia_financiera_data as $solvencia): 
                    if ($count >= $limit) break;
                    $count++;
                    ?>
                    <tr class="Table-tr">
                        <td class="Table-td"><?php echo htmlspecialchars($solvencia['anio'] ?? 'N/A'); ?></td>
                        <td class="Table-td"><?php echo $solvencia['mes'] ? date('F', mktime(0, 0, 0, $solvencia['mes'], 1)) : 'N/A'; ?></td>
                        <td class="Table-td">
                            <span class="EstadoBadge EstadoBadge--<?php echo strtolower($solvencia['estado'] ?? 'pendiente'); ?>">
                                <?php echo htmlspecialchars($solvencia['estado'] ?? 'PENDIENTE'); ?>
                            </span>
                        </td>
                        <td class="Table-td Table-td--bold">$<?php echo number_format($solvencia['monto'] ?? 0, 2); ?></td>
                        <td class="Table-td"><?php echo $solvencia['fecha_pago'] ? date('d/m/Y', strtotime($solvencia['fecha_pago'])) : 'N/A'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (count($solvencia_financiera_data) > $limit): ?>
                    <tr class="Table-tr">
                        <td colspan="5" class="Table-td" style="text-align: center; font-style: italic;">
                            ... y <?php echo (count($solvencia_financiera_data) - $limit); ?> registros más
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php else: ?>
                    <tr class="Table-tr">
                        <td colspan="5" class="Table-td" style="text-align: center; font-style: italic;">
                            No hay datos de solvencia financiera disponibles
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Reporte de Solvencia Fiscal -->
    <div class="Card">
        <h3>📋 Reporte de Solvencia Fiscal</h3>
        
        <form method="GET" class="Form">
            <input type="hidden" name="type" value="solvencia_fiscal">
            
            <div class="Field Grid Grid--3">
                <div class="Field">
                    <label for="sfs_estado" class="Label">Estado</label>
                    <select id="sfs_estado" name="estado" class="Select">
                        <option value="">Todos</option>
                        <option value="CUMPLIDO">Cumplido</option>
                        <option value="AUSENTE">Ausente</option>
                    </select>
                </div>
                <div class="Field">
                    <label for="sfs_anio" class="Label">Año</label>
                    <select id="sfs_anio" name="anio" class="Select">
                        <option value="">Todos</option>
                        <?php for($y = date('Y') - 2; $y <= date('Y') + 2; $y++): ?>
                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="Field">
                    <label for="sfs_semana" class="Label">Semana</label>
                    <select id="sfs_semana" name="semana" class="Select">
                        <option value="">Todas</option>
                        <?php for($w = 1; $w <= 52; $w++): ?>
                            <option value="<?php echo $w; ?>">Semana <?php echo $w; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <div class="Field Grid Grid--3">
                <div class="Field">
                    <label>&nbsp;</label>
                    <div class="ExportActions">
                        <button type="submit" name="export" value="csv" class="Btn BtnSecondary">📄 Exportar CSV</button>
                        <button type="submit" name="export" value="excel" class="Btn BtnSecondary">📊 Exportar Excel</button>
                        <button type="submit" name="export" value="pdf" class="Btn BtnSecondary">📋 Exportar PDF</button>
                    </div>
                </div>
            </div>
        </form>
        
        <!-- Vista previa de datos -->
        <div class="Table--responsive">
            <table class="Table">
                <thead class="Table-header">
                    <tr>
                        <th class="Table-th">Día Semana</th>
                        <th class="Table-th">Semana</th>
                        <th class="Table-th">Estado</th>
                        <th class="Table-th">Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $limit = 5;
                    $count = 0;
                    if (!empty($solvencia_fiscal_data)):
                    foreach ($solvencia_fiscal_data as $solvencia): 
                    if ($count >= $limit) break;
                    $count++;
                    ?>
                    <tr class="Table-tr">
                        <td class="Table-td"><?php echo htmlspecialchars($solvencia['dia_semana'] ?? 'N/A'); ?></td>
                        <td class="Table-td"><?php echo htmlspecialchars($solvencia['semana'] ?? 'N/A'); ?></td>
                        <td class="Table-td">
                            <span class="EstadoBadge EstadoBadge--<?php echo strtolower($solvencia['estado'] ?? 'ausente'); ?>">
                                <?php echo htmlspecialchars($solvencia['estado'] ?? 'AUSENTE'); ?>
                            </span>
                        </td>
                        <td class="Table-td"><?php echo htmlspecialchars($solvencia['observaciones'] ?? 'N/A'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (count($solvencia_fiscal_data) > $limit): ?>
                    <tr class="Table-tr">
                        <td colspan="4" class="Table-td" style="text-align: center; font-style: italic;">
                            ... y <?php echo (count($solvencia_fiscal_data) - $limit); ?> registros más
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php else: ?>
                    <tr class="Table-tr">
                        <td colspan="4" class="Table-td" style="text-align: center; font-style: italic;">
                            No hay datos de solvencia fiscal disponibles
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div> 
</body>
</html>

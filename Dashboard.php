<?php
require_once 'includes/page_setup.php';
require_once 'includes/page_header.php';
// --------------------------------------------------------------------------
// --- Lógica de Reportes y Estadísticas (Se mantiene en dashboard.php) ---
// --------------------------------------------------------------------------

$user_id = $_SESSION['user_id'];
$report_stats = [
    'total_vehicles' => 0,
    'total_conductors' => 0,
    'total_spent' => 0.00,
    'total_records' => 0,
    'maintenance_by_vehicle' => []
];

$conductor_stats = [
    'total_socios' => 0,
    'total_afiliados' => 0,
    'total_conductores' => 0
];

try {
    // 1. Calcular Total de Vehículos
    $sql_vehicles = '
        SELECT COUNT(*) as total_vehicles
        FROM vehiculo 
        WHERE conductor_id = ?';
    
    $stmt_vehicles = $pdo->prepare($sql_vehicles);
    $stmt_vehicles->execute([$user_id]);
    $vehicles_data = $stmt_vehicles->fetch();
    
    if ($vehicles_data) {
        $report_stats['total_vehicles'] = (int)($vehicles_data['total_vehicles'] ?? 0);
    }

    // 2. Calcular Total de Conductores
    $sql_conductors = '
        SELECT COUNT(*) as total_conductors
        FROM conductores 
        WHERE usuario_id = ?';
    
    $stmt_conductors = $pdo->prepare($sql_conductors);
    $stmt_conductors->execute([$user_id]);
    $conductors_data = $stmt_conductors->fetch();
    
    if ($conductors_data) {
        $report_stats['total_conductors'] = (int)($conductors_data['total_conductors'] ?? 0);
    }

    // 1. Calcular Gasto Total en Mantenimiento y Total de Registros
    $sql_summary = '
        SELECT 
            SUM(m.costo) as total_spent, 
            COUNT(m.id) as total_records
        FROM 
            mantenimiento m
        JOIN 
            vehiculo v ON m.vehiculo_id = v.id
        WHERE 
            v.conductor_id = ?';
    
    $stmt_summary = $pdo->prepare($sql_summary);
    $stmt_summary->execute([$user_id]);
    $summary_data = $stmt_summary->fetch();
    
    if ($summary_data) {
        $report_stats['total_spent'] = (float)($summary_data['total_spent'] ?? 0.00);
        $report_stats['total_records'] = (int)($summary_data['total_records'] ?? 0);
    }

    // 2. Calcular Gasto Total por Vehículo
    $sql_by_vehicle = '
        SELECT
            v.placa,
            v.marca,
            v.modelo,
            SUM(m.costo) as spent,
            COUNT(m.id) as count
        FROM
            mantenimiento m
        JOIN
            vehiculo v ON m.vehiculo_id = v.id
        WHERE
            v.conductor_id = ?
        GROUP BY
            v.id, v.placa, v.marca, v.modelo
        ORDER BY
            spent DESC';
            
    $stmt_by_vehicle = $pdo->prepare($sql_by_vehicle);
    $stmt_by_vehicle->execute([$user_id]);
    $report_stats['maintenance_by_vehicle'] = $stmt_by_vehicle->fetchAll();

    // 3. Calcular Estadísticas de Conductores (solo para ADMIN)
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN') {
        $sql_conductors = '
            SELECT 
                rol,
                COUNT(*) as count
            FROM 
                usuario
            WHERE 
                rol IN (\'SOCIO\', \'AFILIADO\')
            GROUP BY 
                rol';
        
        $stmt_conductors = $pdo->prepare($sql_conductors);
        $stmt_conductors->execute();
        $conductor_data = $stmt_conductors->fetchAll();
        
        foreach ($conductor_data as $row) {
            if ($row['rol'] === 'SOCIO') {
                $conductor_stats['total_socios'] = (int)$row['count'];
            } elseif ($row['rol'] === 'AFILIADO') {
                $conductor_stats['total_afiliados'] = (int)$row['count'];
            }
        }
        
        $conductor_stats['total_conductores'] = $conductor_stats['total_socios'] + $conductor_stats['total_afiliados'];

        // 4. Calcular Estadísticas de Solvencia (solo para ADMIN)
        require_once 'includes/solvencia_logic.php';
        // Las estadísticas ya se calculan en solvencia_logic.php
    }

} catch (PDOException $e) {
    // Si hay error en Reportes, se maneja aquí.
    $report_error = 'Error al generar los reportes: ' . htmlspecialchars($e->getMessage());
}

?>

<div id="reports-section" class="Card Card--full Card--reports">
    <h2>📈 Módulo de Reportes y Estadísticas</h2>
    
    <div class="Grid Grid--3 Grid--reports">
        <div class="Card Card--stat Card--stat1">
            <p class="Card-statLabel">Total de Vehículos</p>
            <h3 class="Card-statValue"><?php echo $report_stats['total_vehicles']; ?></h3>
        </div>
        
        <div class="Card Card--stat Card--stat2">
            <p class="Card-statLabel Card-statLabel--success">Total de Conductores</p>
            <h3 class="Card-statValue"><?php echo $report_stats['total_conductors']; ?></h3>
        </div>
        
        <div class="Card Card--stat Card--stat3">
            <p class="Card-statLabel Card-statLabel--warning">Estado General</p>
            <h3 class="Card-statValue">Activo</h3>
        </div>
    </div>
</div>

<!-- Sección de Clasificación de Conductores (solo para ADMIN) -->
<?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN'): ?>
<div id="conductors-section" class="Card Card--full Card--reports">
    <h2>👥 Clasificación de Conductores</h2>
    
    <?php if ($conductor_stats['total_conductores'] === 0): ?>
        <p class="EmptyState EmptyState--reports">No hay conductores registrados. Registra usuarios como Socios o Afiliados.</p>
    <?php else: ?>
    
        <div class="Grid Grid--3 Grid--reports">
            <div class="Card Card--stat Card--stat4">
                <p class="Card-statLabel Card-statLabel--socios">Total de Socios</p>
                <h3 class="Card-statValue"><?php echo $conductor_stats['total_socios']; ?></h3>
                <p class="Card-statPercent">
                    <?php 
                    $percent_socios = $conductor_stats['total_conductores'] > 0 ? 
                        round(($conductor_stats['total_socios'] / $conductor_stats['total_conductores']) * 100, 1) : 0;
                    echo $percent_socios . '% del total';
                    ?>
                </p>
            </div>
            
            <div class="Card Card--stat Card--stat5">
                <p class="Card-statLabel Card-statLabel--afiliados">Total de Afiliados</p>
                <h3 class="Card-statValue"><?php echo $conductor_stats['total_afiliados']; ?></h3>
                <p class="Card-statPercent">
                    <?php 
                    $percent_afiliados = $conductor_stats['total_conductores'] > 0 ? 
                        round(($conductor_stats['total_afiliados'] / $conductor_stats['total_conductores']) * 100, 1) : 0;
                    echo $percent_afiliados . '% del total';
                    ?>
                </p>
            </div>

            <div class="Card Card--stat Card--stat6">
                <p class="Card-statLabel Card-statLabel--total">Total de Conductores</p>
                <h3 class="Card-statValue"><?php echo $conductor_stats['total_conductores']; ?></h3>
                <p class="Card-statPercent">100% del total</p>
            </div>
        </div>

        <div class="ConductorSummary">
            <div class="ProgressBar">
                <div class="ProgressBar-socios" style="width: <?php echo $percent_socios ?? 0; ?>%">
                    <span>Socios: <?php echo $conductor_stats['total_socios']; ?></span>
                </div>
                <div class="ProgressBar-afiliados" style="width: <?php echo $percent_afiliados ?? 0; ?>%">
                    <span>Afiliados: <?php echo $conductor_stats['total_afiliados']; ?></span>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Sección de Solvencia Administrativa (solo para ADMIN) -->
<?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN'): ?>
<div id="solvencia-section" class="Card Card--full Card--reports">
    <h2>💰 Solvencia Administrativa</h2>
    
    <div class="Grid Grid--3 Grid--reports">
        <div class="Card Card--stat Card--stat7">
            <p class="Card-statLabel Card-statLabel--pagados">Pagos Realizados</p>
            <h3 class="Card-statValue"><?php echo $solvencia_stats['financiera_pagados']; ?></h3>
            <p class="Card-statPercent">Control financiero</p>
        </div>
        
        <div class="Card Card--stat Card--stat8">
            <p class="Card-statLabel Card-statLabel--cumplidos">Días Cumplidos</p>
            <h3 class="Card-statValue"><?php echo $solvencia_stats['fiscal_cumplidos']; ?></h3>
            <p class="Card-statPercent">Control fiscal</p>
        </div>

        <div class="Card Card--stat Card--stat9">
            <p class="Card-statLabel Card-statLabel--pendientes">Registros Pendientes</p>
            <h3 class="Card-statValue"><?php echo $solvencia_stats['financiera_pendientes'] + $solvencia_stats['fiscal_pendientes']; ?></h3>
            <p class="Card-statPercent">Total pendientes</p>
        </div>
    </div>

    <div class="Grid Grid--2 Grid--reports">
        <div class="Card">
            <h4>📊 Estado Financiero</h4>
            <div class="ProgressBar">
                <div class="ProgressBar-pagados" style="width: <?php 
                    $total_financiera = $solvencia_stats['financiera_pagados'] + $solvencia_stats['financiera_pendientes'] + $solvencia_stats['financiera_atrasados'];
                    $percent_pagados = $total_financiera > 0 ? round(($solvencia_stats['financiera_pagados'] / $total_financiera) * 100, 1) : 0;
                    echo $percent_pagados; ?>%">
                    <span>Pagados: <?php echo $solvencia_stats['financiera_pagados']; ?></span>
                </div>
                <div class="ProgressBar-pendientes" style="width: <?php 
                    $percent_pendientes = $total_financiera > 0 ? round(($solvencia_stats['financiera_pendientes'] / $total_financiera) * 100, 1) : 0;
                    echo $percent_pendientes; ?>%">
                    <span>Pendientes: <?php echo $solvencia_stats['financiera_pendientes']; ?></span>
                </div>
                <div class="ProgressBar-atrasados" style="width: <?php 
                    $percent_atrasados = $total_financiera > 0 ? round(($solvencia_stats['financiera_atrasados'] / $total_financiera) * 100, 1) : 0;
                    echo $percent_atrasados; ?>%">
                    <span>Atrasados: <?php echo $solvencia_stats['financiera_atrasados']; ?></span>
                </div>
            </div>
        </div>
        
        <div class="Card">
            <h4>📅 Estado Fiscal</h4>
            <div class="ProgressBar">
                <div class="ProgressBar-cumplidos" style="width: <?php 
                    $total_fiscal = $solvencia_stats['fiscal_cumplidos'] + $solvencia_stats['fiscal_pendientes'] + $solvencia_stats['fiscal_ausentes'];
                    $percent_cumplidos = $total_fiscal > 0 ? round(($solvencia_stats['fiscal_cumplidos'] / $total_fiscal) * 100, 1) : 0;
                    echo $percent_cumplidos; ?>%">
                    <span>Cumplidos: <?php echo $solvencia_stats['fiscal_cumplidos']; ?></span>
                </div>
                <div class="ProgressBar-pendientes" style="width: <?php 
                    $percent_pendientes_fiscal = $total_fiscal > 0 ? round(($solvencia_stats['fiscal_pendientes'] / $total_fiscal) * 100, 1) : 0;
                    echo $percent_pendientes_fiscal; ?>%">
                    <span>Pendientes: <?php echo $solvencia_stats['fiscal_pendientes']; ?></span>
                </div>
                <div class="ProgressBar-ausentes" style="width: <?php 
                    $percent_ausentes = $total_fiscal > 0 ? round(($solvencia_stats['fiscal_ausentes'] / $total_fiscal) * 100, 1) : 0;
                    echo $percent_ausentes; ?>%">
                    <span>Ausentes: <?php echo $solvencia_stats['fiscal_ausentes']; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

</div> 

</body>
</html>
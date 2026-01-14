<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Función para exportar a CSV
function export_to_csv($data, $filename, $headers = []) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // Escribir headers si existen
    if (!empty($headers)) {
        fputcsv($output, $headers);
    }
    
    // Escribir datos
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

// Función para exportar a Excel (formato simple HTML)
function export_to_excel($data, $filename, $headers = []) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    
    echo '<table border="1">';
    
    // Escribir headers
    if (!empty($headers)) {
        echo '<tr>';
        foreach ($headers as $header) {
            echo '<th><b>' . htmlspecialchars($header) . '</b></th>';
        }
        echo '</tr>';
    }
    
    // Escribir datos
    foreach ($data as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>' . htmlspecialchars($cell) . '</td>';
        }
        echo '</tr>';
    }
    
    echo '</table>';
    exit;
}

// Función para exportar a PDF (básico)
function export_to_pdf($data, $filename, $title, $headers = []) {
    // Incluir HTML2PDF si está disponible
    if (!class_exists('\Mpdf\Mpdf')) {
        // Fallback a CSV si no hay librería PDF
        export_to_csv($data, $filename, $headers);
        return;
    }
    
    try {
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L', // Orientación Horizontal (Landscape) para que quepan las tablas
            'margin_top' => 15,
        ]);

        $css = "
        body {font-family: Arial, sans-serif;}
        h1 {text-align: center; color: #333;}
        table {width: 100%; border-collapse: collapse; margin-top: 20px;}
        th {background-color: #4CAF50; color: white; padding: 10px; font-size: 12px;}
        td {border: 1px solid #ddd; padding: 8px; font-size: 11px; text-align: left;}
        tr:nth-child(even) { background-color: #f2f2f2; }
        ";
        
        $html = '<h1>' . htmlspecialchars($title) . '</h1>';
        $html .= '<table>';
        
        // Headers
        if (!empty($headers)) {
            $html .= '<thead><tr>';
            foreach ($headers as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr></thead>';
        }
        
        // Datos
        $html .= '<tbody>';
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $content = ($cell !== null) ? $cell : '';
                $html .= '<td>' . htmlspecialchars($content) . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        
        $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

        $mpdf->Output($filename . '.pdf', 'D');
        
    } catch (Exception $e) {
        // Fallback a CSV si hay error
        //export_to_csv($data, $filename, $headers);
        die("Error al generar PDF: " . $e->getMessage());
    }
    exit;
}

// Función para obtener datos de vehículos para reportes
function get_vehicles_report_data($pdo, $filters = []) {
    $sql = "SELECT v.id, v.placa, v.marca, v.modelo, v.anio, v.color, v.capacidad, v.estado, 
                     u.email as conductor_email, v.created_at
              FROM vehiculo v 
              LEFT JOIN usuario u ON v.conductor_id = u.id
              WHERE 1=1";
    
    $params = [];
    
    // Aplicar filtros
    if (!empty($filters['estado'])) {
        $sql .= " AND v.estado = ?";
        $params[] = $filters['estado'];
    }
    
    if (!empty($filters['conductor_id'])) {
        $sql .= " AND v.conductor_id = ?";
        $params[] = $filters['conductor_id'];
    }
    
    if (!empty($filters['fecha_desde'])) {
        $sql .= " AND v.created_at >= ?";
        $params[] = $filters['fecha_desde'];
    }
    
    if (!empty($filters['fecha_hasta'])) {
        $sql .= " AND v.created_at <= ?";
        $params[] = $filters['fecha_hasta'];
    }
    
    $sql .= " ORDER BY v.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Función para obtener datos de mantenimiento para reportes
// function get_maintenance_report_data($pdo, $filters = []) {
//     $sql = "SELECT m.id, m.descripcion, m.costo, m.fecha_servicio, m.tipo_servicio,
//                  v.placa, v.marca, v.modelo,
//                  u.email as conductor_email, m.created_at
//           FROM mantenimiento m 
//           JOIN vehiculo v ON m.vehiculo_id = v.id
//           LEFT JOIN usuario u ON v.conductor_id = u.id
//           WHERE 1=1";
    
//     $params = [];
    
//     // Aplicar filtros
//     if (!empty($filters['vehiculo_id'])) {
//         $sql .= " AND m.vehiculo_id = ?";
//         $params[] = $filters['vehiculo_id'];
//     }
    
//     if (!empty($filters['conductor_id'])) {
//         $sql .= " AND v.conductor_id = ?";
//         $params[] = $filters['conductor_id'];
//     }
    
//     if (!empty($filters['fecha_desde'])) {
//         $sql .= " AND m.fecha_servicio >= ?";
//         $params[] = $filters['fecha_desde'];
//     }
    
//     if (!empty($filters['fecha_hasta'])) {
//         $sql .= " AND m.fecha_servicio <= ?";
//         $params[] = $filters['fecha_hasta'];
//     }
    
//     if (!empty($filters['tipo'])) {
//         $sql .= " AND m.tipo_servicio = ?";
//         $params[] = $filters['tipo'];
//     }
    
//     $sql .= " ORDER BY m.fecha_servicio DESC";
    
//     $stmt = $pdo->prepare($sql);
//     $stmt->execute($params);
//     return $stmt->fetchAll();
// }

// Función para obtener datos de conductores para reportes
function get_conductors_report_data($pdo, $filters = []) {
    $sql = "SELECT c.id, c.nombre, c.apellido, c.cedula, c.licencia_tipo, 
                     c.licencia_vencimiento, u.email as usuario_email, c.created_at
              FROM conductores c 
              LEFT JOIN usuario u ON c.usuario_id = u.id
              WHERE 1=1";
    
    $params = [];
    
    // Aplicar filtros
    if (!empty($filters['licencia_vence_mes'])) {
        $sql .= " AND MONTH(c.licencia_vencimiento) = ?";
        $params[] = $filters['licencia_vence_mes'];
    }
    
    if (!empty($filters['licencia_vence_anio'])) {
        $sql .= " AND YEAR(c.licencia_vencimiento) = ?";
        $params[] = $filters['licencia_vence_anio'];
    }
    
    $sql .= " ORDER BY c.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Función para obtener datos de solvencia para reportes
function get_solvencia_report_data($pdo, $filters = []) {
    $tipo = $filters['tipo'] ?? 'financiera';
    
    if ($tipo === 'financiera') {
        $sql = "SELECT sf.id, sf.anio, sf.mes, sf.estado, sf.monto, sf.fecha_pago,
                         u.email as usuario_email, sf.created_at
                  FROM solvencia_financiera sf 
                  LEFT JOIN usuario u ON sf.usuario_id = u.id
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['estado'])) {
            $sql .= " AND sf.estado = ?";
            $params[] = $filters['estado'];
        }
        
        if (!empty($filters['anio'])) {
            $sql .= " AND sf.anio = ?";
            $params[] = $filters['anio'];
        }
        
        if (!empty($filters['mes'])) {
            $sql .= " AND sf.mes = ?";
            $params[] = $filters['mes'];
        }
        
        $sql .= " ORDER BY sf.anio DESC, sf.mes DESC";
        
    } else {
        $sql = "SELECT sf.id, sf.dia_semana, sf.semana, sf.estado, sf.observaciones,
                         u.email as usuario_email, sf.created_at
                  FROM solvencia_fiscal sf 
                  LEFT JOIN usuario u ON sf.usuario_id = u.id
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['estado'])) {
            $sql .= " AND sf.estado = ?";
            $params[] = $filters['estado'];
        }
        
        if (!empty($filters['semana'])) {
            $sql .= " AND sf.semana = ?";
            $params[] = $filters['semana'];
        }
        
        $sql .= " ORDER BY sf.semana DESC, sf.dia_semana ASC";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Función para generar resumen de costos
// function get_cost_summary($pdo, $filters = []) {
//     $sql = "SELECT 
//                 SUM(m.costo) as total_costos,
//                 COUNT(m.id) as total_servicios,
//                 AVG(m.costo) as costo_promedio,
//                 MIN(m.costo) as costo_minimo,
//                 MAX(m.costo) as costo_maximo
//               FROM mantenimiento m 
//               JOIN vehiculo v ON m.vehiculo_id = v.id";
    
//     $params = [];
//     $where_clauses = [];
    
//     // Aplicar filtros
//     if (!empty($filters['vehiculo_id'])) {
//         $where_clauses[] = "m.vehiculo_id = ?";
//         $params[] = $filters['vehiculo_id'];
//     }
    
//     if (!empty($filters['conductor_id'])) {
//         $where_clauses[] = "v.conductor_id = ?";
//         $params[] = $filters['conductor_id'];
//     }
    
//     if (!empty($filters['fecha_desde'])) {
//         $where_clauses[] = "m.fecha_servicio >= ?";
//         $params[] = $filters['fecha_desde'];
//     }
    
//     if (!empty($filters['fecha_hasta'])) {
//         $where_clauses[] = "m.fecha_servicio <= ?";
//         $params[] = $filters['fecha_hasta'];
//     }
    
//     if (!empty($where_clauses)) {
//         $sql .= " WHERE " . implode(" AND ", $where_clauses);
//     }
    
//     $stmt = $pdo->prepare($sql);
//     $stmt->execute($params);
//     return $stmt->fetch();
// }
?>

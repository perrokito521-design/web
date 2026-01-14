<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($page_title ?? 'Panel de Control') . ' - Vehículos'; ?></title> 
    
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; padding: 20px; 
        }

        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 12px; 
            padding: 40px; 
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18); 
        }

        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 2px solid #f0f0f0; 
            padding-bottom: 20px; 
            margin-bottom: 25px; 
        }

        h1 { font-size: 1.5rem; color: #1f2937; }

        h2 { 
            font-size: 1.25rem; 
            color: #374151; 
            margin-bottom: 15px; 
        }

        h3 { 
            font-size: 1.1rem; 
            color: #4b5563; 
            margin-top: 0; 
            margin-bottom: 15px; 
        }

        .logout-btn { 
            background: #dc2626; 
            color: white; 
            padding: 8px 12px; 
            border-radius: 6px; 
            text-decoration: none; 
            font-weight: 600; 
            font-size: 0.9rem; 
            transition: background 0.2s;
            align-self: center;
        }

        .logout-btn:hover { background: #b91c1c; }

        .Card { 
            background: #f9fafb; 
            border-radius: 8px; 
            padding: 20px; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); 
            margin-bottom: 20px; 
        }
        .Grid { display: grid; gap: 20px; }
        .Grid--2 { grid-template-columns: repeat(2, 1fr); }
        .Grid--3 { grid-template-columns: repeat(3, 1fr); }
        .Form .Field { margin-bottom: 15px; }

        .Label { display: block; 
            margin-bottom: 5px; 
            font-weight: 600; 
            color: #4b5563; 
            font-size: 0.9rem; 
        }

        .Input, .Select { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #d1d5db; 
            border-radius: 6px; 
            font-size: 0.9rem; 
        }

        .Actions { 
            display: flex; 
            justify-content: flex-start; 
            gap: 10px; 
            margin-top: 20px; 
        }

        .Btn { 
            background: #4f46e5; 
            color: white; 
            padding: 10px 15px; 
            border: none; 
            border-radius: 6px; 
            text-decoration: none; 
            cursor: pointer; 
            font-weight: 600; 
            transition: background 0.2s; 
            font-size: 0.9rem; 
        }

        .Btn:hover { background: #4338ca; }

        .BtnSecondary { 
            background: #f3f4f6; 
            color: #4b5563; 
            padding: 10px 15px; 
            border: 1px solid #d1d5db; 
            border-radius: 6px; 
            text-decoration: none; 
            cursor: pointer; 
            font-weight: 600; 
            transition: background 0.2s; 
            font-size: 0.9rem; 
        }

        .BtnSecondary:hover { background: #e5e7eb; }

        .List .ListItem { 
            background: white; 
            padding: 15px; 
            border-radius: 6px; 
            border: 1px solid #e5e7eb; 
            margin-bottom: 10px; 
        }

        .Table { width: 100%; border-collapse: collapse; }
        .Table th, .Table td { 
            padding: 12px; 
            border-bottom: 1px solid #e5e7eb; 
            text-align: left; 
        }
        .Table th { 
            background-color: #f9fafb; 
            font-weight: 600; 
        }
        
        /* Maintenance Page Specific Styles */
    .Card--full {
        grid-column: 1 / -1;
        margin-top: 10px;
    }

    .AlertMessage {
        color: #ef4444;
        margin: 10px 0;
    }

    .AlertMessage-link {
        font-weight: 600;
        color: #dc2626;
        text-decoration: none;
    }

    .AlertMessage-link:hover {
        text-decoration: underline;
    }

    .EmptyState {
        text-align: center;
        padding: 20px;
        color: #9ca3af;
    }

    .ListItem--maint,
    .ListItem--vehicle,
    .ListItem--conductor {
        padding: 10px 15px;
        background: white;
        border-radius: 6px;
        margin-bottom: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .ListItem--maint {
        border-left: 5px solid #3b82f6;
    }
    
    .ListItem--activo {
        border-left: 5px solid #10b981;
    }
    
    .ListItem--mantenimiento {
        border-left: 5px solid #f97316;
    }
    
    .ListItem--inactivo {
        border-left: 5px solid #6b7280;
    }
    
    .ListItem--conductor {
        border-left: 5px solid #8b5cf6;
    }

    .ListItem-title {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 5px;
        color: #1f2937;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .ListItem-date,
    .ListItem-year,
    .ListItem-cedula {
        font-weight: 400;
        color: #6b7280;
        font-size: 0.8rem;
    }

    .ListItem-details {
        font-size: 0.85rem;
        color: #4b5563;
        margin: 5px 0;
    }

    .Actions--end {
        justify-content: flex-end;
        margin-top: 5px;
    }

    .Btn--small {
        padding: 4px 8px !important;
        font-size: 0.8rem !important;
    }

    .BtnDanger {
        background: #f87171 !important;
    }

    .BtnDanger:hover {
        background: #dc2626 !important;
    }

    /* Desktop breakpoint */
    @media (min-width: 768px) {
        .Grid--2 { grid-template-columns: repeat(2, 1fr); }
        .Grid--3 { grid-template-columns: repeat(3, 1fr); }
    }

    /* Navigation Styles */
    .welcome-message {
        margin-top: 10px;
        margin-bottom: 25px;
        color: #4b5563;
    }
    
    .nav-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        flex-wrap: wrap;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 20px;
    }
    
    .BtnNav {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
        padding: 8px 12px;
        font-size: 0.85rem;
        white-space: nowrap;
    }
    
    .BtnNav--reports {
        background: #eef2ff;
        color: #4f46e5;
        border: 1px solid #c7d2fe;
    }
    
    .BtnNav--reports:hover {
        background: #e0e7ff;
    }
    
    .BtnNav--vehicles {
        background: #10b981;
        color: white;
    }
    
    .BtnNav--vehicles:hover {
        background: #0d9c6e;
    }
    
    .BtnNav--conductors {
        background: #8b5cf6;
        color: white;
    }
    
    .BtnNav--conductors:hover {
        background: #7c3aed;
    }
    
    .BtnNav--maintenance {
        background: #3b82f6;
        color: white;
    }
    
    .BtnNav--maintenance:hover {
        background: #2563eb;
    }
    
    .BtnNav--solvencia {
        background: #059669;
        color: white;
    }
    
    .BtnNav--solvencia:hover {
        background: #047857;
    }
    
    .BtnNav--users {
        background: #7c3aed;
        color: white;
    }
    
    .BtnNav--users:hover {
        background: #6d28d9;
    }
    
    .BtnNav--reportes {
        background: #0891b2;
        color: white;
    }
    
    .BtnNav--reportes:hover {
        background: #0e7490;
    }
    
    .alert {
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 20px;
        border: 1px solid;
    }
    
    .alert-success {
        background-color: #d1fae5;
        color: #065f46;
        border-color: #a7f3d0;
    }
    
    .alert-error {
        background-color: #fee2e2;
        color: #991b1b;
        border-color: #fecaca;
    }
    
    @media (max-width: 767px) {
        .nav-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin: 0 auto 20px;
            padding: 0 10px 15px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }
        
        .BtnNav {
            flex: 1 1 120px;
            max-width: 200px;
            padding: 10px 5px;
            font-size: 0.75rem;
            text-align: center;
            white-space: normal;
            word-break: break-word;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 60px;
            box-sizing: border-box;
        }
        
        @media (max-width: 480px) {
            .nav-buttons {
                flex-wrap: nowrap;
                overflow-x: auto;
                justify-content: flex-start;
                padding-bottom: 20px;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
            }
            
            .nav-buttons::-webkit-scrollbar {
                display: none;
            }
            
            .BtnNav {
                flex: 0 0 auto;
                min-width: 120px;
                max-width: none;
                white-space: nowrap;
            }
            
            .BtnNav {
                flex-direction: row;
                justify-content: flex-start;
                padding: 8px 12px;
                min-height: auto;
                text-align: left;
                gap: 8px;
            }
        }
    }
    
    /* Dashboard/Reports Styles */
    .Card--reports {
        margin-top: 30px;
    }
    
    .EmptyState--reports {
        text-align: center;
        padding: 20px;
        color: #9ca3af;
    }
    
    .Grid--reports {
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .Card--stat {
        padding: 15px;
        border-left: 5px solid;
    }
    
    .Card--stat1 {
        background: #eef2ff;
        border-left-color: #4f46e5;
    }
    
    .Card--stat2 {
        background: #ecfdf5;
        border-left-color: #059669;
    }
    
    .Card--stat3 {
        background: #fff7ed;
        border-left-color: #f97316;
    }
    
    .Card--stat4 {
        background: #ecfdf5;
        border-left-color: #059669;
    }
    
    .Card--stat5 {
        background: #fef3c7;
        border-left-color: #d97706;
    }
    
    .Card--stat6 {
        background: #f3f4f6;
        border-left-color: #6b7280;
    }
    
    .Card-statLabel {
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 5px;
        color: #4f46e5;
    }
    
    .Card-statLabel--success {
        color: #059669;
    }
    
    .Card-statLabel--warning {
        color: #f97316;
    }
    
    .Card-statLabel--socios {
        color: #059669;
    }
    
    .Card-statLabel--afiliados {
        color: #d97706;
    }
    
    .Card-statLabel--total {
        color: #6b7280;
    }
    
    .Card-statPercent {
        font-size: 0.7rem;
        color: #6b7280;
        margin: 5px 0 0 0;
    }
    
    .Card-statValue {
        font-size: 1.5rem;
        color: #1e293b;
        margin: 0;
    }
    
    .Table--reports {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    
    .Table-header {
        background-color: #f3f4f6;
    }
    
    .Table-th {
        padding: 10px;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .Table-th--left {
        text-align: left;
    }
    
    .Table-th--center {
        text-align: center;
    }
    
    .Table-th--right {
        text-align: right;
    }
    
    .Table-tr {
        border-bottom: 1px solid #f3f4f6;
    }
    
    .Table-td {
        padding: 10px;
    }
    
    .Table-td--center {
        text-align: center;
    }
    
    .Table-td--right {
        text-align: right;
    }
    
    .Table-td--bold {
        font-weight: 600;
    }
    
    .ErrorMessage {
        color: #ef4444;
        margin-top: 20px;
    }
    
    /* Maintenance Page Responsive Adjustments */
    @media (max-width: 767px) {
        .Card--stat {
            padding: 12px;
        }
        
        .Card-statLabel {
            font-size: 0.75rem;
        }
        
        .Card-statValue {
            font-size: 1.25rem;
        }
        
        .Grid--reports {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .Table--reports {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }
        .ListItem--maint,
        .ListItem--vehicle {
            padding: 8px 12px;
        }
        
        .ListItem-title, 
        .ListItem-details {
            font-size: 0.85rem;
        }
        
        .Btn--small {
            padding: 3px 6px !important;
            font-size: 0.75rem !important;
        }
    }

    /* Bloque de estilos Responsive: 320px - 450px */
@media (max-width: 450px) {
    /* Contenedor principal y espaciado */
    .container {
        max-width: 100%; /* Ocupa todo el ancho */
        padding: 16px; /* Menos padding */
        border-radius: 10px; /* Opcional: bordes rectos en móvil */
        box-shadow: none; /* Opcional: sin sombra en móvil */
    }

    /* Encabezado: Título y Usuario/Logout */
    .header {
        flex-direction: column; /* Apila el título y el bloque de usuario */
        align-items: flex-start; /* Alinea los elementos a la izquierda */
        gap: 12px; /* Espacio entre el título y el bloque de usuario */
        padding-bottom: 15px;
    }
    
    .header h1 {
        font-size: 1.5rem;
    }

    /* Botón de Cerrar Sesión (dentro del header/div) */
    .logout-btn {
        width: 100%; /* Ocupa todo el ancho disponible */
        text-align: center;
        padding: 12px;
        margin-top: 5px; /* Pequeño margen superior para separarlo */
    }
    
    /* Navegación (Botones de Reportes/Vehículos/Mantenimiento) */
    /* El estilo en línea ya usa flex y flex-wrap, lo mantendremos, pero añadiremos que ocupen todo el ancho si es necesario */
    .header + h2 + p + div { /* Referencia a la div de botones de navegación */
        flex-direction: column; /* Apila los botones de navegación */
    }
    
    .header + h2 + p + div .Btn,
    .header + h2 + p + div .BtnSecondary {
        width: 100%; /* Cada botón de navegación ocupa todo el ancho */
    }

    /* Diseño de la Cuadrícula: 2 columnas en escritorio -> 1 columna en móvil */
    .Grid, .Grid--2 {
        grid-template-columns: 1fr; /* Una sola columna para todo el contenido */
        gap: 15px; /* Espacio entre el formulario y la lista, por ejemplo */
    }
    
    /* Tarjetas y Listas: Adaptación de Listado de Vehículos/Mantenimiento */
    .Card {
        padding: 20px; /* Menos padding dentro de la tarjeta */
    }
    
    /* Campo del formulario que usa Grid--2 */
    .Field.Grid--2 {
        grid-template-columns: 1fr; /* Los campos anidados también se apilan */
        gap: 15px;
    }

    /* Estilos de la tabla de Reportes (Dashboard) */
    .Table {
        font-size: 0.85rem;
    }
    
    .Table th, .Table td {
        padding: 8px;
    }

    /* Estilos de la lista de elementos (Vehículos y Mantenimiento) */
    .List .ListItem {
        /* Se adapta bien por defecto, pero podemos reducir el padding si es necesario */
        padding: 12px;
    }
    
    .List .ListItem .Actions {
        flex-direction: column; /* Apila los botones de Editar/Eliminar */
        align-items: flex-start;
        gap: 8px;
    }
    
    .List .ListItem .Actions .Btn, 
    .List .ListItem .Actions .BtnSecondary {
        width: 100%; /* Los botones de acción ocupan todo el ancho */
        text-align: center;
        padding: 8px;
        font-size: 0.9rem !important; /* !important si es necesario por estilos en línea */
    }

    .Btn{
        width: 100%;
    }
}

/* Conductor Classification Styles */
.ConductorSummary {
    margin-top: 20px;
}

.ProgressBar {
    height: 40px;
    background: #f3f4f6;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.ProgressBar-socios {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    transition: width 0.3s ease;
}

.ProgressBar-afiliados {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    transition: width 0.3s ease;
}

.ProgressBar-pagados {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    transition: width 0.3s ease;
}

.ProgressBar-pendientes {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    transition: width 0.3s ease;
}

.ProgressBar-atrasados {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    transition: width 0.3s ease;
}

.ProgressBar-cumplidos {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    transition: width 0.3s ease;
}

.ProgressBar-ausentes {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    transition: width 0.3s ease;
}

.ProgressBar span {
    padding: 0 8px;
    white-space: nowrap;
}

/* Solvencia Styles */
.EstadoBadge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.EstadoBadge--pendiente {
    background: #fef3c7;
    color: #92400e;
}

.EstadoBadge--pagado {
    background: #d1fae5;
    color: #065f46;
}

.EstadoBadge--atrasado {
    background: #fee2e2;
    color: #991b1b;
}

.EstadoBadge--cumplido {
    background: #d1fae5;
    color: #065f46;
}

.EstadoBadge--ausente {
    background: #fee2e2;
    color: #991b1b;
}

.EstadoBadge--admin {
    background: #ede9fe;
    color: #5b21b6;
}

.EstadoBadge--socio {
    background: #ecfdf5;
    color: #065f46;
}

.EstadoBadge--afiliado {
    background: #fef3c7;
    color: #92400e;
}

.Card--info {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 6px;
    padding: 15px;
    margin: 15px 0;
}

.Grid--4 {
    grid-template-columns: repeat(4, 1fr);
}

@media (max-width: 1024px) {
    .Grid--4 {
        grid-template-columns: repeat(2, 1fr);
    }
}

.Table--responsive {
    overflow-x: auto;
    white-space: nowrap;
}

/* Estilos para botones de exportación */
.ExportActions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
    margin-top: 16px;
}

.ExportActions .Btn {
    white-space: nowrap;
    flex-shrink: 0;
}

@media (max-width: 767px) {
    .ExportActions {
        flex-direction: column;
        gap: 4px;
    }
    
    .ExportActions .Btn {
        width: 100%;
        justify-content: center;
    }
    
    .Table--responsive {
        font-size: 0.85rem;
    }
    
    .Table th, .Table td {
        padding: 8px 4px;
    }
    
    .EstadoBadge {
        font-size: 0.7rem;
        padding: 2px 6px;
    }
}
    </style>
</head>
<body>
    <?php require 'templates/navigation.php'; ?>
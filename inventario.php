<?php
$archivo_json = 'productos.json';
if (!file_exists($archivo_json)){
    file_put_contents($archivo_json, json_encode([]));
}
$contenido = file_get_contents($archivo_json);
$productos = json_decode($contenido, true); 
function agregar(){
    global $archivo_json, $productos; 
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $nombre = htmlspecialchars(trim($_POST['nombre']));
        $cantidad = (int)$_POST['cantidad'];
        $precio = (float)$_POST['precio'];
        if(!empty($nombre) && $cantidad >= 0 && $precio >= 0){
            $nuevo_producto = [
                "id" => time(), // CORREGIDO: Quitamos el espacio en blanco de "id "
                "nombre" => $nombre,
                "cantidad" => $cantidad,
                "precio" => $precio // La última coma es opcional, está bien
            ];
            $productos[] = $nuevo_producto;
            file_put_contents($archivo_json, json_encode($productos, JSON_PRETTY_PRINT));
            header("Location: inventario.php");
            exit;
        }
    }
}
function actualizar(){
    global $archivo_json, $productos;

    // Supongamos que enviamos la actualización por POST desde un formulario de edición
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_id'])) {
        $id_editar = (int)$_POST['actualizar_id'];
        $nuevo_nombre = htmlspecialchars(trim($_POST['nombre']));
        $nueva_cantidad = (int)$_POST['cantidad'];
        $nuevo_precio = (float)$_POST['precio'];

        // Recorremos los productos por REFERENCIA usando el signo '&'
        // Esto permite modificar el valor directamente dentro del array original
        foreach ($productos as &$prod) {
            if ($prod['id'] === $id_editar) {
                $prod['nombre'] = $nuevo_nombre;
                $prod['cantidad'] = $nueva_cantidad;
                $prod['precio'] = $nuevo_precio;
                break; // Ya lo encontramos, dejamos de buscar
            }
        }

        // Guardamos los cambios
        file_put_contents($archivo_json, json_encode($productos, JSON_PRETTY_PRINT));
        header("Location: inventario.php");
        exit;
    }
}
function eliminar(){
    global $archivo_json, $productos;
    if(isset($_GET['eliminar'])){
        $id_borrar = (int)$_GET['eliminar'];
        $productos_limpios =[];
        foreach($productos as $prod){
            if($prod['id']!== $id_borrar){
                $productos_limpios []= $prod;
            }
        }
        $productos =$productos_limpios;
        file_put_contents($archivo_json, json_encode($productos,JSON_PRETTY_PRINT));
        header("Location: inventario.php");
        exit;
    }
}




$gran_total = 0;
foreach ($productos as $prod) {
    $gran_total += ($prod['precio'] * $prod['cantidad']);
}
function exportarReporte(){
    global $productos;

    if (isset($_GET['reporte'])) {
        $tipo = $_GET['reporte'];
        $fecha = date('Y-m-d_H-i-s');

        switch ($tipo) {
            case 'txt':
                // Configurar cabeceras de descarga para TXT
                header("Content-Type: text/plain; charset=utf-8");
                header("Content-Disposition: attachment; filename=reporte_inventario_$fecha.txt");
                
                echo "=========================================\r\n";
                echo "        REPORTE DE INVENTARIO            \r\n";
                echo "        Fecha: " . date('Y-m-d H:i:s') . "       \r\n";
                echo "=========================================\r\n\r\n";
                
                $gran_total = 0;
                foreach ($productos as $prod) {
                    $subtotal = $prod['precio'] * $prod['cantidad'];
                    $gran_total += $subtotal;
                    echo "ID: {$prod['id']}\r\n";
                    echo "Producto: {$prod['nombre']}\r\n";
                    echo "Precio: \$" . number_format($prod['precio'], 2) . "\r\n";
                    echo "Cantidad: {$prod['cantidad']}\r\n";
                    echo "Subtotal: \$" . number_format($subtotal, 2) . "\r\n";
                    echo "-----------------------------------------\r\n";
                }
                echo "\r\nVALOR TOTAL DEL INVENTARIO: \$" . number_format($gran_total, 2) . "\r\n";
                exit;

            case 'csv':
                // Configurar cabeceras de descarga para CSV (compatible con Excel)
                header("Content-Type: text/csv; charset=utf-8");
                header("Content-Disposition: attachment; filename=reporte_inventario_$fecha.csv");
                
                // Agregar la marca BOM para que Excel detecte los acentos correctamente
                echo "\xEF\xBB\xBF"; 
                
                $output = fopen('php://output', 'w');
                
                // Cabeceras de las columnas
                fputcsv($output, ['ID', 'Producto', 'Precio ($)', 'Cantidad', 'Total ($)']);
                
                $gran_total = 0;
                foreach ($productos as $prod) {
                    $subtotal = $prod['precio'] * $prod['cantidad'];
                    $gran_total += $subtotal;
                    fputcsv($output, [
                        $prod['id'],
                        $prod['nombre'],
                        number_format($prod['precio'], 2, '.', ''),
                        $prod['cantidad'],
                        number_format($subtotal, 2, '.', '')
                    ]);
                }
                
                // Fila final con el gran total
                fputcsv($output, ['', '', '', 'VALOR TOTAL:', number_format($gran_total, 2, '.', '')]);
                fclose($output);
                exit;

            case 'json':
                // Configurar cabeceras de descarga para JSON
                header("Content-Type: application/json; charset=utf-8");
                header("Content-Disposition: attachment; filename=reporte_inventario_$fecha.json");
                
                // Calculamos el total antes de enviar para estructurar un JSON más completo
                $gran_total = 0;
                foreach ($productos as $prod) {
                    $gran_total += ($prod['precio'] * $prod['cantidad']);
                }

                $estructura_reporte = [
                    "fecha_generacion" => date('Y-m-d H:i:s'),
                    "total_inventario" => $gran_total,
                    "productos" => $productos
                ];

                echo json_encode($estructura_reporte, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                exit;
        }
    }
}

agregar();
actualizar();
eliminar();
exportarReporte();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario Web Profesional</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 40px; background-color: #f4f7f6; color: #333; }
        h1 { color: #2c3e50; }
        .container { display: flex; gap: 20px; }
        .box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); flex: 1; }
        input, button { display: block; width: 95%; margin-bottom: 10px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { background-color: #27ae60; color: white; border: none; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #219150; }
        .btn-alert { background-color: #e67e22; }
        .btn-alert:hover { background-color: #d35400; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #2c3e50; color: white; }
        .flash { padding: 10px; margin-bottom: 15px; border-radius: 4px; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; }
        .danger { background-color: #f8d7da; color: #721c24; }
        .btn-delete { color: #e74c3c; text-decoration: none; font-weight: bold; }
        .footer-container { display: flex; justify-content: space-between; align-items: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid #ddd; }
        .report-buttons { display: flex; gap: 10px; }
        .btn-report { padding: 10px 15px; text-decoration: none; font-weight: bold; color: white; border-radius: 4px; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: background 0.2s; }
        .btn-txt { background-color: #7f8c8d; }
        .btn-txt:hover { background-color: #6c7a7d; }
        .btn-csv { background-color: #2980b9; }
        .btn-csv:hover { background-color: #2471a3; }
        .btn-json { background-color: #8e44ad; }
        .btn-json:hover { background-color: #7d3c98; }
        .total-box { font-size: 24px; font-weight: bold; color: #2c3e50; }
    </style>
</head>
<body>

    <h1>🛒 Sistema de Inventarios (PHP) 💹</h1>
    <hr>

    <?php if (isset($_GET['status']) && isset($_GET['msg'])): ?>
        <div class="flash <?php echo htmlspecialchars($_GET['status']); ?>">
            <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="box">
            <h3>➕ Agregar Producto</h3>
            <form action="inventario.php" method="POST">
                <input type="hidden" name="accion" value="agregar">
                <input type="text" name="nombre" placeholder="Nombre del producto" required>
                <input type="number" step="0.01" name="precio" placeholder="Precio" required>
                <input type="number" name="cantidad" placeholder="Cantidad" required>
                <button type="submit">Agregar al Inventario</button>
            </form>
        </div>

        <div class="box">
            <h3>🔄 Actualizar Stock </h3>
            <form action="inventario.php" method="POST">
                <input type="hidden" name="accion" value="actualizar">
                <input type="text" name="nombre" placeholder="Nombre del producto a buscar" required>
                <input type="number" step="0.01" name="precio" placeholder="Nuevo precio" required>
                <input type="number" name="cantidad" placeholder="Nueva cantidad" required>
                <button type="submit" class="btn-alert">Actualizar Producto</button>
            </form>
        </div>
    </div>

    <h2>📦 Productos en Existencia</h2>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Producto</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($productos)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #777;">No hay productos en el inventario.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($productos as $producto): ?>
                <tr>
                    <td><?php echo $producto['id']; ?></td>
                    <td><?php echo $producto['nombre']; ?></td>
                    <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                    <td><?php echo $producto['cantidad']; ?></td>
                    <td>$<?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?></td>
                    <td>
                        <a href="inventario.php?eliminar=<?php echo $producto['id']; ?>" class="btn-delete" onclick="return confirm('¿Seguro que deseas eliminar este producto?');">❌ Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="report-buttons">
    <a href="inventario.php?reporte=txt" class="btn-report btn-txt">📄 Reporte TXT</a>
    <a href="inventario.php?reporte=csv" class="btn-report btn-csv">📊 Reporte CSV</a>
    <a href="inventario.php?reporte=json" class="btn-report btn-json">⚙️ Reporte JSON</a>
</div>
        
        <div class="total-box">
            💰 Valor Total del Inventario: $<?php echo number_format($gran_total, 2); ?>
        </div>
    </div>

</body>
</html>
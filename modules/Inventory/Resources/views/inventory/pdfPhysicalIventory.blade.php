<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inventario Físico</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { text-align: center; color: blue; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; border: 2px solid blue; }
        th, td { border: 1px solid blue; padding: 10px; text-align: left; }
        th { background-color: #cce5ff; color: black; }
    </style>
</head>
<body>
    <h1>Reporte de Inventario Físico</h1>

    <p><strong>Establecimiento:</strong> {{ $data->establishment_description }}</p>
    <p><strong>Almacén:</strong> {{ $data->warehouse_description }}</p>
    <p><strong>Tipo de Ajuste:</strong> {{ $data->adjustment_type_name }}</p>
    <p><strong>ID:</strong> {{ $data->id }}</p>
    <p><strong>Descripción:</strong> {{ $data->comment }}</p>
    <p><strong>Fecha:</strong> {{ $data->created_at }}</p>

    <h2>Detalles del Inventario</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Descripción del Ítem</th>
                <th>Categoría</th>
                <th>Stock Sistema</th>
                <th>Stock Real</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $detail)
            <tr>
                <td>{{ $detail->id }}</td>
                <td>{{ $detail->item_description }}</td>
                <td>{{ $detail->category_name }}</td>
                <td>{{ $detail->system_quantity }}</td>
                <td>{{ $detail->counted_quantity}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

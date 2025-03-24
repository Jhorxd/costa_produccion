<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inventario Físico</title>
    <style>
        .full-width{
            width: 100%;
        }
        .half-width{
            width: 50%;
        }
        .fourteen-width{
            width: 40%;
        }
        .ten-width{
            width: 10%;
        }
        .celda {
            text-align: center;
            padding: 5px;
            border: 0.1px solid black;
        }
    
        .text-left{
            text-align: left;
        }
        .text-right{
            text-align: right;
        }
        .five-width{
            width: 5%;
        }
        html {
            font-family: sans-serif;
            font-size: 12px;
        }
    
        .page-break {
            page-break-after: always;
        }
    
        table {
            width: 100%;
            border-spacing: 0;
            border: 1px solid black;
        }
    
        table.no-border{
            border: 0px solid white;
    
        }
    
    
        th {
            padding: 5px;
            text-align: center;
            border-color: #0088cc;
            border: 0.1px solid black;
        }
    
        .title {
            font-weight: bold;
            padding: 5px;
            font-size: 20px !important;
            text-decoration: underline;
        }
    
        p>strong {
            margin-left: 5px;
            font-size: 13px;
        }
    
        thead {
            font-weight: bold;
            background: #0088cc;
            color: white;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php
        $document_type = 'REPORTE DE INVENTARIO FÍSICO';
        $series = !empty($data->series) ? $data->series : 'NT';
        $number = !empty($data->number) ? $data->number : 0;
    ?>
    <div>
        <table class="no-border">
            <tr>
                <td
                    colspan="4"
                    align="center"
                    style="max-width: 300px; height: auto;"
                >
                @if(!empty($company->logo))
                    <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}"
                        alt="{{$company->name}}"
                        class="company_logo_ticket contain"
                        style="max-width: 300px; height: auto;"
                        >
                @endif
                </td>
    
                <td
                    colspan="4"
                    align="left"
                    style="max-width: 300px; height: auto;"
                >
                    <table style="border:2px solid black; max-width: 150px;" >
                        <tr>
                            <td
                                align="center"
                            >
                                <h3 class="font-bold">{{ 'R.U.C. '.$company->number }}</h3>
                                <h3 class="text-center font-bold">{{ $document_type }}</h3>
                                <h3 class="text-center font-bold">{{ $series }} - {{ $number }}</h3>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <div>
        <table  class="no-border">
            <tr>
                <td>
                    <table  class="no-border">
                        <tr>
                            <td
                                {{-- colspan="2" --}}
                            >
                                <strong>ESTABLECIMIENTO</strong>
                            </td>
                            <td
                                {{-- colspan="2" --}}
                            >
                                {{ $data->establishment_description }}
                            </td>
                            <td>
                                <strong>ALMACÉN:</strong>
                            </td>
                            <td>
                                {{$data->warehouse_description}}
                            </td>
                        </tr>
                        <tr>
                            <td
                                {{-- colspan="2" --}}
                            >
                                <strong>TIPO DE AJUSTE</strong>
                            </td>
                            <td
                                {{-- colspan="2" --}}
                            >
                                {{ $data->adjustment_type_name }}
                            </td>
                            <td>
                                <strong>DESCRIPCIÓN:</strong>
                            </td>
                            <td>
                                {{ $data->comment ?? '---' }}
                            </td>
                        </tr>
                        <tr>
                            <td
                                {{-- colspan="2" --}}
                            >
                                <strong>FECHA</strong>
                            </td>
                            <td
                                {{-- colspan="2" --}}
                            >
                                {{ $data->created_at }}
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

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
                <td class="celda text-center">{{ $detail->id }}</td>
                <td class="celda text-left">{{ $detail->item_description }}</td>
                <td class="celda">{{ $detail->category_name }}</td>
                <td class="celda">{{ $detail->system_quantity }}</td>
                <td class="celda">{{ $detail->counted_quantity}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

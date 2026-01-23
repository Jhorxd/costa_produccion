<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Competencia</title>
    <style>
        html {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-spacing: 0;
            border: 1px solid black;
        }

        .celda {
            text-align: center;
            padding: 5px;
            border: 0.1px solid black;
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

        p > strong {
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
<div>
    <p align="center" class="title"><strong>Reporte Ventas por precio de Competencia</strong></p>
</div>
<div style="margin-top:20px; margin-bottom:20px;">
    <table>
        <tr>
            <td>
                <p><strong>Empresa: </strong>{{$company->name}}</p>
            </td>
            <td>
                <p><strong>Fecha: </strong>{{date('Y-m-d')}}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p><strong>Ruc: </strong>{{$company->number}}</p>
            </td>
            <td>
                <p><strong>Establecimiento: </strong>{{$establishment->address}}
                    - {{$establishment->department->description}} - {{$establishment->district->description}}</p>
            </td>
        </tr>
    </table>
</div>
@if(!empty($records))
    <div class="">
        <div class=" ">
            <table class="">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre Producto</th>
                    <th>Descripción</th>
                    <th>Precio Local</th>
                    <th>P. Competencia 1</th>
                    <th>P. Competencia 2</th>
                    <th>P. Competencia 3</th>
                    <th>P. Competencia 4</th>
                </tr>
                </thead>
                <tbody>
                @foreach($records as $key => $value)
                    
                    <tr>
                        <td class="celda">{{$loop->iteration}}
                        </td>
                        <td class="celda">{{$value->item_description}}
                        </td>
                        <td class="celda">{{$value->description}}</td>
                        <td class="celda">{{$value->label_1}}</td>
                        <td class="celda">
                            {{$value->label_2}}
                        </td>
                        <td class="celda">
                            {{$value->label_3}}
                        </td>
                        <td class="celda">
                            {{$value->label_4}}
                        </td>

                        <td class="celda">
                            {{$value->label_5}}
                        </td>
                  
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="callout callout-info">
        <p>No se encontraron registros.</p>
    </div>
@endif
</body>
</html>

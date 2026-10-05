<html>
    <tr></tr>
    <tr>
        @foreach ($data as $area => $tipos)
            <td colspan="7" style="border: 1px solid black;">{{ $area }}</td>
            <td></td>
        @endforeach
    </tr>
    <tr>
        @foreach ($data as $area => $tipos)
            <td style="border: 1px solid black; width: 1.37cm;">ITEMS</td>
            <td style="border: 1px solid black; width: 5.15cm;">DESCRIPCIÓN</td>
            <td style="border: 1px solid black; width: 3.10cm;">UBICACIÓN</td>
            <td style="border: 1px solid black; width: 3.36cm;">CANTIDAD TOTAL</td>
            <td style="border: 1px solid black; width: 2.43cm;">FUNCIONAL</td>
            <td style="border: 1px solid black; width: 2.99cm;">NO FUNCIONAL</td>
            <td style="border: 1px solid black; width: 2.82cm;">OBSERVACIÓN</td>
            <td style="width: 2.09cm;"></td>
        @endforeach
    </tr>
    @php
        $index = 1;
    @endphp
    @foreach($types as $type => $typeName)
        <tr>
            @foreach ($data as $area => $tipos)
                <td style="border: 1px solid black;">{{ $index }}</td>
                <td style="border: 1px solid black;">{{ $typeName }}</td>
                <td style="border: 1px solid black;">{{ $area }}</td>
                <td style="border: 1px solid black;">{{ ($tipos[$type]['funcionales'] ?? 0) + ($tipos[$type]['no_funcionales'] ?? 0) }}</td>
                <td style="border: 1px solid black;">{{ $tipos[$type]['funcionales'] ?? 0 }}</td>
                <td style="border: 1px solid black;">{{ $tipos[$type]['no_funcionales'] ?? 0 }}</td>
                <td style="border: 1px solid black;"></td>
                <td></td>
            @endforeach
        </tr>
        @php
            $index++;
        @endphp
    @endforeach
</html>
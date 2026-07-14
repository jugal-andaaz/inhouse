<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Report</title>
    <style>
        body { font-family: arial, sans-serif; font-size: 12px; }
         /* Header & Footer */
        header {
            position: fixed;
            top: -50px;
            left: 0;
            right: 0;
            height: 40px;
            text-align: center;
            line-height: 40px;
            font-size: 14px;
            font-weight: bold;
        }

        footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 10px;
            color: #555;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ccc; }
        th, td { padding: 4px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        ANDAAZ FASHION
    </header>
    <h2>Order Report with Remarks</h2>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Product SKU</th>
                <th>Dispatch Date</th>                
                <th>Source</th>
                <th>Status</th>
                <th>Given For</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row->order_id }}</td>
                    <td>{{ $row->item_sku }}</td>
                    <td>{{ $row->dispatch_date }}</td>
                    <td>{{ $row->source }}</td>
                    <td>{{ $row->sub_status_status }}<br />({{ $row->statuslocation }})</td>
                    <td>{{ $row->given_for ?? '' }}<br />{{ $row->doer_name }}</td>
                    <td>{{ $row->remark ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table> 
    <footer>
        © {{ date('Y') }} SV Global Designs Private Limited
    </footer>

<script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->get_font("DejaVu Sans", "normal");
        $pdf->page_text(520, 820, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, 9, [0,0,0]);
    }
</script>
</body>
</html>

@php
$addonsArray = [];

if (strpos($product_size, 'Blouse Tailoring Service') !== false) {

    if (preg_match('/Convert into Ready to Wear Saree:([^|]*)/', $product_size, $match)) {
        $addonsArray['Convert into Ready to Wear Saree'] = trim($match[1]);
    }

    if (preg_match('/Petticoat:([^|]*)/', $product_size, $match)) {
        $addonsArray['Petticoat'] = str_replace('\/', '/', trim($match[1]));
    }

    if (preg_match('/Blouse Pad:([^|]*)/', $product_size, $match)) {
        $addonsArray['Blouse Pad'] = trim($match[1]);
    }

    if (preg_match('/Fall & Pico:([^|]*)/', $product_size, $match)) {
        $addonsArray['Fall & Pico'] = str_replace('"','',trim($match[1]));
    }
}
@endphp


@if (!empty($addonsArray))
<div class="table-responsive">
    <table class="table table-bordered table-striped product-tbl-font font-weight-bold">
        <thead>
            <tr>
                <th scope="col" colspan="2">
                    <h2 style="margin: 0;">Saree Add-On's</h2>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($addonsArray as $addonKey => $addonVal)
                <tr>
                    <td class="high-lightbg">
                        <h4 style="margin: 0;">{{ $addonKey }}</h4>
                    </td>
                    <td class="high-lightbg">
                        <h3 style="margin: 0;">{{ html_entity_decode($addonVal, ENT_QUOTES | ENT_HTML5, 'UTF-8' ); }}</h3>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

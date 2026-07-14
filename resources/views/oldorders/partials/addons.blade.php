@php
    $addons = '';
    $addonsArray = [];

    if (strpos($product_size, 'Add Ons:') !== false) {
        // Extract the part starting from "Add Ons:" up to the next pipe (|) or end of string
        preg_match('/Add Ons:([^|]*)/', $product_size, $matches);

        if (isset($matches[1])) {            
            $addons = str_replace('"', '', trim($matches[1]));
            $addonsArray = array_map('trim', explode(',', $addons));
        }
    }
@endphp 
 
@if (!empty($addonsArray) && $addons !='')
    <div class="table-responsive">
        <table class="table table-bordered table-striped product-tbl-font font-weight-bold">
            <thead>
                <tr>
                    <th scope="col"><h2 style="margin: 0;">Add-On's</h2></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($addonsArray as $addon)
                    <tr>                       
                        <td class="high-lightbg">
                            <h3 style="margin: 0;">{{ $addon }}</h3>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

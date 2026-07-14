@php
    $specialInstructCollection = getOldSpecialInstructionBySkuItemId($product->product_sku, $product->id);
@endphp
 
@if(count($specialInstructCollection) > 0)
    
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th scope="col" class="printfont">Heading</th> 
                    <th scope="col" class="printfont" colspan="2">Instruction</th> 
                </tr>
            </thead>
            <tbody>
                @foreach($specialInstructCollection as $specialInstruction)
                    <tr>
                        <td class="high-lightbg w-28">{{ $specialInstruction->heading }}</td>
                        <td class="high-lightbg w-50">
                            {{ $specialInstruction->instruction }}
                        </td>
                        <td class="high-lightbg w-20">
                            @if(!empty($specialInstruction->image_url) && ($specialInstruction->image_url!='--'))
                                <p style="margin: 0;"><img src="{{ $specialInstruction->image_url }}" alt="Instruction Image" style="max-width: 100px;" /></p>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    
@endif

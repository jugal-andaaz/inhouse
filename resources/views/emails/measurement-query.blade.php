<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Measurement Query</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,sans-serif;font-size:14px;color:#333;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f5f5;">
<tr><td align="center" style="padding:24px 10px;">
<table width="620" cellpadding="0" cellspacing="0" border="0">

    {{-- Logo --}}
    <tr>
        <td align="center" style="padding:0 0 16px;">
            <img src="https://inhouse.andaazfashion.com/assets/img/andaaz-fashion-logo.png"
                 alt="Andaaz Fashion" width="160" style="display:block;margin:0 auto;"/>
        </td>
    </tr>

    {{-- Card --}}
    <tr>
        <td style="background:#fff;border-radius:6px;overflow:hidden;border:1px solid #e0e0e0;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">

            {{-- Banner --}}
            <tr>
                <td style="background:#a10047;padding:20px 28px;">
                    <p style="margin:0;font-size:11px;font-weight:700;color:rgba(255,255,255,0.75);
                               text-transform:uppercase;letter-spacing:1px;">
                        Men's &nbsp;·&nbsp; Kurta-Pajama &nbsp;·&nbsp; Sherwani &nbsp;·&nbsp; Jodhpuri &nbsp;·&nbsp; Kurta
                    </p>
                    <p style="margin:6px 0 0;font-size:20px;font-weight:700;color:#fff;">
                        Measurement Query
                    </p>
                    <p style="margin:4px 0 0;font-size:13px;color:rgba(255,255,255,0.8);">
                        Order: <strong>{{ $orderId }}</strong>
                        @if($productDesign)
                            &nbsp;·&nbsp; Design: <strong>{{ $productDesign }}</strong>
                        @endif
                    </p>
                </td>
            </tr>

            {{-- Greeting --}}
            <tr>
                <td style="padding:24px 28px 0;">
                    <p style="margin:0;font-size:16px;color:#333;">
                        Hello <strong>{{ $customerFirstName }}</strong>,
                    </p>
                    <p style="margin:10px 0 0;font-size:14px;color:#555;line-height:1.7;">
                        Greetings From <strong>Andaaz Fashion</strong>. Hope you are doing well.
                    </p>
                    <p style="margin:8px 0 0;font-size:14px;color:#555;line-height:1.7;">
                        This is regarding your recent order with us
                        <strong style="color:#a10047;">{{ $orderId }}</strong>
                    </p>
                </td>
            </tr>

            {{-- Product images --}}
            @if($items->count() > 0)
            <tr>
                <td style="padding:20px 28px 0;">
                    <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                        @foreach($items as $item)
                            @php $imgUrl = $item->product_img ?? ''; @endphp
                            <td style="padding-right:12px;vertical-align:top;">
                                @if($imgUrl)
                                    <a href="{{ $imgUrl }}" target="_blank" style="display:block;">
                                        <img src="{{ $imgUrl }}" alt="{{ $item->product_name ?? '' }}"
                                             width="80" height="96"
                                             style="display:block;object-fit:cover;border-radius:4px;border:1px solid #eee;"/>
                                    </a>
                                @endif
                                @if(!empty($item->product_sku))
                                    <p style="margin:4px 0 0;font-size:11px;color:#888;text-align:center;
                                               font-family:monospace;">{{ $item->product_sku }}</p>
                                @endif
                            </td>
                        @endforeach
                        </tr>
                    </table>
                </td>
            </tr>
            @endif

            {{-- Divider --}}
            <tr>
                <td style="padding:20px 28px 0;">
                    <div style="border-top:1px solid #f0f0f0;"></div>
                </td>
            </tr>

            <!-- {{-- Father Son Section (product_type 9396) --}}
            @if($isFatherSon)
            <tr>
                <td style="padding:20px 28px 0;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="4" style="background:#a10047;border-radius:2px;">&nbsp;</td>
                            <td style="padding-left:12px;">
                                <p style="margin:0;font-size:14px;font-weight:700;color:#a10047;">
                                    Additional Measurements Required
                                </p>
                            </td>
                        </tr>
                    </table>
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" 
                        style="margin-top:12px;border-collapse:collapse;border:1px solid #eee;border-radius:4px;overflow:hidden;">
                        <thead>
                            <tr style="background:#f9f0f4;">
                                <th style="text-align:left;font-size:12px;color:#666;font-weight:700;
                                        padding:10px 14px;border-bottom:2px solid #e8d0dc;width:55%;">
                                        Measurement
                                    </th>
                                    <th style="text-align:left;font-size:12px;color:#666;font-weight:700;padding:10px 14px;border-bottom:2px solid #e8d0dc;">
                                        How to Measure
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="background:#fff;">
                                    <td style="padding:11px 14px;font-size:13px;color:#444;font-weight:600;">
                                        Full Body Height
                                    </td>
                                    <td style="padding:11px 14px;font-size:13px;">
                                        <a href="https://www.youtube.com/watch?v=dIttyziyQjM" target="_blank"
                                                style="display:inline-block;background:#a10047;color:#fff;font-size:12px;
                                                    font-weight:700;padding:7px 14px;border-radius:4px;
                                                    text-decoration:none;letter-spacing:0.3px;">
                                                ▶ &nbsp;Watch Video
                                            </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p style="margin:12px 0 0;font-size:13px;color:#555;line-height:1.7;
                                background:#fff8fb;border-left:3px solid #a10047;padding:10px 14px;
                                border-radius:0 4px 4px 0;">
                            Please share all these measurements for the <strong>kid</strong> as well.
                        </p>
                    </td>
                </tr>
                @endif -->

            {{-- Section 1: Chest Size --}}
            <tr>
                <td style="padding:20px 28px 0;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="4" style="background:#a10047;border-radius:2px;">&nbsp;</td>
                            <td style="padding-left:12px;">
                                <p style="margin:0;font-size:14px;font-weight:700;color:#a10047;">
                                    Around Chest Size
                                </p>
                            </td>
                        </tr>
                    </table>
                    <p style="margin:10px 0 0;font-size:14px;color:#444;line-height:1.7;">
                        @if($chestSize)
                            Please confirm the <strong>{{ $chestSize }}-inch chest size</strong> is your
                        @else
                            Please confirm your chest size is your
                        @endif
                        <strong>exact body chest measurement</strong> (without any loosening)
                        or <strong>garment chest size</strong> (with any loosening).
                    </p>
                    @php
                        $allChests  = collect(explode('&', $chestSize ?? '0'))->map(fn($s) => (int) trim($s))->filter();
                        $smallSizes = $allChests->filter(fn($s) => $s <= 48)->join(' & ');
                        $largeSizes = $allChests->filter(fn($s) => $s > 48)->join(' & ');
                    @endphp

                    @if($smallSizes)
                    <p style="margin:10px 0 0;font-size:13px;color:#666;line-height:1.7;
                               background:#fff8fb;border-left:3px solid #a10047;padding:10px 14px;
                               border-radius:0 4px 4px 0;">
                        For <strong>{{ $smallSizes }}-inch</strong> chest size — we generally allow <strong>4 inches
                        (2 inches on both sides)</strong> of loosening in the garment for ease of
                        movement and perfect fit on your body.
                    </p>
                    @endif

                    @if($largeSizes)
                    <p style="margin:10px 0 0;font-size:13px;color:#666;line-height:1.7;
                               background:#fff8fb;border-left:3px solid #a10047;padding:10px 14px;
                               border-radius:0 4px 4px 0;">
                        For <strong>{{ $largeSizes }}-inch</strong> chest size — we generally allow <strong>5 inches
                        (2.5 inches on both sides)</strong> of loosening in the garment for ease of
                        movement and perfect fit on your body.
                    </p>
                    @endif
                    <p style="margin:12px 0 0;">
                        <a href="https://youtu.be/FbQ9GQAkw8I"
                           style="display:inline-block;background:#a10047;color:#fff;font-size:12px;
                                  font-weight:700;padding:7px 14px;border-radius:4px;text-decoration:none;
                                  letter-spacing:0.3px;">
                            ▶ &nbsp;How to measure chest size
                        </a>
                    </p>
                </td>
            </tr>

            {{-- Section 2: Sleeve Length --}}
            <tr>
                <td style="padding:20px 28px 0;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="4" style="background:#a10047;border-radius:2px;">&nbsp;</td>
                            <td style="padding-left:12px;">
                                <p style="margin:0;font-size:14px;font-weight:700;color:#a10047;">
                                    Sleeve Length
                                </p>
                            </td>
                        </tr>
                    </table>
                    @foreach($heightSleevePairs as $pair)
                    <p style="margin:10px 0 0;font-size:14px;color:#444;line-height:1.7;">
                        As per your height of <strong>{{ $pair['height'] }}</strong>, sleeve length should be
                        approx. <strong>{{ $pair['sleeve'] }} inches</strong>.
                    </p>
                    @endforeach
                    <p style="margin:10px 0 0;font-size:13px;color:#555;line-height:1.7;">
                        Please confirm the sleeve length as per the image
                        <em style="color:#888;">(shoulder end to wrist)</em>.
                    </p>
                </td>
            </tr>

            {{-- Section 3: Measurement Table --}}
            <tr>
                <td style="padding:20px 28px 0;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="4" style="background:#a10047;border-radius:2px;">&nbsp;</td>
                            <td style="padding-left:12px;">
                                <p style="margin:0;font-size:14px;font-weight:700;color:#a10047;">
                                    Measurements Required
                                </p>
                            </td>
                        </tr>
                    </table>
                    <p style="margin:10px 0 12px;font-size:13px;color:#555;">
                        We would be grateful if you could share the following measurements to avoid delays:
                    </p>
                    <table width="100%" cellpadding="0" cellspacing="0" border="0"
                           style="border-collapse:collapse;border:1px solid #eee;border-radius:4px;overflow:hidden;">
                        <thead>
                            <tr style="background:#f9f0f4;">
                                <th style="text-align:left;font-size:12px;color:#666;font-weight:700;
                                           padding:10px 14px;border-bottom:2px solid #e8d0dc;width:55%;">
                                    Measurement
                                </th>
                                <th style="text-align:left;font-size:12px;color:#666;font-weight:700;
                                           padding:10px 14px;border-bottom:2px solid #e8d0dc;">
                                    Your Size (inches)
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        @php
                            $rows = [
                                ['Around Chest',  '', 'https://inhouse.andaazfashion.com/uploads/measurementimg/measurechestcircumference.jpg'],
                                ['Sleeve Length', '', 'https://inhouse.andaazfashion.com/uploads/measurementimg/measuresleevelengths.jpg'],
                                ['Shoulder',      '', 'https://inhouse.andaazfashion.com/uploads/measurementimg/measureshoulder.jpg'],
                                ['Natural Waist', '', 'https://inhouse.andaazfashion.com/uploads/measurementimg/measurewaist.jpg'],
                                ['Bottom Waist',  '', 'https://inhouse.andaazfashion.com/uploads/measurementimg/measuretrouserpantwaist.jpg'],
                                ['Around Hip',    '', 'https://inhouse.andaazfashion.com/uploads/measurementimg/measurehipcircumference.jpg'],
                                ['Biceps',        '', 'https://inhouse.andaazfashion.com/uploads/measurementimg/measurebiceps.jpg'],
                            ]; 
                        @endphp
                        @foreach($rows as $i => $row)
                            <tr style="background:{{ $i % 2 === 0 ? '#fff' : '#fdfafa' }};
                                        border-bottom:1px solid #f0f0f0;">
                                <td style="padding:11px 14px;font-size:13px;color:#444;font-weight:600;">
                                    {{ $row[0] }}
                                </td>
                                <td style="padding:11px 14px;font-size:13px;">
                                    @if($row[1])
                                        <span style="color:#a10047;display:block;margin-bottom:6px;">{{ $row[1] }}</span>
                                    @endif
                                    @if($row[2])
                                        <a href="{{ $row[2] }}" target="_blank" style="display:inline-block;">
                                            <img src="{{ $row[2] }}" alt="{{ $row[0] }}"
                                                 style="max-height:100px;max-width:100px;display:block;
                                                        border-radius:3px;border:1px solid #eee;"/>
                                        </a><br>
                                        <a href="{{ $row[2] }}" target="_blank" download
                                           style="display:inline-block;margin-top:4px;font-size:11px;
                                                  color:#a10047;text-decoration:none;">
                                            ⬇ Download
                                        </a>
                                    @elseif(!$row[1])
                                        <span style="color:#bbb;">___________</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>

            {{-- Section 4: Occasion Date --}}
            <tr>
                <td style="padding:20px 28px 0;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="4" style="background:#a10047;border-radius:2px;">&nbsp;</td>
                            <td style="padding-left:12px;">
                                <p style="margin:0;font-size:14px;font-weight:700;color:#a10047;">
                                    Occasion Date
                                </p>
                            </td>
                        </tr>
                    </table>
                    @if($occasion)
                    <p style="margin:10px 0 0;font-size:14px;color:#444;line-height:1.7;">
                        Occasion date on record: <strong style="color:#a10047;">{{ $occasion }}</strong>
                    </p>
                    @endif
                    <p style="margin:10px 0 0;font-size:13px;color:#555;line-height:1.7;">
                        Also, please share your occasion date — when you are planning to wear this outfit.
                        It will help us plan dispatch timelines accordingly.
                    </p>
                </td>
            </tr>

            {{-- Closing --}}
            <tr>
                <td style="padding:24px 28px;">
                    <p style="margin:0;font-size:14px;color:#444;line-height:1.7;">
                        Thank you for choosing us.
                    </p>
                    <p style="margin:16px 0 0;font-size:14px;color:#333;">
                        Regards,<br/>
                        <strong>Shivani</strong><br/>
                        <span style="color:#a10047;font-weight:600;">Andaaz Fashion</span>
                    </p>
                </td>
            </tr>

        </table>
        </td>
    </tr>

    {{-- Footer --}}
    <tr>
        <td align="center" style="padding:16px 10px;">
            <p style="margin:0;font-size:12px;color:#aaa;">
                &copy; {{ date('Y') }} Andaaz Fashion
            </p>
        </td>
    </tr>

</table>
</td></tr>
</table>
</body>
</html>

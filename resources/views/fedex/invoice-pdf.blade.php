<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <style>
      @page { size: A4 portrait; margin: 7mm; }
      * { margin: 0; padding: 0; box-sizing: border-box; }
      body { font-family: Arial, Helvetica, sans-serif; font-size: 9.5pt; color: #000;padding:5px 8px; }     
      table { width: 100%; border-collapse: collapse; }
      td, th { padding: 2pt 3pt; vertical-align: top; font-size: 9.5pt; }
      .sec-hdr { background-color: #d4d4d4; font-weight: bold; text-align: center; font-size: 9pt; letter-spacing: 0.3pt; }
      .label { font-weight: bold; font-size: 8.5pt; color: #222; }
    </style>
  </head>
  <body>
    <div>

    {{-- ===== HEADER ===== --}}
      <table>
        <tr>
          {{-- INVOICE title --}}
          <td width="38%" style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;font-size:14pt;font-weight:bold;text-align:center;letter-spacing:2pt;padding:7pt 3pt;vertical-align:middle;">
            {{ ($sheet_name ?? '') === 'FEDEX' ? 'COMMERCIAL INVOICE' : 'INVOICE' }}
          </td>
          {{-- AWB block --}}
          <td width="62%" style="border-bottom:0.75pt solid #000;vertical-align:middle;padding:4pt 6pt;">            
            <span style="font-size:10pt;vertical-align: middle; font-weight:bold;color:#444;letter-spacing:0.3pt;">FedEx INTERNATIONAL AIRWAYBILL</span>
            <span style="float: right;vertical-align: middle;font-size:12pt;font-weight:bold;">{{ $awb_number ?? '' }}</span>
          </td>
        </tr>
        <tr>
          <td style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;padding:3pt 4pt;">
            <span class="label">DATE OF EXPORT&nbsp;&nbsp;</span>
            <span style="float: right;">{{ $date_of_export ?? '' }}</span>
          </td>
          <td style="border-bottom:0.75pt solid #000;padding:3pt 4pt;">
            <span class="label">EXPORT REFERENCES&nbsp;&nbsp;</span>
            <span style="float: right; text-align: right;">{{ $export_reference ?? '' }}</span>
          </td>
        </tr>
        <tr>
          <td style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;padding:3pt 4pt;">
            <span class="label">INVOICE NUMBER&nbsp;&nbsp;</span>
            <span style="float: right;">{{ $invoice_number ?? '' }}</span>
          </td>
          <td style="border-bottom:0.75pt solid #000;padding:3pt 4pt;">
            <span class="label">INVOICE DATE&nbsp;&nbsp;</span>
            <span style="float: right;">{{ $invoice_date ?? '' }}</span>
          </td>
        </tr>
      </table>

      {{-- ===== SHIPPER / RECIPIENT ===== --}}
      <table>
        <tr>
          <td width="50%" class="sec-hdr" style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;padding:3pt;">SHIPPER / EXPORTER</td>
          <td width="50%" class="sec-hdr" style="border-bottom:0.75pt solid #000;padding:3pt;">RECIPIENT / CONSIGNEE</td>
        </tr>
        <tr>
          <td style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;height:76pt;vertical-align:top;line-height:1.6;padding:4pt;">
            <strong>{{ config('services.fedexftb.shipper.name') }}</strong><br>
            {{ config('services.fedexftb.shipper.company') }}<br>
            {{ config('services.fedexftb.shipper.street') }}<br>
            {{ config('services.fedexftb.shipper.city') }} {{ config('services.fedexftb.shipper.state') }} {{ config('services.fedexftb.shipper.postal') }} {{ config('services.fedexftb.shipper.country') }}<br>
            <span class="label">EMAIL:</span> {{ config('services.fedexftb.shipper.email') }}<br>
            <span class="label">TEL:</span> {{ config('services.fedexftb.shipper.phone') }}<br>
            <strong style="font-size:8.5pt;">SHIPPER'S TAX NUMBER: {{ config('services.fedexftb.shipper.gstin') }}</strong>
          </td>
          <td style="border-bottom:0.75pt solid #000;height:76pt;vertical-align:top;line-height:1.6;padding:4pt;">
            <strong>{{ $recipient_name ?? '' }}</strong><br>

            @if(!empty($recipient_address1))
                {!! str_replace('#', '<br>', e($recipient_address1)) !!}<br>
            @endif

            @if(!empty($recipient_address2))
                {!! str_replace('#', '<br>', e($recipient_address2)) !!}<br>
            @endif

            @if(!empty($recipient_city_state)){{ $recipient_city_state }}<br>@endif

            <span class="label">EMAIL:</span> {{ $recipient_email ?? '' }}<br>
            <span class="label">TEL:</span> {{ $recipient_phone ?? '' }}<br>
            <span class="label">RECIPIENT VAT/TIN/IOSS:</span> {{ $recipient_vat ?? '' }}
          </td>
        </tr>
      </table>

      {{-- ===== IMPORTER / INVOICE DETAILS ===== --}}
      @php $computedTotal = collect($items)->sum(fn($i) => (float)($i['quantity'] ?? 0) * (float)($i['unit_value'] ?? 0)); @endphp
      <table>
        <tr>
          <td width="50%" class="sec-hdr" style="padding:3pt;">IMPORTER OTHER THAN C/NEE OR BILL TO PARTY</td>
          <td width="50%" class="sec-hdr" style="border-bottom:0.75pt solid #000;padding:3pt;">
            @if (($sheet_name ?? '') === 'FEDEX')
              EXPORTER MANDATORY DETAILS
            @else
              INVOICE DETAILS
            @endif
          </td>
        </tr>
        <tr>
          <td style="border-right:0.75pt solid #000;vertical-align:top;"></td>
          <td style="vertical-align:top;padding:3pt;">
            @if (($sheet_name ?? '') === 'FEDEX')
              <table style="width:100%;border-collapse:collapse;font-size:8pt;">
                <tr>
                  <td style="border:0.5pt solid #111;padding:2pt 4pt;width:50%;font-size:8pt;"><span>CSB TYPE:</span><span style="float: right;">CSB5</span></td>
                  <td style="border:0.5pt solid #111;padding:2pt 4pt;width:50%;font-size:8pt;"><span>ECOM TYPE:</span><span style="float: right;">ECOM</span></td>
                </tr>
                <tr>
                  <td style="border:0.5pt solid #111;padding:2pt 4pt;font-size:8pt;"><span>INVOICE TYPE:</span><span style="float: right;">GST</span></td>
                  <td style="border:0.5pt solid #111;padding:2pt 4pt;font-size:8pt;"><span>TERM OF SALE:</span><span style="float: right;">CFR</span></td>
                </tr>
                <tr>
                  <td style="border:0.5pt solid #111;padding:2pt 4pt;font-size:8pt;"><span>AD CODE:</span><span style="float: right;">01803033480007</span></td>
                  <td style="border:0.5pt solid #111;padding:2pt 4pt;font-size:8pt;"><span>IEC NUMBER:</span><span style="float: right;">0516508041</span></td>
                </tr>
                <tr>
                  <td style="border:0.5pt solid #111;padding:2pt 4pt;font-size:8pt;"><span>LUT NO.:</span><span style="float: right;">AD070326014263G</span></td>
                  <td style="border:0.5pt solid #111;padding:2pt 4pt;font-size:8pt;"><span>LUT EXP. DATE:</span><span style="float: right;">31.03.2027</span></td>
                </tr>
                <tr>
                  <td style="border:0.5pt solid #111;padding:2pt 4pt;font-size:8pt;">
                    <span>PURPOSE OF SHIPMENT:</span><span style="float: right;vertical-align: middle;">SOLD</span>
                  </td>
                  <td style="border:0.5pt solid #111;padding:2pt 4pt;font-size:8pt;"><span>AGAINST BOND OR LUT</span></td>
                </tr>
                <tr>
                  <td style="border:0.5pt solid #111;padding:2pt 4pt;font-size:8pt;"><span>IGST PAID IF ANY (INR):</span></td>
                  <td style="border:0.5pt solid #111;padding:2pt 4pt;font-size:8pt;"><span>BANK IF ANY:</span></td>
                </tr>
                <tr>
                  <td colspan="2" style="border:0.5pt solid #111;padding:2pt 4pt;font-size:8pt;"><span>SHIPMENT FOB VALUE:</span> {{ $computedTotal }}</td>
                </tr>
              </table>
            @else
              <span class="label">INCOTERM:</span> {{ $incoterm ?? 'CFR' }}<br>
              {{ $notes ?? '' }}
            @endif
          </td>
        </tr>
        @if(($sheet_name ?? '') === 'CSB4')
        <tr>
          <td colspan="2" style="border-bottom:0.75pt solid #000;text-align:center;font-weight:bold;font-size:9pt;padding:4pt 3pt;letter-spacing:0.5pt;background-color:#f8f8f8;">SAMPLE PURPOSE ONLY NOT FOR SALE</td>
        </tr>
        @endif
      </table>

      {{-- ===== ITEMS TABLE ===== --}}
      <table>
        <thead>
          <tr>
            <th style="border:0.75pt solid #000;font-size:9.5pt;text-align:center;width:3.5%;padding:2pt 1pt;">S.<br>NO</th>
            <th style="border:0.75pt solid #000;font-size:9.5pt;text-align:center;width:19.5%;padding:2pt 1pt;">FULL DESCRIPTION OF GOODS</th>
            <th style="border:0.75pt solid #000;font-size:9.5pt;text-align:center;width:8%;padding:2pt 1pt;">STATE OF<br>ORIGIN</th>
            <th style="border:0.75pt solid #000;font-size:9.5pt;text-align:center;width:8%;padding:2pt 1pt;">DISTRICT<br>OF ORIGIN</th>
            <th style="border:0.75pt solid #000;font-size:9.5pt;text-align:center;width:7%;padding:2pt 1pt;">HS CODE</th>
            <th style="border:0.75pt solid #000;font-size:9.5pt;text-align:center;width:7%;padding:2pt 1pt;">COUNTRY<br>OF MFG</th>
            <th style="border:0.75pt solid #000;font-size:9.5pt;text-align:center;width:6%;padding:2pt 1pt;">NET WT<br>KG</th>
            <th style="border:0.75pt solid #000;font-size:9.5pt;text-align:center;width:4%;padding:2pt 1pt;">QTY</th>
            <th style="border:0.75pt solid #000;font-size:9.5pt;text-align:center;width:5%;padding:2pt 1pt;">UOM</th>
            <th style="border:0.75pt solid #000;font-size:9.5pt;text-align:center;width:8.5%;padding:2pt 1pt;">UNIT<br>VALUE</th>
            <th style="border:0.75pt solid #000;font-size:9.5pt;text-align:center;width:9%;padding:2pt 1pt;">TOTAL<br>VALUE</th>
            <th style="border:0.75pt solid #000;font-size:9.5pt;text-align:center;width:5%;padding:2pt 1pt;">IGST<br>%</th>
            <th style="border:0.75pt solid #000;font-size:9.5pt;text-align:center;width:9%;padding:2pt 1pt;">IGST AMT<br>(INR)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($items as $i => $item)
          <tr style="{{ $i % 2 === 1 ? 'background-color:#fafafa;' : '' }}">
            <td style="border:0.75pt solid #000;text-align:center;padding:2pt;">{{ $i + 1 }}</td>
            <td style="border:0.75pt solid #000;padding:2pt;">{{ strtoupper($item['description'] ?? '') }}</td>
            <td style="border:0.75pt solid #000;text-align:center;padding:2pt;">{{ strtoupper($item['state_of_origin'] ?? '') }}</td>
            <td style="border:0.75pt solid #000;text-align:center;padding:2pt;">{{ strtoupper($item['district_of_origin'] ?? '') }}</td>
            <td style="border:0.75pt solid #000;text-align:center;padding:2pt;">{{ $item['hs_code'] ?? '' }}</td>
            <td style="border:0.75pt solid #000;text-align:center;padding:2pt;">{{ strtoupper($item['country_of_mfg'] ?? 'INDIA') }}</td>
            <td style="border:0.75pt solid #000;text-align:right;padding:2pt;">{{ $item['net_weight'] ?? '' }}</td>
            <td style="border:0.75pt solid #000;text-align:center;padding:2pt;">{{ $item['quantity'] ?? '' }}</td>
            <td style="border:0.75pt solid #000;text-align:center;padding:2pt;">{{ strtoupper($item['uom'] ?? 'PCS') }}</td>
            <td style="border:0.75pt solid #000;text-align:right;padding:2pt;">{{ number_format((float)($item['unit_value'] ?? 0), 2) }}</td>
            <td style="border:0.75pt solid #000;text-align:right;padding:2pt;">{{ number_format((float)($item['quantity'] ?? 0) * (float)($item['unit_value'] ?? 0), 2) }}</td>
            <td style="border:0.75pt solid #000;text-align:center;padding:2pt;">{{ $item['igst_pct'] ?? '' }}</td>
            <td style="border:0.75pt solid #000;text-align:right;padding:2pt;">{{ $item['igst_amt'] ?? '' }}</td>
          </tr>
          @endforeach
          @for($pad = count($items); $pad < 1; $pad++)
          <tr style="height:14pt;">
            <td style="border:0.75pt solid #000;"></td><td style="border:0.75pt solid #000;"></td>
            <td style="border:0.75pt solid #000;"></td><td style="border:0.75pt solid #000;"></td>
            <td style="border:0.75pt solid #000;"></td><td style="border:0.75pt solid #000;"></td>
            <td style="border:0.75pt solid #000;"></td><td style="border:0.75pt solid #000;"></td>
            <td style="border:0.75pt solid #000;"></td><td style="border:0.75pt solid #000;"></td>
            <td style="border:0.75pt solid #000;"></td><td style="border:0.75pt solid #000;"></td>
            <td style="border:0.75pt solid #000;"></td>
          </tr>
          @endfor
        </tbody>
      </table> 

      {{-- ===== FOOTER TABLE ===== --}}
      {{-- 5 columns mirroring items table positions: --}}
      {{-- Col1=53% (S.NO→COUNTRY MFG)  Col2=15% (NETWT+QTY+UOM) --}}
      {{-- Col3=8.5% (UNIT VALUE)  Col4=9% (TOTAL VALUE ← value column) --}}
      {{-- Col5=14.5% (IGST%+IGST AMT) --}}
      <table>
        {{-- Row 1a: Additional info --}}
        <tr>
          <td width="53%"   style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;font-weight:bold;font-size:8.5pt;padding:3pt 4pt;">ADDITIONAL SHIPMENT/INVOICE INFO. IF ANY: @if(($sheet_name ?? '') === 'FEDEX' && strtoupper($recipient_country ?? '') === 'US') MID-INSVGLOC32DEL @endif </td>          
          <td colspan="2" width="15.5%" style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;font-weight:bold;font-size:9pt;text-align:right;padding:2pt 4pt;">Sub Total  </td>
          <td width="17.5%" style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;text-align:right;font-weight:bold;font-size:10pt;padding:2pt 4pt;">{{ number_format($computedTotal, 2) }}</td>
          <td width="9%" style="border-bottom:0.75pt solid #000;padding:2pt;"></td>
        </tr> 
        {{-- Row 2: Currency | Freight (label in Col2+3, value in Col4) | Package --}}
        <tr>
          <td style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;font-size:8.5pt;padding:3pt 4pt;vertical-align:top;"><span class="label">CURRENCY IN WORDS:</span><br>{{ $currency_words ?? 'INDIAN RUPEE' }}</td>
          <td colspan="2" style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;font-size:9pt;padding:3pt 4pt;vertical-align:middle;">TOTAL FREIGHT CHARGES</td>
          <td style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;text-align:right;font-weight:bold;font-size:10pt;padding:3pt 4pt;vertical-align:middle;">{{ number_format((float)($freight_charges ?? 0), 2) }}</td>
          <td style="border-bottom:0.75pt solid #000;font-weight:bold;text-align:right;font-size:9pt;padding:3pt 4pt;vertical-align:middle;">TOTAL PKG:&nbsp;{{ $total_packages ?? 1 }}</td>
        </tr>
        {{-- Row 3: Insurance (label in Col2+3, value in Col4) | Weight --}}
        <tr>
          <td style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;padding:3pt;"></td>
          <td colspan="2" style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;font-size:9pt;padding:3pt 4pt;vertical-align:middle;">TOTAL INSURANCE CHARGES</td>
          <td style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;text-align:right;font-weight:bold;font-size:10pt;padding:3pt 4pt;vertical-align:middle;">{{ number_format((float)($insurance_charges ?? 0), 2) }}</td>
          <td style="border-bottom:0.75pt solid #000;font-weight:bold;text-align:right;font-size:9pt;padding:3pt 4pt;vertical-align:middle;">TOTAL Wt.:&nbsp;{{ $total_weight ?? '' }} KG</td>
        </tr>
        {{-- Row 4: Declaration | Taxes --}}
        <tr>
          <td style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;font-size:8.5pt;font-weight:bold;padding:3pt 4pt;line-height:1.5;">I DECLARE ALL THE INFORMATION CONTAINED IN THIS INVOICE IS TRUE AND CORRECT TO THE BEST OF MY KNOWLEDGE.</td>
          <td colspan="2" style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;font-size:9pt;padding:3pt 4pt;vertical-align:middle;">TAXES</td>
          <td colspan="2" style="border-bottom:0.75pt solid #000;padding:3pt;"></td>
        </tr>
        {{-- Row 5: Date | Total Invoice Amount label (Col2+3) | value (Col4+5) --}}
        <tr>
          <td style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;vertical-align:top;padding:4pt;height:40pt;">
            <span class="label">DATE:</span> {{ $invoice_date ?? '' }}<br><br>
            <span style="font-size:11pt;color:#000;">(SIGNATURE REQUIREMENTS MAY VARY PER COUNTRY)</span>
          </td>
          <td colspan="2" style="border-right:0.75pt solid #000;border-bottom:0.75pt solid #000;font-weight:bold;text-align:center;vertical-align:middle;font-size:9pt;padding:3pt;">TOTAL INVOICE AMOUNT<br>{{ strtoupper($currency ?? 'INR') }}
          </td>
          <td colspan="2" style="border-bottom:0.75pt solid #000;font-weight:bold;text-align:center;vertical-align:middle;font-size:13pt;padding:3pt;">
            {{ number_format($computedTotal + (float)($freight_charges ?? 0) + (float)($insurance_charges ?? 0), 2) }}
          </td>
        </tr>
        {{-- Row 6: Name print | Signature --}}
        <tr>
          <td style="border-bottom:0.75pt solid #000;vertical-align:bottom;padding:4pt;height:48pt;">
            <span class="label" style="font-size:10pt;">NAME (PLEASE PRINT)</span><br>
            <span style="font-size:9.5pt;font-weight:bold;">{{ strtoupper(config('services.fedexftb.shipper.company')) }}</span>
          </td>
          <td colspan="4" style="border-bottom:0.75pt solid #000;text-align:center;vertical-align:middle;padding:4pt;height:48pt;">
            @if(!empty($signature_image))
              <img src="{{ $signature_image }}" style="max-height:42pt;max-width:260pt;display:block;margin:0 auto 2pt auto;">
            @endif
            <div style="border-top:0.75pt solid #000;margin:0 40pt;padding-top:2pt;font-size:11pt;color:#000;letter-spacing:0.3pt;">AUTHORIZED SIGNATURE</div>
          </td>
        </tr>
      </table>
    </div>
  </body>
</html>

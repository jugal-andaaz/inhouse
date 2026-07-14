@extends('layouts.app')

@section('page-title', __('Create FedEx Shipment'))
@section('page-heading', __('Create FedEx Shipment'))

@section('breadcrumbs')
    <li class="breadcrumb-item active">Create FedEx Shipment</li>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ url('assets/css/fedex.css') }}">
@endsection

@section('content')
@include('partials.messages')

{{-- Success Result --}}
@if (!empty($success))
<div class="card mb-4 border-success">
    <div class="card-header bg-success text-white">
        <strong>Shipment Created Successfully</strong>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5>Tracking Number</h5>
                <h3 class="text-success font-weight-bold">{{ $trackingNo ?? 'N/A' }}</h3>
            </div>
            @if (!empty($labelUrl) || !empty($invoiceUrl))
            <div class="col-md-6 text-md-right">
                @if (!empty($labelUrl))
                <a href="{{ route('fedex.label.download', ['url' => $labelUrl]) }}" class="btn btn-primary btn-lg mb-1">
                    Download Shipping Label
                </a>
                @endif
                @if (!empty($invoiceUrl))
                <a href="{{ route('fedex.label.download', ['url' => $invoiceUrl]) }}" class="btn btn-secondary btn-lg mb-1">
                    Download Commercial Invoice (PDF)
                </a>
                @endif
            </div>
            @endif
        </div>
        @if (!empty($alerts))
        <hr>
        <h6>Alerts</h6>
        @foreach ($alerts as $alert)
            <div class="alert alert-{{ $alert['alertType'] === 'NOTE' ? 'info' : 'warning' }} py-1 mb-1">
                <small><strong>{{ $alert['code'] }}</strong>: {{ $alert['message'] }}</small>
            </div>
        @endforeach
        @endif
    </div>
</div>
@endif

{{-- Error --}}
@if (!empty($error))
<div class="alert alert-danger">
    <strong>Error:</strong> {{ $error }}
</div>
@endif

{{-- ===== QUICK SHIP FROM EXCEL ===== --}}
<div class="card mb-4 card-quick-ship">
    <div class="card-header text-white">
        <strong class="qs-title">&#9889; Quick Ship from Excel</strong>
        <small class="ml-2 font-weight-normal header-subtitle">— enter invoice/AWB, generates invoice PDF &amp; shipping label automatically</small>
    </div>
    <div class="card-body py-3">
        <form method="POST" action="{{ route('fedex.shipment.quick') }}">
            @csrf
            <div class="form-row align-items-end">
                <div class="col-md-4">
                    <label class="small font-weight-bold mb-1">Invoice Number or AWB <span class="text-danger">*</span></label>
                    <input type="text" name="quick_ref" class="form-control" required
                        placeholder="e.g. SVG/1169/26-27 or OWK014084285"
                        autocomplete="off">
                    <small class="text-muted">Searches FEDEX sheet, then CSB4</small>
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold mb-1">Service Type <span class="text-danger">*</span></label>
                    <select name="service_type" class="form-control" required>
                        @foreach ($serviceTypes as $val => $label)
                            <option value="{{ $val }}" {{ $val === 'FEDEX_INTERNATIONAL_PRIORITY' ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold mb-1">Pickup Type <span class="text-danger">*</span></label>
                    <select name="pickup_type" class="form-control" required>
                        @foreach ($pickupTypes as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold mb-1">Ship Date <span class="text-danger">*</span></label>
                    <input type="date" name="ship_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-1 text-right">
                    <button type="submit" class="btn btn-block font-weight-bold btn-create-label">
                        Create &amp; Label
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<form method="POST" action="{{ route('fedex.shipment.store') }}" enctype="multipart/form-data">
    @csrf

    {{-- ===== EXCEL LOOKUP PANEL ===== --}}
    <div class="card mb-4 card-load-excel">
        <div class="card-header text-white">
            <strong>&#128196; Load from Excel</strong>
            <small class="ml-2 font-weight-normal header-subtitle">— enter invoice number or AWB to auto-fill the form below</small>
        </div>
        <div class="card-body py-3">
            <div class="form-row align-items-end">
                <div class="col-md-5">
                    <label class="small font-weight-bold mb-1">Invoice Number or AWB Reference</label>
                    <input type="text" id="excel_lookup_ref" class="form-control" autocomplete="off"
                        placeholder="e.g. SVG/1169/26-27 or OWC001083340">
                    <small class="text-muted">Searches FEDEX sheet, then CSB4</small>
                </div>
                <div class="col-md-2">
                    <button type="button" id="btn_load_excel" class="btn btn-primary btn-block">
                        <span id="btn_load_text">Load Data</span>
                        <span id="btn_load_spinner" class="spinner-border spinner-border-sm d-none ml-1" role="status"></span>
                    </button>
                </div>
                <div class="col-md-5">
                    <div id="excel_lookup_msg" class="mt-1"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        {{-- Left Column --}}
        <div class="col-md-6">

            {{-- Shipment Details --}}
            <div class="card mb-4">
                <div class="card-header bg-dark text-white"><strong>Shipment Details</strong></div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Ship Date <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="date" name="ship_date" class="form-control form-control-sm" value="{{ old('ship_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Service Type <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <select name="service_type" class="form-control form-control-sm" required>
                                @foreach ($serviceTypes as $val => $label)
                                    <option value="{{ $val }}" {{ old('service_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Pickup Type <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <select name="pickup_type" class="form-control form-control-sm" required>
                                @foreach ($pickupTypes as $val => $label)
                                    <option value="{{ $val }}" {{ old('pickup_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Reference No.</label>
                        <div class="col-sm-8">
                            <input type="text" name="reference" class="form-control form-control-sm" placeholder="Order ID / Reference" value="{{ old('reference') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">INV No.</label>
                        <div class="col-sm-8">
                            <input type="text" name="inv_number" class="form-control form-control-sm" placeholder="Invoice Number" value="{{ old('inv_number') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">PO Number</label>
                        <div class="col-sm-8">
                            <input type="text" name="po_number" class="form-control form-control-sm" placeholder="Purchase Order Number" value="{{ old('po_number') }}">
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Dept. Number</label>
                        <div class="col-sm-8">
                            <input type="text" name="dept_number" class="form-control form-control-sm" placeholder="e.g. CS5/N/FOB/U/E/M/0/..." value="{{ old('dept_number') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Package Details --}}
            <div class="card mb-4">
                <div class="card-header bg-dark text-white"><strong>Package Details</strong></div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Weight <span class="text-danger">*</span></label>
                        <div class="col-sm-5">
                            <input type="number" name="weight" step="0.01" min="0.1" class="form-control form-control-sm" placeholder="e.g. 1.5" value="{{ old('weight') }}" required>
                        </div>
                        <div class="col-sm-3">
                            <select name="weight_unit" class="form-control form-control-sm">
                                <option value="KG" {{ old('weight_unit') == 'KG' ? 'selected' : '' }}>KG</option>
                                <option value="LB" {{ old('weight_unit') == 'LB' ? 'selected' : '' }}>LB</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Dimensions (L×W×H)</label>
                        <div class="col-sm-2 pr-1">
                            <input type="number" name="length" id="dim_length" class="form-control form-control-sm dim-input" placeholder="L" value="{{ old('length') }}">
                        </div>
                        <div class="col-sm-2 px-1">
                            <input type="number" name="width" id="dim_width" class="form-control form-control-sm dim-input" placeholder="W" value="{{ old('width') }}">
                        </div>
                        <div class="col-sm-2 px-1">
                            <input type="number" name="height" id="dim_height" class="form-control form-control-sm dim-input" placeholder="H" value="{{ old('height') }}">
                        </div>
                        <div class="col-sm-2 pl-1">
                            <select name="dim_unit" id="dim_unit" class="form-control form-control-sm dim-input">
                                <option value="CM" {{ old('dim_unit', 'CM') == 'CM' ? 'selected' : '' }}>CM</option>
                                <option value="IN" {{ old('dim_unit') == 'IN' ? 'selected' : '' }}>IN</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-8 offset-sm-4">
                            <small id="girth_display" class="text-muted">Length + Girth: — (Max: 330 CM / 130 IN)</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Declared Value <span class="text-danger">*</span></label>
                        <div class="col-sm-5">
                            <input type="number" name="declared_value" step="0.01" min="0" class="form-control form-control-sm" placeholder="e.g. 5000" value="{{ old('declared_value') }}" required>
                        </div>
                        <div class="col-sm-3">
                            <select name="currency" class="form-control form-control-sm">
                                <option value="INR" {{ old('currency', 'INR') == 'INR' ? 'selected' : '' }}>INR</option>
                                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP</option>
                                <option value="AED" {{ old('currency') == 'AED' ? 'selected' : '' }}>AED</option>
                                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                <option value="AUD" {{ old('currency') == 'AUD' ? 'selected' : '' }}>AUD</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Item Description</label>
                        <div class="col-sm-8">
                            <input type="text" name="item_description" class="form-control form-control-sm" placeholder="e.g. Ethnic wear / Lehenga" value="{{ old('item_description') }}">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column — Recipient --}}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-dark text-white"><strong>Recipient Details</strong></div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Full Name <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" name="recipient_name" class="form-control form-control-sm" value="{{ old('recipient_name') }}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Company</label>
                        <div class="col-sm-8">
                            <input type="text" name="recipient_company" class="form-control form-control-sm" value="{{ old('recipient_company') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Phone <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" name="recipient_phone" class="form-control form-control-sm" value="{{ old('recipient_phone') }}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Email</label>
                        <div class="col-sm-8">
                            <input type="email" name="recipient_email" class="form-control form-control-sm" value="{{ old('recipient_email') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Street Line 1 <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" name="recipient_street1" class="form-control form-control-sm" value="{{ old('recipient_street1') }}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Street Line 2</label>
                        <div class="col-sm-8">
                            <input type="text" name="recipient_street2" class="form-control form-control-sm" value="{{ old('recipient_street2') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">City <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" name="recipient_city" class="form-control form-control-sm" value="{{ old('recipient_city') }}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">State / Province</label>
                        <div class="col-sm-8">
                            <input type="text" name="recipient_state" class="form-control form-control-sm" placeholder="e.g. MH, CA" value="{{ old('recipient_state') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Postal Code <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" name="recipient_postal" class="form-control form-control-sm" value="{{ old('recipient_postal') }}" placeholder="e.g. 110020" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Country <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <select name="recipient_country" class="form-control form-control-sm" required>
                                @foreach ($countries as $code => $name)
                                    <option value="{{ $code }}" {{ old('recipient_country', 'IN') == $code ? 'selected' : '' }}>{{ $name }} ({{ $code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Residential?</label>
                        <div class="col-sm-8 d-flex align-items-center">
                            <div class="form-check">
                                <input type="checkbox" name="residential" value="1" class="form-check-input" id="residential" {{ old('residential') ? 'checked' : '' }}>
                                <label class="form-check-label" for="residential">Yes, residential address</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Customs / Export Details (required for international shipments) --}}
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <strong>Customs / Export Details</strong>
            <small class="ml-2 font-weight-normal text-white-50">(Required for international shipments)</small>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Commodity Description</label>
                        <div class="col-sm-8">
                            <input type="text" name="customs_commodity" class="form-control form-control-sm"
                                placeholder="e.g. Ethnic wear / Lehenga"
                                value="{{ old('customs_commodity', old('item_description')) }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Quantity</label>
                        <div class="col-sm-4">
                            <input type="number" name="customs_quantity" class="form-control form-control-sm"
                                min="1" value="{{ old('customs_quantity', 1) }}">
                        </div>
                        <div class="col-sm-4">
                            <select name="customs_qty_unit" class="form-control form-control-sm">
                                <option value="PCS" {{ old('customs_qty_unit', 'PCS') == 'PCS' ? 'selected' : '' }}>PCS</option>
                                <option value="NOS" {{ old('customs_qty_unit') == 'NOS' ? 'selected' : '' }}>NOS</option>
                                <option value="SET" {{ old('customs_qty_unit') == 'SET' ? 'selected' : '' }}>SET</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label col-form-label-sm">Country of Manufacture</label>
                        <div class="col-sm-8">
                            <select name="customs_country_manufacture" class="form-control form-control-sm">
                                @foreach ($countries as $code => $name)
                                    <option value="{{ $code }}" {{ old('customs_country_manufacture', 'IN') == $code ? 'selected' : '' }}>
                                        {{ $name }} ({{ $code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row mb-0">
                        <label class="col-sm-4 col-form-label col-form-label-sm">HS / Tariff Code</label>
                        <div class="col-sm-8">
                            <input type="text" name="customs_hs_code" class="form-control form-control-sm"
                                placeholder="e.g. 6211 (optional)"
                                value="{{ old('customs_hs_code') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ETD / Electronic Trade Document --}}
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <strong>Electronic Trade Document (ETD)</strong>
            <small class="ml-2 font-weight-normal text-white-50">Upload commercial invoice PDF for CSB5-compliant label</small>
        </div>
        <div class="card-body">
            <div class="form-group row mb-0">
                <label class="col-sm-3 col-form-label col-form-label-sm">
                    Commercial Invoice PDF
                </label>
                <div class="col-sm-9">
                    <input type="file" name="invoice_pdf" class="form-control-file form-control-sm" accept=".pdf">
                    <small class="text-muted">
                        Optional — PDF only, max 10 MB. When uploaded, the document is submitted to FedEx ETD and a CSB5-compliant label is generated.
                        Leave blank to generate a standard label.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="text-right mb-4">
        <button type="submit" class="btn btn-primary btn-lg px-5">
            Create Shipment &amp; Generate Label
        </button>
    </div>

</form>
@endsection

@section('scripts')
<script>
(function () {
    /* ---- Girth calculator ---- */
    const MAX_CM = 330, MAX_IN = 130;
    function recalc() {
        const l = parseFloat(document.getElementById('dim_length').value) || 0;
        const w = parseFloat(document.getElementById('dim_width').value)  || 0;
        const h = parseFloat(document.getElementById('dim_height').value) || 0;
        const unit = document.getElementById('dim_unit').value;
        const el   = document.getElementById('girth_display');
        if (!l && !w && !h) { el.textContent = 'Length + Girth: — (Max: 330 CM / 130 IN)'; el.className = 'text-muted'; return; }
        const combined = l + 2 * h + 2 * w;
        const max      = unit === 'IN' ? MAX_IN : MAX_CM;
        const over     = combined > max;
        el.textContent = `Length + Girth: ${combined.toFixed(1)} ${unit}  (Max: ${max} ${unit})${over ? ' ⚠ EXCEEDS LIMIT' : ' ✓'}`;
        el.className   = over ? 'text-danger font-weight-bold' : 'text-success';
    }
    document.querySelectorAll('.dim-input').forEach(el => el.addEventListener('input', recalc));
    recalc();

    /* ---- Excel lookup ---- */
    const COUNTRY_OPTIONS = @json(array_keys($countries));

    function setField(name, value) {
        const el = document.querySelector('[name="' + name + '"]');
        if (!el || value === null || value === undefined || value === '') return;
        if (el.tagName === 'SELECT') {
            const val = String(value).toUpperCase();
            for (const opt of el.options) {
                if (opt.value.toUpperCase() === val) { opt.selected = true; return; }
            }
        } else {
            el.value = value;
        }
    }

    document.getElementById('btn_load_excel').addEventListener('click', function () {
        const ref = document.getElementById('excel_lookup_ref').value.trim();
        if (!ref) { showMsg('Please enter an Invoice Number or AWB reference.', 'warning'); return; }

        setLoading(true);
        showMsg('', '');

        fetch('{{ route('fedex.invoice.lookup') }}?ref=' + encodeURIComponent(ref), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            setLoading(false);
            if (!ok) { showMsg(data.error || 'Not found.', 'danger'); return; }

            setField('inv_number',                  data.inv_number);
            setField('reference',                   data.reference);
            setField('po_number',                   data.po_number);
            setField('dept_number',                 data.dept_number);
            setField('recipient_name',              data.recipient_name);
            setField('recipient_company',           data.recipient_company);
            setField('recipient_phone',             data.recipient_phone);
            setField('recipient_email',             data.recipient_email);
            setField('recipient_street1',           data.recipient_street1);
            setField('recipient_street2',           data.recipient_street2);
            setField('recipient_city',              data.recipient_city);
            setField('recipient_state',             data.recipient_state);
            setField('recipient_postal',            data.recipient_postal);
            setField('recipient_country',           data.recipient_country);
            setField('weight',                      data.weight);
            setField('length',                      data.length);
            setField('width',                       data.width);
            setField('height',                      data.height);
            setField('declared_value',              data.declared_value);
            setField('currency',                    data.currency);
            setField('customs_commodity',           data.customs_commodity);
            setField('customs_quantity',            data.customs_quantity);
            setField('customs_qty_unit',            data.customs_qty_unit);
            setField('customs_hs_code',             data.customs_hs_code);
            setField('customs_country_manufacture', data.customs_country_manufacture);

            recalc();
            showMsg('Data loaded successfully from Excel.', 'success');
        })
        .catch(() => { setLoading(false); showMsg('Request failed. Check server logs.', 'danger'); });
    });

    document.getElementById('excel_lookup_ref').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btn_load_excel').click(); }
    });

    function setLoading(on) {
        document.getElementById('btn_load_text').textContent = on ? 'Loading…' : 'Load Data';
        document.getElementById('btn_load_spinner').classList.toggle('d-none', !on);
        document.getElementById('btn_load_excel').disabled = on;
    }
    function showMsg(msg, type) {
        const el = document.getElementById('excel_lookup_msg');
        el.innerHTML = msg ? `<span class="text-${type}">${msg}</span>` : '';
    }
})();
</script>
@endsection

@extends('layouts.app')

@section('page-title', __('Report Remarks'))
@section('page-heading', __('Report Remarks')) 

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/orderdetails.css') }}" media="all" type="text/css"> 
@endpush

<h4 class="m-3">Remarks</h4>
<div class="card">
    <div class="card-body">
        <div class="row">            
            <div class="col-12 mx-auto bg-white">
                <div class="card border-0">
                    <form method="POST" action="{{ route('order.report.updateremarks') }}" id="datawiserepoerremarkForm">
                        @csrf
                        @php 
                            $itemSku   = $collection->unique_id; 
                            $entity_id = $collection->entity_id;
                            $remark = $collection->remark; 
                            
                        @endphp

                        <input type="hidden" name="datewisereport_id" value="{{ $entity_id }}">
                        <input type="hidden" name="item_sku" value="{{ $itemSku ?? '' }}">
                        
                        <div class="table-responsive" id="users-table-wrapper">
                            <table class="table table-bordered table-striped small">
                                <tbody>
                                    <tr>
                                        <td>
                                            @php
                                            $remarks = getRemarksByEntityId($itemSku); // collection of remarks
                                            $allRemarks = ''; // string to store remarks
                                            if ($remarks->isNotEmpty()) {
                                                foreach ($remarks as $remark) {
                                                    $allRemarks .= $remark->remark . "\n"; // append with newline
                                                }
                                            }
                                            @endphp
                                            <textarea id="remark" name="remark" class="form-control" rows="4">{{ trim($allRemarks) }}</textarea>
                                        </td>
                                    </tr> 
                                    <tr>
                                        <td>
                                            <button type="submit" class="btn btn-primary btn-success" id="btn-success">Add Remarks</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .navbar, .col-md-2.sidebar { display: none !important; }
</style>

<script src="//cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        window.attachFormSubmitLoader('datawiserepoerremarkForm', 'btn-success');
    }); 

    CKEDITOR.replace('remark', {
        extraPlugins: 'colorbutton,font',versionCheck: false,
        toolbar: [
            { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike'] },
            { name: 'paragraph', items: ['NumberedList', 'BulletedList'] },
            { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
            { name: 'colors', items: ['TextColor', 'BGColor'] },
            { name: 'insert', items: ['Image', 'Link', 'Unlink'] },
            { name: 'tools', items: ['Maximize'] }
        ]
    });
</script>
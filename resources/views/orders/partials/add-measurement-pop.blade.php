<div class="modal fade" id="addSizeModal" tabindex="-1" aria-labelledby="addSizeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 shadow con">
            <div class="modal-header position-relative">
                <h5 class="modal-title w-100 text-center" id="addSizeModalLabel">Add Size</h5>
                <button type="button" class="btn-close position-absolute end-0 bg-transparent"
                    style="right:10px; border:none;" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>
            <div class="modal-body">
                <div class="custom-card-container">
                    @include('orders.partials.add-measurement-pop.add-size')
                    @include('orders.partials.add-measurement-pop.add-instruction')
                    @include('orders.partials.add-measurement-pop.upload-image')
                </div>
            </div>
        </div>
    </div>
</div>
 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
{{-- 全域共用圖片預覽組件 --}}
<div class="modal fade" id="globalImageModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-image mr-2 text-primary"></i>圖片預覽
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-4">
                {{-- 這裡不放 src，由 JS 動態注入 --}}
                <img id="globalPreviewImg" src="" class="img-fluid border shadow-sm" style="max-height: 70vh;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
            </div>
        </div>
    </div>
</div>

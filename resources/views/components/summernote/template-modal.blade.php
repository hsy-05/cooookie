<!-- 📄 Summernote 範本插入 Modal -->
<div class="modal fade" id="templateModal" tabindex="-1" role="dialog" aria-labelledby="templateModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">插入範本</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- 範本選單列表 -->
                <ul class="list-group">
                    @for ($i = 1; $i <= 4; $i++)
                        <li class="list-group-item template-item" data-id="{{ $i }}">
                            📄 範本 {{ $i }}
                        </li>
                    @endfor
                </ul>
            </div>
        </div>
    </div>
</div>

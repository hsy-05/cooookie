document.addEventListener('DOMContentLoaded', function () {
    // 綁定所有 "全選" 按鈕
    const selectAllBtns = document.querySelectorAll('.js-perm-select-all');

    selectAllBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const groupKey = this.dataset.group;
            // 找到該群組內所有的 checkbox
            const checkboxes = document.querySelectorAll(`.js-perm-checkbox[data-group="${groupKey}"]`);

            // 判斷目前是否全選中，若是則全取消，反之全選
            let allChecked = true;
            checkboxes.forEach(cb => {
                if (!cb.checked) allChecked = false;
            });

            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });
        });
    });

    // 刪除按鈕確認 (若共用 admin.js 可省略此段)
    const deleteBtns = document.querySelectorAll('.js-delete-btn');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if(confirm('確定要刪除此管理員嗎？此操作無法復原。')) {
                document.getElementById('deleteForm' + this.dataset.id).submit();
            }
        });
    });
});

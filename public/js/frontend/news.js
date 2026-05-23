/**
         * 匿名自我執行函式：優化內頁編輯器輸出內容
         *
         * 用途：防止 Summernote 產生的 inline style 破壞前端 RWD 佈局，並自動補上圖片懶加載
         */
        (function() {
            /**
             * 函式名稱：initArticleContent
             * 用途：遍歷並清洗編輯器內的圖片樣式
             * 參數：無
             */
            const initArticleContent = () => {
                const editorContent = document.querySelector('.editor-content');

                // 防呆機制：若頁面沒有編輯器內容區塊則直接中斷執行，避免 JS 報錯
                if (!editorContent) return;

                const imgs = editorContent.querySelectorAll('img');
                imgs.forEach(img => {
                    // 強制加入效能優化屬性
                    img.loading = 'lazy';

                    // 防呆：移除後台編輯器可能夾帶的固定寬高 style，改由 CSS 統一控制響應式
                    if (img.getAttribute('style')) {
                        img.removeAttribute('style');
                    }
                });
            };

            // 當 DOM 樹載入完成後執行
            document.addEventListener('DOMContentLoaded', initArticleContent);
        })();

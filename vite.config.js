import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin'; // Laravel Vite 插件，用於與 Laravel 框架集成

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/js/app.js',  // 編譯的 JS 文件入口
        'resources/css/app.css', // 編譯的 CSS 文件入口
        'resources/css/home.scss',
      ],
      refresh: true,  // 啟用自動刷新
    }),
  ],

  server: {
    port: 3000,  // 設定開發伺服器端口為 82
    host: 'localhost',  // 只允許本地訪問
    open: true,  // 自動打開瀏覽器
  },

  build: {
    outDir: 'public',  // 指定編譯後的文件輸出目錄
    assetsDir: 'assets',  // 靜態資源（如圖片、字型等）會放到 public/assets 目錄下
    sourcemap: true,  // 開啟源映射，方便除錯
    chunkSizeWarningLimit: 500,  // 設置文件大小警告限制，超過 500 KB 會顯示警告
    manifest: true,  // 生成 manifest.json，便於前端資源管理

    // 禁止 Vite 清空 public 目錄，避免誤刪除上傳圖片
    emptyOutDir: false,  // 防止在構建時清空 public 目錄內容，這樣可以保證上傳的圖片不會丟失

    // 確保生成的圖片、CSS、JS 資源存放在正確的位置
    rollupOptions: {
      output: {
        // 禁止刪除 public 目錄中的原始圖片，並設置資源的子目錄
        assetFileNames: (assetInfo) => {
          if (assetInfo.name === 'images') {
            return 'images/[name].[hash][extname]'; // 靜態圖片放到 images 資料夾下
          }
          return 'assets/[name].[hash][extname]'; // 其他資源放到 assets 資料夾
        }
      }
    },
  },

  resolve: {
    alias: {
      '@': '/resources/js',  // '@' 解析為 /resources/js
      '@css': '/resources/css',  // '@css' 解析為 /resources/css
    },
  },

  // 確保圖片從 storage 目錄中能夠正確加載
  server: {
    proxy: {
      '/storage': {
        target: 'http://localhost:82/cooookie', // 或是配置為你的網站伺服器 URL，根據需要調整
        changeOrigin: true,
        secure: false,
      },
    },
  },
});

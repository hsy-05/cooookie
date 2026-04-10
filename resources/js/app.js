import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();


// 引入你的 CSS/SCSS (這樣 Vite 就會幫你編譯 SCSS)
import '../css/frontend.scss';

// 引入你的 JS 邏輯
import './frontend/common.js';
import './frontend/home.js';

<section class="mail-settings-container">
    <header class="section-header">
        <h2>郵件伺服器設定 (SMTP)</h2>
        <p>請設定您的發信伺服器資訊，系統將透過此設定發送通知信。</p>
    </header>

    <form action="{{ route('admin.system_settings.update_all') }}" method="POST" id="mailSettingsForm" class="responsive-form">
        @csrf
        @method('PUT')

        <fieldset class="setting-group">
            <legend>基本發信設定</legend>

            <div class="form-row">
                <div class="form-group">
                    <label for="mail_from_name">寄件者名稱 (顯示名稱)</label>
                    <input type="text" id="mail_from_name" name="settings[mail_from_name]" value="{{ $settings['mail_from_name'] ?? '我的網站' }}" required>
                </div>
                <div class="form-group">
                    <label for="mail_from_address">寄件者信箱 (系統發信帳號)</label>
                    <input type="email" id="mail_from_address" name="settings[mail_from_address]" value="{{ $settings['mail_from_address'] ?? 'noreply@example.com' }}" required>
                </div>
            </div>
        </fieldset>

        <fieldset class="setting-group">
            <legend>SMTP 伺服器連線資訊</legend>

            <div class="form-row">
                <div class="form-group">
                    <label for="mail_host">伺服器地址 (Host)</label>
                    <input type="text" id="mail_host" name="settings[mail_host]" value="{{ $settings['mail_host'] ?? 'sandbox.smtp.mailtrap.io' }}" required>
                </div>
                <div class="form-group">
                    <label for="mail_port">連接埠 (Port)</label>
                    <input type="number" id="mail_port" name="settings[mail_port]" value="{{ $settings['mail_port'] ?? '2525' }}" required>
                </div>
                <div class="form-group">
                    <label for="mail_encryption">連線加密方式</label>
                    <select id="mail_encryption" name="settings[mail_encryption]">
                        <option value="tls" {{ ($settings['mail_encryption'] ?? '') == 'tls' ? 'selected' : '' }}>TLS (建議)</option>
                        <option value="ssl" {{ ($settings['mail_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="" {{ ($settings['mail_encryption'] ?? '') == '' ? 'selected' : '' }}>無加密</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="mail_username">帳號 (Username)</label>
                    <input type="text" id="mail_username" name="settings[mail_username]" value="{{ $settings['mail_username'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label for="mail_password">密碼 (Password)</label>
                    <input type="password" id="mail_password" name="settings[mail_password]" value="{{ $settings['mail_password'] ?? '' }}">
                </div>
            </div>
        </fieldset>

        <div class="form-actions">
            <button type="submit" class="btn-primary" id="saveMailSettingsBtn">儲存設定</button>
        </div>
    </form>
</section>

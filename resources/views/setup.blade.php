<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TinyURL Setup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .setup-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 800px;
            width: 100%;
        }

        .setup-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .setup-header h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .setup-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .storage-option {
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .storage-option:hover {
            border-color: #667eea;
            background-color: #f8f9ff;
        }

        .storage-option.selected {
            border-color: #667eea;
            background-color: #f8f9ff;
        }

        .storage-option input[type="radio"] {
            display: none;
        }

        .option-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .option-header .radio-custom {
            width: 20px;
            height: 20px;
            border: 2px solid #ddd;
            border-radius: 50%;
            margin-right: 15px;
            position: relative;
            transition: all 0.3s ease;
        }

        .storage-option.selected .radio-custom {
            border-color: #667eea;
        }

        .storage-option.selected .radio-custom::after {
            content: '';
            width: 10px;
            height: 10px;
            background: #667eea;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .option-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
        }

        .option-description {
            color: #666;
            margin-left: 35px;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .multi-db-config {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .multi-db-config.show {
            display: block;
        }

        .db-config-group {
            margin-bottom: 25px;
            padding: 15px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e1e5e9;
        }

        .db-config-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 500;
            color: #555;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .form-group input {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.95rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .setup-actions {
            margin-top: 40px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a67d8;
        }

        .btn-secondary {
            background: #f7fafc;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #edf2f7;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .alert-success {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            color: #22543d;
        }

        .alert-error {
            background: #fed7d7;
            border: 1px solid #fc8181;
            color: #742a2a;
        }

        .alert-info {
            background: #ebf8ff;
            border: 1px solid #90cdf4;
            color: #2a4365;
        }

        .connection-test {
            margin-top: 15px;
        }

        .connection-result {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .connection-result.success {
            background: #f0fff4;
            color: #22543d;
        }

        .connection-result.error {
            background: #fed7d7;
            color: #742a2a;
        }

        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loading .spinner {
            display: inline-block;
        }

        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 350px;
        }

        .toast {
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
            padding: 16px 20px;
            border-left: 4px solid;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }

        .toast.success {
            border-left-color: #38a169;
        }

        .toast.error {
            border-left-color: #e53e3e;
        }

        .toast.info {
            border-left-color: #3182ce;
        }

        .toast.warning {
            border-left-color: #d69e2e;
        }

        .toast-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .toast-title {
            font-weight: 600;
            font-size: 0.95rem;
            margin: 0;
        }

        .toast.success .toast-title {
            color: #38a169;
        }

        .toast.error .toast-title {
            color: #e53e3e;
        }

        .toast.info .toast-title {
            color: #3182ce;
        }

        .toast.warning .toast-title {
            color: #d69e2e;
        }

        .toast-close {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #a0aec0;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .toast-close:hover {
            background: #f7fafc;
            color: #4a5568;
        }

        .toast-message {
            color: #4a5568;
            font-size: 0.9rem;
            line-height: 1.4;
            margin: 0;
        }

        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 0 0 8px 8px;
            transition: width linear;
        }

        .toast.success .toast-progress {
            background: #38a169;
        }

        .toast.error .toast-progress {
            background: #e53e3e;
        }

        .toast.info .toast-progress {
            background: #3182ce;
        }

        .toast.warning .toast-progress {
            background: #d69e2e;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .setup-actions {
                flex-direction: column;
            }

            .toast-container {
                left: 20px;
                right: 20px;
                max-width: none;
            }

            .toast {
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>

    <div class="setup-container">
        <div class="setup-header">
            <h1>TinyURL Setup</h1>
            <p>Choose your storage mode to get started</p>
        </div>

        <div id="alert-container"></div>

        <form id="setup-form">
            @csrf

            <!-- Single Table Option -->
            <div class="storage-option" data-mode="single">
                <input type="radio" name="storage_mode" value="single" id="single">
                <div class="option-header">
                    <div class="radio-custom"></div>
                    <label for="single" class="option-title">Single Table</label>
                </div>
                <div class="option-description">
                    Store all URLs in a single table. Simple and efficient for small to medium scale applications.
                    <br><strong>Best for:</strong> Up to 1M URLs, simple setup, single database server.
                </div>
            </div>

            <!-- Multi Table Option -->
            <div class="storage-option" data-mode="multi_table">
                <input type="radio" name="storage_mode" value="multi_table" id="multi_table">
                <div class="option-header">
                    <div class="radio-custom"></div>
                    <label for="multi_table" class="option-title">Multiple Tables</label>
                </div>
                <div class="option-description">
                    Distribute URLs across multiple tables for better performance. Uses sharding based on URL token.
                    <br><strong>Best for:</strong> Up to 10M URLs, improved query performance, single database server.
                </div>
            </div>

            <!-- Multi Database Option -->
            <div class="storage-option" data-mode="multi_db">
                <input type="radio" name="storage_mode" value="multi_db" id="multi_db">
                <div class="option-header">
                    <div class="radio-custom"></div>
                    <label for="multi_db" class="option-title">Multiple Databases</label>
                </div>
                <div class="option-description">
                    Distribute URLs across multiple database servers for maximum scalability and redundancy.
                    <br><strong>Best for:</strong> 10M+ URLs, high availability, multiple database servers.
                </div>

                <!-- Multi-DB Configuration -->
                <div class="multi-db-config" id="multi-db-config">
                    <div class="alert alert-info">
                        <strong>Database Configuration:</strong> Configure connection details for each database server.
                        Each database will handle a portion of your URLs automatically.
                    </div>

                    @for ($i = 1; $i <= 4; $i++)
                        <div class="db-config-group">
                            <div class="db-config-title">Database {{ $i }} Configuration</div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="db_{{ $i }}_host">Host</label>
                                    <input type="text" id="db_{{ $i }}_host"
                                        name="db_{{ $i }}_host" value="127.0.0.1"
                                        placeholder="Database host">
                                </div>
                                <div class="form-group">
                                    <label for="db_{{ $i }}_port">Port</label>
                                    <input type="text" id="db_{{ $i }}_port"
                                        name="db_{{ $i }}_port" value="3306" placeholder="Database port">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="db_{{ $i }}_database">Database Name</label>
                                    <input type="text" id="db_{{ $i }}_database"
                                        name="db_{{ $i }}_database" value="tinyurl_{{ $i }}"
                                        placeholder="Database name">
                                </div>
                                <div class="form-group">
                                    <label for="db_{{ $i }}_username">Username</label>
                                    <input type="text" id="db_{{ $i }}_username"
                                        name="db_{{ $i }}_username" value="root"
                                        placeholder="Database username">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="db_{{ $i }}_password">Password</label>
                                    <input type="password" id="db_{{ $i }}_password"
                                        name="db_{{ $i }}_password" placeholder="Database password">
                                </div>
                                <div class="form-group"></div>
                            </div>
                        </div>
                    @endfor

                    <div class="connection-test">
                        <button type="button" class="btn btn-secondary" id="test-connections">
                            <span class="spinner"></span>
                            Test Connections
                        </button>
                        <div id="connection-results"></div>
                    </div>
                </div>
            </div>

            <div class="setup-actions">
                <button type="button" class="btn btn-secondary" id="test-config">
                    <span class="spinner"></span>
                    Test Configuration
                </button>
                <button type="submit" class="btn btn-primary" id="configure-btn">
                    <span class="spinner"></span>
                    Configure & Start
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('setup-form');
            const storageOptions = document.querySelectorAll('.storage-option');
            const multiDbConfig = document.getElementById('multi-db-config');
            const testConnectionsBtn = document.getElementById('test-connections');
            const testConfigBtn = document.getElementById('test-config');
            const configureBtn = document.getElementById('configure-btn');
            const alertContainer = document.getElementById('alert-container');
            const toastContainer = document.getElementById('toast-container');

            // Toast Notification System
            function showToast(message, type = 'info', title = '', duration = 5000) {
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;

                const toastId = 'toast-' + Date.now();
                toast.id = toastId;

                let toastTitle = title;
                if (!toastTitle) {
                    switch (type) {
                        case 'success':
                            toastTitle = 'Success';
                            break;
                        case 'error':
                            toastTitle = 'Error';
                            break;
                        case 'warning':
                            toastTitle = 'Warning';
                            break;
                        case 'info':
                            toastTitle = 'Info';
                            break;
                        default:
                            toastTitle = 'Notification';
                    }
                }

                toast.innerHTML = `
                    <div class="toast-header">
                        <h4 class="toast-title">${toastTitle}</h4>
                        <button class="toast-close" onclick="removeToast('${toastId}')">&times;</button>
                    </div>
                    <p class="toast-message">${message}</p>
                    <div class="toast-progress" style="width: 100%;"></div>
                `;

                toastContainer.appendChild(toast);

                // Trigger animation
                setTimeout(() => {
                    toast.classList.add('show');
                }, 10);

                // Auto-remove after duration
                if (duration > 0) {
                    const progressBar = toast.querySelector('.toast-progress');
                    progressBar.style.transition = `width ${duration}ms linear`;
                    progressBar.style.width = '0%';

                    setTimeout(() => {
                        removeToast(toastId);
                    }, duration);
                }

                return toastId;
            }

            // Remove toast function
            window.removeToast = function(toastId) {
                const toast = document.getElementById(toastId);
                if (toast) {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.parentNode.removeChild(toast);
                        }
                    }, 300);
                }
            };

            // Handle storage option selection
            storageOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const mode = this.dataset.mode;
                    const radio = this.querySelector('input[type="radio"]');

                    // Clear previous selections
                    storageOptions.forEach(opt => opt.classList.remove('selected'));

                    // Select current option
                    this.classList.add('selected');
                    radio.checked = true;

                    // Show toast for selection
                    const modeNames = {
                        'single': 'Single Table',
                        'multi_table': 'Multiple Tables',
                        'multi_db': 'Multiple Databases'
                    };
                    showToast(`${modeNames[mode]} storage mode selected`, 'info',
                        'Selection Updated', 3000);

                    // Show/hide multi-DB configuration
                    if (mode === 'multi_db') {
                        multiDbConfig.classList.add('show');
                        showToast('Configure your database connections below', 'info',
                            'Configuration Required', 4000);
                    } else {
                        multiDbConfig.classList.remove('show');
                    }
                });
            });

            // Test connections for multi-DB
            testConnectionsBtn.addEventListener('click', function() {
                testConnections();
            });

            // Test configuration
            testConfigBtn.addEventListener('click', function() {
                testConfiguration();
            });

            // Handle form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                configureSetup();
            });

            function showAlert(message, type = 'info') {
                const alert = document.createElement('div');
                alert.className = `alert alert-${type}`;
                alert.innerHTML = message;
                alertContainer.innerHTML = '';
                alertContainer.appendChild(alert);

                setTimeout(() => {
                    alert.remove();
                }, 5000);
            }

            function setLoading(button, loading) {
                if (loading) {
                    button.classList.add('loading');
                    button.disabled = true;
                } else {
                    button.classList.remove('loading');
                    button.disabled = false;
                }
            }

            function testConnections() {
                const formData = new FormData(form);
                setLoading(testConnectionsBtn, true);
                showToast('Testing database connections...', 'info', 'Please Wait');

                fetch('{{ route('setup.test-connections') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        const resultsContainer = document.getElementById('connection-results');
                        resultsContainer.innerHTML = '';

                        if (data.success) {
                            let successCount = 0;
                            let errorCount = 0;

                            Object.entries(data.connections).forEach(([connection, result]) => {
                                const resultDiv = document.createElement('div');
                                resultDiv.className = `connection-result ${result.status}`;
                                resultDiv.innerHTML = `
                                <strong>${connection}:</strong> ${result.message}
                            `;
                                resultsContainer.appendChild(resultDiv);

                                if (result.status === 'success') {
                                    successCount++;
                                } else {
                                    errorCount++;
                                }
                            });

                            if (errorCount === 0) {
                                showToast('All database connections are working perfectly!', 'success',
                                    'Connection Test');
                            } else {
                                showToast(
                                    `${successCount} connections successful, ${errorCount} failed. Check the details below.`,
                                    'warning', 'Connection Test');
                            }
                        } else {
                            showToast(data.message, 'error', 'Connection Test Failed');
                        }
                    })
                    .catch(error => {
                        showToast('Connection test failed: ' + error.message, 'error', 'Network Error');
                    })
                    .finally(() => {
                        setLoading(testConnectionsBtn, false);
                    });
            }

            function testConfiguration() {
                const formData = new FormData(form);
                setLoading(testConfigBtn, true);
                showToast('Testing configuration...', 'info', 'Validating Setup');

                fetch('{{ route('setup.test-connections') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Configuration test successful! Ready to proceed.', 'success',
                                'Configuration Valid');
                        } else {
                            showToast('Configuration test failed: ' + data.message, 'error',
                                'Configuration Error');
                        }
                    })
                    .catch(error => {
                        showToast('Configuration test failed: ' + error.message, 'error', 'Network Error');
                    })
                    .finally(() => {
                        setLoading(testConfigBtn, false);
                    });
            }

            function configureSetup() {
                const formData = new FormData(form);
                setLoading(configureBtn, true);
                showToast('Configuring your TinyURL application...', 'info', 'Setup in Progress');

                fetch('{{ route('setup.configure') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success', 'Setup Complete!');
                            showToast('Redirecting to your application...', 'info', 'Please Wait', 3000);
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 2000);
                        } else {
                            showToast(data.message, 'error', 'Setup Failed');
                        }
                    })
                    .catch(error => {
                        showToast('Setup failed: ' + error.message, 'error', 'Setup Error');
                    })
                    .finally(() => {
                        setLoading(configureBtn, false);
                    });
            }

            // Auto-select first option if none selected
            if (!document.querySelector('input[name="storage_mode"]:checked')) {
                storageOptions[0].click();
            }

            // Show welcome toast
            setTimeout(() => {
                showToast('Welcome to TinyURL setup! Select your preferred storage mode to get started.',
                    'info', 'Welcome!', 6000);
            }, 500);
        });
    </script>
</body>

</html>

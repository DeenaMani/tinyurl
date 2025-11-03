<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TinyURL') }} - Modern URL Shortener</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --shadow-soft: 0 10px 30px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 15px 40px rgba(0, 0, 0, 0.15);
            --border-radius: 15px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }

        .url-shortener-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .url-shortener-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-5px);
        }

        .card-header-custom {
            background: var(--primary-gradient);
            color: white;
            padding: 25px 30px;
            border: none;
            text-align: center;
        }

        .card-header-custom h2 {
            margin: 0;
            font-weight: 600;
            font-size: 1.8rem;
        }

        .card-header-custom p {
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .card-body-custom {
            padding: 40px 30px;
        }

        .form-group-custom {
            margin-bottom: 25px;
        }

        .form-label-custom {
            font-weight: 600;
            color: #374151;
            margin-bottom: 10px;
            display: block;
        }

        .form-control-custom {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 15px 18px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fafbfc;
        }

        .form-control-custom:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
            outline: none;
        }

        .btn-primary-custom {
            background: var(--primary-gradient);
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-primary-custom:disabled {
            opacity: 0.7;
            transform: none;
            box-shadow: none;
        }

        .btn-copy {
            background: var(--success-gradient);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-left: 10px;
        }

        .btn-copy:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
            color: white;
        }

        .btn-new-url {
            background: var(--secondary-gradient);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 15px;
        }

        .btn-new-url:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 87, 108, 0.3);
            color: white;
        }

        .result-container {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 12px;
            padding: 25px;
            margin-top: 25px;
            border: 1px solid #e2e8f0;
        }

        .result-url-container {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 8px;
            padding: 3px;
            border: 1px solid #e5e7eb;
        }

        .result-url {
            flex: 1;
            border: none;
            padding: 12px 15px;
            background: transparent;
            font-size: 1rem;
            color: #374151;
        }

        .result-url:focus {
            outline: none;
        }

        .loading-spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stats-container {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: #667eea;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 2px;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 10px;
            }

            .card-body-custom {
                padding: 30px 20px;
            }

            .card-header-custom {
                padding: 20px;
            }

            .result-url-container {
                flex-direction: column;
            }

            .btn-copy {
                margin-left: 0;
                margin-top: 10px;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="url-shortener-card">
            <div class="card-header-custom">
                <h2><i class="bi bi-link-45deg"></i> TinyURL Generator</h2>
                <p>Transform long URLs into short, shareable links instantly</p>
            </div>

            <div class="card-body-custom">
                <form id="urlShortenerForm">
                    <div class="form-group-custom">
                        <label for="url" class="form-label-custom">
                            <i class="bi bi-globe"></i> Enter your long URL
                        </label>
                        <input type="url" name="url" id="url" class="form-control form-control-custom"
                            placeholder="https://example.com/your-very-long-url-here" required />
                    </div>

                    <button type="submit" class="btn btn-primary-custom" id="shortenBtn">
                        <span id="btnText">
                            <i class="bi bi-scissors"></i> Shorten URL
                        </span>
                        <span id="btnLoading" style="display: none;">
                            <div class="loading-spinner"></div>
                            Generating...
                        </span>
                    </button>
                </form>

                <div id="resultContainer" class="result-container fade-in" style="display: none;">
                    <h5 class="mb-3"><i class="bi bi-check-circle-fill text-success"></i> Your shortened URL is ready!
                    </h5>

                    <div class="result-url-container">
                        <input type="text" id="shortenedUrl" class="result-url" readonly />
                        <button type="button" class="btn btn-copy" id="copyBtn" onclick="copyToClipboard()">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>

                    <button type="button" class="btn btn-new-url" onclick="resetForm()">
                        <i class="bi bi-plus-circle"></i> Shorten Another URL
                    </button>

                    <div class="stats-container">
                        <div class="stat-item">
                            <div class="stat-number" id="originalLength">0</div>
                            <div class="stat-label">Original Length</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" id="shortenedLength">0</div>
                            <div class="stat-label">Shortened Length</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" id="savedChars">0</div>
                            <div class="stat-label">Characters Saved</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#urlShortenerForm').on('submit', function(e) {
            e.preventDefault();

            const url = $('#url').val();
            if (!url) {
                toastr.error('Please enter a valid URL');
                return;
            }

            $('#btnText').hide();
            $('#btnLoading').show();
            $('#shortenBtn').prop('disabled', true);

            $.ajax({
                url: '{{ route('tiny-url.store') }}',
                method: 'POST',
                data: {
                    url: url
                },
                success: function(response) {
                    if (response.success) {
                        displayResult(response.data);
                        toastr.success('URL shortened successfully!');
                    } else {
                        toastr.error(response.message || 'Failed to shorten URL');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while shortening the URL';

                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join(', ');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    toastr.error(errorMessage);
                },
                complete: function() {
                    $('#btnText').show();
                    $('#btnLoading').hide();
                    $('#shortenBtn').prop('disabled', false);
                }
            });
        });

        function displayResult(data) {
            const originalUrl = data.orginal_url;
            const shortenedUrl = `{{ url('') }}/${data.token}`;

            $('#shortenedUrl').val(shortenedUrl);
            $('#resultContainer').fadeIn(300);

            $('#originalLength').text(originalUrl.length);
            $('#shortenedLength').text(shortenedUrl.length);
            $('#savedChars').text(originalUrl.length - shortenedUrl.length);

            $('#urlShortenerForm').fadeOut(300);
        }

        function copyToClipboard() {
            const shortenedUrl = $('#shortenedUrl').val();

            if (navigator.clipboard) {
                navigator.clipboard.writeText(shortenedUrl).then(function() {
                    toastr.success('URL copied to clipboard!');
                    $('#copyBtn').html('<i class="bi bi-check"></i> Copied!').addClass('btn-success');
                    setTimeout(() => {
                        $('#copyBtn').html('<i class="bi bi-clipboard"></i> Copy').removeClass(
                            'btn-success');
                    }, 2000);
                }).catch(function() {
                    fallbackCopyTextToClipboard(shortenedUrl);
                });
            } else {
                fallbackCopyTextToClipboard(shortenedUrl);
            }
        }

        function fallbackCopyTextToClipboard(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";

            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    toastr.success('URL copied to clipboard!');
                    $('#copyBtn').html('<i class="bi bi-check"></i> Copied!').addClass('btn-success');
                    setTimeout(() => {
                        $('#copyBtn').html('<i class="bi bi-clipboard"></i> Copy').removeClass('btn-success');
                    }, 2000);
                } else {
                    toastr.error('Failed to copy URL');
                }
            } catch (err) {
                toastr.error('Failed to copy URL');
            }

            document.body.removeChild(textArea);
        }

        function resetForm() {
            $('#url').val('');
            $('#resultContainer').fadeOut(300);
            $('#urlShortenerForm').fadeIn(300);
            $('#url').focus();
        }

        $(document).ready(function() {
            $('#url').focus();
        });

        $('#url').on('keypress', function(e) {
            if (e.which === 13) {
                $('#urlShortenerForm').submit();
            }
        });
    </script>
</body>

</html>

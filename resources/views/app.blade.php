<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/css/boxicons.min.css" rel="stylesheet">
    <script src="/js/theme.js"></script>
    <title>{{ config('app.name') }}</title>
    <link rel="icon" type="image/svg" href="/img/logo_light.png">
    <script>
        window.api_url = '{{ config('app.url') }}/api/';
        window.csrf_token = '{{ csrf_token() }}';

        function makeRequest(url, method, callback, error, form_id = null) {
            var xhr = new XMLHttpRequest();

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        let responseData = null;
                        try {
                            responseData = JSON.parse(xhr.responseText);
                        } catch (e) {
                            responseData = xhr.responseText;
                        }
                        callback(responseData);
                    } else {
                        let errorMessage = `Error ${xhr.status}: ${xhr.statusText}`;
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            if (errorResponse.message) {
                                errorMessage = errorResponse.message;
                            }
                            error();
                        } catch (e) {
                            error();
                        }
                    }
                }
            };

            xhr.open(method, url, true);

            if (form_id) {
                const form = document.getElementById(form_id);
                const formData = new FormData(form);

                let hasFile = false;
                for (const entry of formData.entries()) {
                    const [key, value] = entry;
                    if (form.elements[key].type === 'file' && form.elements[key].value !== '') {
                        hasFile = true;
                        break;
                    }
                }
                if (hasFile) {
                    xhr.send(formData);
                } else {
                    let jsonData = {};
                    formData.forEach((value, key) => {
                        if (form.elements[key].type === 'checkbox') {
                            jsonData[key] = form.elements[key].checked;
                        } else {
                            jsonData[key] = value;
                        }
                    });

                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.send(JSON.stringify(jsonData));
                }
            } else {
                xhr.send();
            }
        }

        function showToast(message = '') {
            let toastContainer = document.getElementById('toast-container');

            if (!toastContainer) {
                let html = `
                    <div id="toast-container" class="fixed bottom-0 right-0 p-4 z-50 flex flex-col gap-2">
                    </div>
                `;
                let element = document.createElement('div');
                element.innerHTML = html;
                document.body.appendChild(element.firstElementChild);
                toastContainer = document.getElementById('toast-container');
            }

            const toastId = 'toast-' + Math.random().toString(36).substr(2, 9);
            const toastHtml = `
                <div id="${toastId}" class="bg-gray-800 text-white px-4 py-3 rounded shadow-lg flex justify-between items-center transition-opacity duration-300">
                    <span>${message}</span>
                    <button onclick="document.getElementById('${toastId}').remove()" class="ml-4 text-gray-400 hover:text-white">&times;</button>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);

            setTimeout(() => {
                const toastEl = document.getElementById(toastId);
                if (toastEl) toastEl.remove();
            }, 3000);
        }

        function closeToast() {
            const toastContainer = document.getElementById('toast-container');
            if (toastContainer) toastContainer.innerHTML = '';
        }

        setInterval(function() {
            fetch('/ping');
        }, 5400000); // 90 minutes
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @routes
</head>
<body id="body-pd" class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-300">
    @inertia
</body>
</html>
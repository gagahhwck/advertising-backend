<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Redirecting ...</title>
    <style>
        html {
          font-family: Helvetica, Arial, sans-serif;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            width: 100%;
            margin: 0;
        }

        .loader {
            width: 350px;
            border-radius: 10px;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-evenly;
            padding: 30px;
            box-shadow: 2px 2px 10px -5px lightgrey;
        }

        .loading {
            width: 100%;
            height: 10px;
            background: lightgrey;
            border-radius: 10px;
            position: relative;
        }

        .loading::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 50%;
            height: 10px;
            background: #002;
            border-radius: 10px;
            z-index: 1;
            animation: loading 0.6s alternate infinite;
        }

        label {
            color: #002;
            font-size: 18px;
            animation: bit 0.6s alternate infinite;
        }

        @keyframes bit {
            from {
                opacity: 0.3;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes loading {
            0% {
                left: 25%;
            }

            100% {
                left: 50%;
            }

            0% {
                left: 0%;
            }
        }
    </style>
</head>

<body onload="loading()">
    <noscript>
        <p><strong>Note:</strong> Since your browser does not support JavaScript, you must press the button below once
            to proceed.</p>
    </noscript>

    <form method="POST" id="sso-form">
        @csrf
        <input type="hidden" id="token" name="token" />
    </form>

    <div class="loader">
        <img src="https://sso.uiii.ac.id/logo/Loading-UIII.gif" alt="Logo"
            style="width: 100%; height: 100px; object-fit:cover;" />
        <label>Redirecting...</label>
        <div class="loading"></div>
    </div>

    <script>
        // Mengambil token dari local storage
        const token = "{{ isset($remove_token) && $remove_token ? 'true' : 'false' }}" === 'true' ? null : localStorage.getItem('token');

        // Menetapkan nilai token ke input tersembunyi
        document.getElementById('token').value = token;

        function loading() {
            // Mengirimkan form secara otomatis
            if (token) {
                document.getElementById('sso-form').submit();
            } else {
                const url = new URL(window.location.href);
                const SAMLResponse = url.searchParams.get("SAMLResponse")?.replaceAll(" ", "+");
                const RelayState = url.searchParams.get("RelayState");
                if (SAMLResponse === null || RelayState === null  ||  SAMLResponse === "" || RelayState === "" || SAMLResponse === undefined || RelayState === undefined) {
                    nextRequest();
                } else  {
                    login(SAMLResponse, RelayState);
                }
            }
        }

        async function nextRequest() {
            localStorage.removeItem("token");
            const saml = await fetch("{{ env('VITE_API_URL') . '/api/saml/request' }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'key': "{{ config('setting.api_key') }}",
                },
                body: JSON.stringify({
                    url_entity_id: window.location.href,
                    url_redirect_acs: window.location.href,
                    acs_binding: "Redirect",
                }),
            }).then(response => response.json());

            if (saml.success) {
                window.location.href = saml.redirect;
            } else {
                alert('Failed to initiate SAML request.');
            }
        }

        async function login(SAMLResponse, RelayState) {
            const response = await fetch("{{ env('VITE_API_URL') . '/api/saml/login' }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'key': "{{ config('setting.api_key') }}",
                },
                body: JSON.stringify({
                    SAMLResponse: SAMLResponse,
                    RelayState: RelayState,
                }),
            }).then(response => response.json());

            if (response.success) {
                localStorage.setItem("token", response.token);
                document.getElementById('token').value = response.token;
                document.getElementById('sso-form').submit();
            } else {
                alert('SAML Response processing failed.');
            }
        }
    </script>
</body>

</html>
